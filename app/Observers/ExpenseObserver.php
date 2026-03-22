<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Expense;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;

class ExpenseObserver
{
    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        $period = FiscalPeriod::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $entry = JournalEntry::create([
            'entry_date'       => $expense->expense_date,
            'description'      => "Gasto - {$expense->description}",
            'reference_type'   => 'expense',
            'reference_id'     => $expense->id,
            'fiscal_period_id' => $period->id,
            'created_by'       => Auth::user()->id,
        ]);

        $entry->lines()->create([
            'account_id'  => $expense->account_id,
            'debit'       => $expense->amount,
            'credit'      => 0,
            'description' => $expense->description,
        ]);

        $creditAccount = match ($expense->paid_with) {
            'efectivo'      => Account::where('code', '1102')->first(),
            'transferencia' => Account::where('code', '1101')->first(),
        };

        $entry->lines()->create([
            'account_id'  => $creditAccount->id,
            'debit'       => 0,
            'credit'      => $expense->amount,
            'description' => 'Pago de gasto',
        ]);
    }
}
