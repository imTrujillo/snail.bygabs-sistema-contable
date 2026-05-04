<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountPeriodBalance;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\JournalEntryType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriodClosingService
{
    /**
     * Valida que todas las partidas del período estén balanceadas.
     * Devuelve array con errores o vacío si todo está bien.
     */
    public function validate(FiscalPeriod $period): array
    {
        $errors = [];

        $entries = JournalEntry::where('fiscal_period_id', $period->id)
            ->with('lines')
            ->get();

        foreach ($entries as $entry) {
            $debit = round($entry->lines->sum('debit'), 2);
            $credit = round($entry->lines->sum('credit'), 2);

            if ($debit !== $credit) {
                $errors[] = "Partida #{$entry->id} ({$entry->description}) no balanceada:
                             Débito \${$debit} ≠ Crédito \${$credit}";
            }
        }

        return $errors;
    }

    /**
     * Ejecuta el cierre formal del período.
     */
    public function close(FiscalPeriod $period): FiscalPeriod
    {
        return DB::transaction(function () use ($period) {

            // 1. Re-mayorizar todo el período (por si hubo ajustes manuales)
            $this->remayorizar($period);

            // 2. Calcular resultados del período
            $totalIncome = $this->sumByType($period, 'Ingreso');
            $totalCost = $this->sumByType($period, 'Costo');
            $totalExpense = $this->sumByType($period, 'Gasto');
            $netResult = $totalIncome - $totalCost - $totalExpense;

            // 3. Registrar partida de cierre (opcional pero trazable)
            $this->crearPartidaCierre($period, $netResult);

            // 4. Guardar en period_closings
            $period->update([
                'is_closed' => true,
                'total_income' => $totalIncome,
                'total_expense' => $totalCost + $totalExpense,
                'net_result' => $netResult,
                'closed_by' => Auth::id(),
                'closed_at' => now(),
            ]);

            // 5. Marcar período como cerrado
            $period->update(['is_closed' => true]);

            // 6. Trasladar saldos al siguiente período (si existe)
            $this->trasladarSaldos($period);

            return $period;
        });
    }

    /** Re-calcula account_period_balances desde cero para el período */
    public function remayorizar(FiscalPeriod $period): void
    {
        // Borrar balances actuales del período
        AccountPeriodBalance::where('fiscal_period_id', $period->id)->delete();

        // Reagrupar todas las líneas del período por cuenta
        $lines = JournalEntryLine::whereHas(
            'journalEntry',
            fn ($q) => $q->where('fiscal_period_id', $period->id)
        )
            ->selectRaw('account_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('account_id')
            ->get();

        foreach ($lines as $line) {
            $account = Account::find($line->account_id);

            // Tomar saldo de apertura del período anterior si existe
            $prevBalance = $this->getSaldoAnterior($account->id, $period);

            $isDebitNature = in_array($account->type, ['Activo', 'Costo', 'Gasto']);

            $closing = $isDebitNature
                ? $prevBalance + $line->total_debit - $line->total_credit
                : $prevBalance + $line->total_credit - $line->total_debit;

            AccountPeriodBalance::create([
                'account_id' => $line->account_id,
                'fiscal_period_id' => $period->id,
                'opening_balance' => $prevBalance,
                'total_debit' => $line->total_debit,
                'total_credit' => $line->total_credit,
                'closing_balance' => $closing,
            ]);
        }
    }

    /** Obtiene el saldo de cierre del período anterior para una cuenta */
    private function getSaldoAnterior(int $accountId, FiscalPeriod $currentPeriod): float
    {
        $prev = FiscalPeriod::where('end_date', '<', $currentPeriod->start_date)
            ->where('is_closed', true)
            ->orderByDesc('end_date')
            ->first();

        if (! $prev) {
            return 0;
        }

        return AccountPeriodBalance::where('account_id', $accountId)
            ->where('fiscal_period_id', $prev->id)
            ->value('closing_balance') ?? 0;
    }

    /** Suma movimientos netos de un tipo de cuenta en el período */
    private function sumByType(FiscalPeriod $period, string $type): float
    {
        $query = JournalEntryLine::whereHas('journalEntry', fn ($q) => $q->where('fiscal_period_id', $period->id))
            ->whereHas('account', fn ($q) => $q->where('type', $type)->where('is_group', false));

        if ($type === 'Ingreso') {
            return (float) ($query->clone()->selectRaw('SUM(credit) - SUM(debit) as total')->value('total') ?? 0);
        }

        return (float) ($query->clone()->selectRaw('SUM(debit) - SUM(credit) as total')->value('total') ?? 0);
    }

    /** Crea partida contable de cierre para trazabilidad */
    private function crearPartidaCierre(FiscalPeriod $period, float $netResult): void
    {
        $tipoAjuste = JournalEntryType::where('name', 'Cierre')->first();
        if (! $tipoAjuste) {
            return;
        }

        // Solo registramos la partida si hay resultado
        // (en un sistema real aquí irían los asientos de cierre de cuentas nominales)
        JournalEntry::create([
            'entry_date' => $period->end_date,
            'description' => "Cierre del período: {$period->name}",
            'fiscal_period_id' => $period->id,
            'user_id' => Auth::id(),
            'journal_entry_type_id' => $tipoAjuste->id,
            'reference_id' => $period->id,
            'reference_type' => 'adjustment',
        ]);
        // Las líneas detalladas de cierre las puedes agregar aquí
        // según tu catálogo de cuentas específico
    }

    /** Traslada saldos de cierre como opening_balance del siguiente período */
    private function trasladarSaldos(FiscalPeriod $closedPeriod): void
    {
        $nextPeriod = FiscalPeriod::where('start_date', '>', $closedPeriod->end_date)
            ->orderBy('start_date')
            ->first();

        if (! $nextPeriod) {
            return;
        }

        $balances = AccountPeriodBalance::where('fiscal_period_id', $closedPeriod->id)->get();

        foreach ($balances as $balance) {
            // Solo trasladar cuentas de balance (Activo, Pasivo, Patrimonio)
            $account = Account::find($balance->account_id);
            if (! in_array($account->type, ['Activo', 'Pasivo', 'Patrimonio'])) {
                continue;
            }

            AccountPeriodBalance::updateOrCreate(
                [
                    'account_id' => $balance->account_id,
                    'fiscal_period_id' => $nextPeriod->id,
                ],
                [
                    'opening_balance' => $balance->closing_balance,
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'closing_balance' => $balance->closing_balance,
                ]
            );
        }
    }
}
