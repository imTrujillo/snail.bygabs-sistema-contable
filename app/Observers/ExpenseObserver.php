<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Expense;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        // 1. Guard: si no hay periodo activo, no crear asiento
        $period = FiscalPeriod::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$period) return;

        // 2. Crear asiento contable
        $entry = JournalEntry::create([
            'entry_date'       => $expense->expense_date,
            'description'      => "Gasto - {$expense->description}",
            'reference_type'   => 'expense',
            'reference_id'     => $expense->id,
            'fiscal_period_id' => $period->id,
            'user_id'          => Auth::check() ? Auth::id() : null,
        ]);

        // 3. Línea DÉBITO → cuenta de gasto seleccionada
        $entry->lines()->create([
            'account_id'  => $expense->account_id,
            'debit'       => $expense->amount,
            'credit'      => 0,
            'description' => $expense->description,
        ]);

        // 4. Línea CRÉDITO → caja o banco según método de pago
        $creditAccount = match ($expense->paid_with) {
            'Efectivo'      => Account::where('code', '1102')->first(),
            'Transferencia' => Account::where('code', '1101')->first(),
            'Tarjeta'       => Account::where('code', '1101')->first(),
            default         => Account::where('code', '1102')->first(), // ← fallback
        };

        // 5. Guard: si la cuenta no existe en el plan de cuentas
        if (!$creditAccount) return;

        $entry->lines()->create([
            'account_id'  => $creditAccount->id,
            'debit'       => 0,
            'credit'      => $expense->amount,
            'description' => 'Pago de gasto',
        ]);

        $recipient = Auth::user();
        if ($recipient) {
            Notification::make()
                ->title('Gasto registrado')
                ->body("**{$expense->description}** por \${$expense->amount} — Pagado con: *{$expense->paid_with}*.")
                ->warning()
                ->sendToDatabase($recipient);
        }
    }
}
