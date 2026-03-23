<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\Purchase;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class PurchaseObserver
{
    public function created(Purchase $purchase): void
    {
        // 1. Guard: periodo activo
        $period = FiscalPeriod::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$period) return;

        // taxDocument ya existe, lo creó el Resource
        $docNumber = $purchase->taxDocument?->document_number ?? 'S/N';

        $entry = JournalEntry::create([
            'entry_date'       => $purchase->purchase_date,
            'description'      => "Compra - {$docNumber}",
            'reference_type'   => 'purchase',
            'reference_id'     => $purchase->id,
            'fiscal_period_id' => $period->id,
            'user_id'          => Auth::check() ? Auth::id() : null,
        ]);

        // 5. DÉBITO → cuenta destino (inventario o gasto)
        $entry->lines()->create([
            'account_id'  => $purchase->account_id,
            'debit'       => $purchase->taxable_amount,
            'credit'      => 0,
            'description' => 'Compra de materiales',
        ]);

        // 6. DÉBITO → IVA crédito fiscal (solo si tiene CCF)
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

        // 7. CRÉDITO → Proveedores
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
            $docNumber  = $purchase->taxDocument?->document_number ?? 'S/N';
            $proveedor  = $purchase->supplier?->name ?? 'Sin proveedor';

            Notification::make()
                ->title('Compra registrada')
                ->body("**{$docNumber}** de *{$proveedor}* por \${$purchase->total_amount}. Crédito fiscal: \${$purchase->credit_fiscal}.")
                ->success()
                ->sendToDatabase($recipient);
        }
    }
}
