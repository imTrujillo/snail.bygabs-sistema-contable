<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryType;
use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;

class PayrollObserver
{
    public function creating(Payroll $payroll): void
    {
        if (!$payroll->user_id) {
            $payroll->user_id = Auth::id();
        }
    }

    public function created(Payroll $payroll): void
    {
        $period = FiscalPeriod::find(session('active_fiscal_period_id'));
        if (!$period) return;

        $entryType = JournalEntryType::where('name', 'Diario')->first();
        if (!$entryType) return;

        $payroll->loadMissing('payrollLines');

        $payroll->update([
            'total_gross' => $payroll->payrollLines->sum('gross_salary'),
            'total_isss'  => $payroll->payrollLines->sum('isss_deduction'),
            'total_afp'   => $payroll->payrollLines->sum('afp_deduction'),
            'total_renta' => $payroll->payrollLines->sum('renta_deduction'),
            'total_net'   => $payroll->payrollLines->sum('net_salary'),
        ]);


        $entry = JournalEntry::create([
            'entry_date'            => $payroll->pay_date,
            'description'           => "Planilla {$payroll->period_type} - {$payroll->pay_date}",
            'reference_type'        => 'manual',
            'reference_id'          => $payroll->id,
            'fiscal_period_id'      => $period->id,
            'user_id'               => Auth::id(),
            'journal_entry_type_id' => $entryType->id,
        ]);

        // DÉBITO → Gasto de sueldos (cuenta 5100 o similar)
        $sueldosAccount = Account::where('code', '5100')->first();
        if ($sueldosAccount) {
            $entry->lines()->create([
                'account_id'  => $sueldosAccount->id,
                'debit'       => $payroll->total_gross,
                'credit'      => 0,
                'description' => 'Gasto de sueldos y salarios',
            ]);
        }

        // CRÉDITO → ISSS por pagar (2101)
        $isssAccount = Account::where('code', '2101')->first();
        if ($isssAccount && $payroll->total_isss > 0) {
            $entry->lines()->create([
                'account_id'  => $isssAccount->id,
                'debit'       => 0,
                'credit'      => $payroll->total_isss,
                'description' => 'ISSS por pagar (cuota empleado)',
            ]);
        }

        // CRÉDITO → AFP por pagar (2102)
        $afpAccount = Account::where('code', '2102')->first();
        if ($afpAccount && $payroll->total_afp > 0) {
            $entry->lines()->create([
                'account_id'  => $afpAccount->id,
                'debit'       => 0,
                'credit'      => $payroll->total_afp,
                'description' => 'AFP por pagar (cuota empleado)',
            ]);
        }

        // CRÉDITO → Sueldos por pagar - neto (2103)
        $sueldosPorPagarAccount = Account::where('code', '2103')->first();
        if ($sueldosPorPagarAccount) {
            $entry->lines()->create([
                'account_id'  => $sueldosPorPagarAccount->id,
                'debit'       => 0,
                'credit'      => $payroll->total_net,
                'description' => 'Sueldo neto por pagar',
            ]);
        }
    }
}
