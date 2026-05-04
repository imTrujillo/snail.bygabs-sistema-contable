<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryType;
use App\Models\Payroll;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

/**
 * El asiento contable debe generarse después de guardar las líneas (CreateRecord::saveRelationships).
 * Use {@see self::finalizeNewPayroll()} desde CreatePayroll::afterCreate().
 */
class PayrollObserver
{
    public function creating(Payroll $payroll): void
    {
        if (! $payroll->user_id) {
            $payroll->user_id = Auth::id();
        }
    }

    /** Registra totales, asiento Diario y notificación cuando la planilla se creó con líneas ya guardadas. */
    public static function finalizeNewPayroll(Payroll $payroll): void
    {
        if (JournalEntry::query()
            ->where('reference_type', 'payroll')
            ->where('reference_id', $payroll->id)
            ->exists()) {
            return;
        }

        $payroll->loadMissing('payrollLines');

        if ($payroll->payrollLines->isEmpty()) {
            Log::warning('Payroll sin líneas; no se generó asiento contable.', ['payroll_id' => $payroll->id]);

            return;
        }

        $period = FiscalPeriod::find(session('active_fiscal_period_id'))
            ?? $payroll->fiscalPeriod()->first();

        if (! $period || $period->is_closed) {
            Log::warning('Payroll: período fiscal no disponible o cerrado', ['payroll_id' => $payroll->id]);

            return;
        }

        $payroll->updateQuietly([
            'total_gross' => $payroll->payrollLines->sum('gross_salary'),
            'total_isss' => $payroll->payrollLines->sum('isss_deduction'),
            'total_afp' => $payroll->payrollLines->sum('afp_deduction'),
            'total_renta' => $payroll->payrollLines->sum('renta_deduction'),
            'total_net' => $payroll->payrollLines->sum('net_salary'),
        ]);

        try {
            $entryType = JournalEntryType::where('name', 'Diario')->firstOrFail();

            $entry = JournalEntry::create([
                'entry_date' => $payroll->pay_date,
                'description' => 'Planilla '.$payroll->period_type.' — '.Carbon::parse($payroll->pay_date)->toDateString(),
                'reference_type' => 'payroll',
                'reference_id' => $payroll->id,
                'fiscal_period_id' => $period->id,
                'user_id' => Auth::id(),
                'journal_entry_type_id' => $entryType->id,
            ]);

            $gastoNomina = Account::where('code', '6100')->first()
                ?? Account::where('code', '5100')->first();

            if ($gastoNomina) {
                $entry->lines()->create([
                    'account_id' => $gastoNomina->id,
                    'debit' => $payroll->total_gross,
                    'credit' => 0,
                    'description' => 'Gasto planilla salarios — bruto',
                ]);
            }

            $isssAccount = Account::where('code', '2101')->first();
            if ($isssAccount && $payroll->total_isss > 0) {
                $entry->lines()->create([
                    'account_id' => $isssAccount->id,
                    'debit' => 0,
                    'credit' => $payroll->total_isss,
                    'description' => 'ISSS por pagar (cuota laboral)',
                ]);
            }

            $afpAccount = Account::where('code', '2102')->first();
            if ($afpAccount && $payroll->total_afp > 0) {
                $entry->lines()->create([
                    'account_id' => $afpAccount->id,
                    'debit' => 0,
                    'credit' => $payroll->total_afp,
                    'description' => 'AFP por pagar (cuota laboral)',
                ]);
            }

            $isrAccount = Account::where('code', '2104-02')->first();
            if ($isrAccount && $payroll->total_renta > 0) {
                $entry->lines()->create([
                    'account_id' => $isrAccount->id,
                    'debit' => 0,
                    'credit' => $payroll->total_renta,
                    'description' => 'ISR retenido sobre salarios',
                ]);
            }

            $sueldosPorPagar = Account::where('code', '2103')->first();
            if ($sueldosPorPagar) {
                $entry->lines()->create([
                    'account_id' => $sueldosPorPagar->id,
                    'debit' => 0,
                    'credit' => $payroll->total_net,
                    'description' => 'Salario neto por pagar',
                ]);
            }

            if (Auth::check()) {
                Notification::make()
                    ->title('Planilla procesada')
                    ->body(sprintf(
                        'Bruto: $%s — Neto: $%s — ISR retenido: $%s',
                        number_format((float) $payroll->total_gross, 2),
                        number_format((float) $payroll->total_net, 2),
                        number_format((float) $payroll->total_renta, 2)
                    ))
                    ->success()
                    ->sendToDatabase(Auth::user());
            }
        } catch (\Throwable $e) {
            Log::error('Payroll finalize error: '.$e->getMessage(), [
                'payroll_id' => $payroll->id,
                'exception' => $e,
            ]);
        }
    }
}
