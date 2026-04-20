<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryType;
use App\Models\Purchase;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class PurchaseObserver
{
    public function saved(Purchase $purchase): void
    {
        // ✅ Solo al crear, no en cada edición
        if (!$purchase->wasRecentlyCreated) return;

        // ✅ Evitar duplicados si se llama dos veces
        if ($purchase->journalEntry()->exists()) return;

        $period = FiscalPeriod::find(session('active_fiscal_period_id'));
        if (!$period) return;

        // ✅ Ahora sí tiene valor porque saved se dispara después de persistir todo
        $docNumber = $purchase->document_number ?? 'S/N';

        $entryType = JournalEntryType::where('name', 'Diario')->firstOrFail();

        $entry = JournalEntry::create([
            'entry_date'            => $purchase->purchase_date,
            'description'           => "Compra - {$docNumber}",
            'reference_type'        => 'purchase',
            'reference_id'          => $purchase->id,
            'fiscal_period_id'      => $period->id,
            'user_id'               => Auth::check() ? Auth::id() : null,
            'journal_entry_type_id' => $entryType->id,
        ]);

        $entry->lines()->create([
            'account_id'  => $purchase->account_id,
            'debit'       => $purchase->taxable_amount,
            'credit'      => 0,
            'description' => 'Compra de materiales',
        ]);

        if ($purchase->credit_fiscal > 0) {
            $ivaAccount = Account::where('code', '1106')->first();
            if ($ivaAccount) {
                $entry->lines()->create([
                    'account_id'  => $ivaAccount->id,
                    'debit'       => $purchase->credit_fiscal,
                    'credit'      => 0,
                    'description' => 'IVA crédito fiscal',
                ]);
            }
        }

        $proveedoresAccount = Account::where('code', '2105')->first();
        if (!$proveedoresAccount) return;

        $entry->lines()->create([
            'account_id'  => $proveedoresAccount->id,
            'debit'       => 0,
            'credit'      => $purchase->total_amount,
            'description' => 'Deuda con proveedor',
        ]);

        $recipient = Auth::user();
        if ($recipient) {
            $proveedor = $purchase->supplier?->name ?? 'Sin proveedor';

            Notification::make()
                ->title('Compra registrada')
                ->body("**{$docNumber}** de *{$proveedor}* por \${$purchase->total_amount}. Crédito fiscal: \${$purchase->credit_fiscal}.")
                ->success()
                ->sendToDatabase($recipient);
        }
    }
}
