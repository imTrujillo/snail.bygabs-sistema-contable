<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Expense;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryType;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        try {
            $period = FiscalPeriod::find(session('active_fiscal_period_id'));
            if (!$period) {
                Log::warning('ExpenseObserver: sin período activo', ['expense_id' => $expense->id]);
                return;
            }

            if (!$expense->payment_account_id) {
                Log::warning('ExpenseObserver: sin cuenta de pago', ['expense_id' => $expense->id]);
                return;
            }

            $entryType = JournalEntryType::where('name', 'Diario')->first();
            if (!$entryType) return;

            $isCCF = $expense->document_type === 'CCF';
            $iva   = $isCCF ? ($expense->iva_amount ?? round($expense->amount * 0.13, 2)) : 0;
            $total = $expense->amount + $iva;

            $entry = JournalEntry::create([
                'entry_date'            => $expense->expense_date,
                'description'           => "Gasto - {$expense->description}",
                'reference_type'        => 'expense',
                'reference_id'          => $expense->id,
                'fiscal_period_id'      => $period->id,
                'user_id'               => Auth::id(),
                'journal_entry_type_id' => $entryType->id,
            ]);

            // DÉBITO → cuenta de gasto
            $entry->lines()->create([
                'account_id'  => $expense->account_id,
                'debit'       => $expense->amount,
                'credit'      => 0,
                'description' => $expense->description,
            ]);

            // DÉBITO → IVA crédito fiscal (solo CCF)
            if ($isCCF && $iva > 0) {
                $ivaAccount = Account::where('code', '1106')->first();
                if ($ivaAccount) {
                    $entry->lines()->create([
                        'account_id'  => $ivaAccount->id,
                        'debit'       => $iva,
                        'credit'      => 0,
                        'description' => "IVA crédito fiscal - {$expense->description}",
                    ]);
                }
            }

            // CRÉDITO → cuenta de pago
            $entry->lines()->create([
                'account_id'  => $expense->payment_account_id,
                'debit'       => 0,
                'credit'      => $total,
                'description' => "Pago gasto" . ($isCCF ? " CCF - {$expense->supplier_name}" : ''),
            ]);

            $recipient = Auth::user();
            if ($recipient) {
                $body = "**{$expense->description}** por \${$expense->amount}";
                $body .= $isCCF ? " + IVA \${$iva} (CCF)" : " (FCF)";
                $body .= " — Pagado con: *{$expense->paid_with}*.";

                Notification::make()
                    ->title('Gasto registrado')
                    ->body($body)
                    ->warning()
                    ->sendToDatabase($recipient);
            }
        } catch (\Throwable $e) {
            Log::error('ExpenseObserver error: ' . $e->getMessage());
        }
    }
}
