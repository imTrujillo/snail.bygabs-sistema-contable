<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class PurchaseObserver
{
    /**
     * Handle the Purchase "created" event.
     */
    public function created(Purchase $purchase): void
    {
        $period = FiscalPeriod::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $entry = JournalEntry::create([
            'entry_date' => $purchase->purchase_date,
            'description' => "Compra - {$purchase->taxDocument->document_number}",
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'fiscal_period_id' => $period->id,
            'user_id' => Auth::user()->id
        ]);

        $entry->lines()->create([
            'account_id'  => $purchase->account_id,
            'debit'       => $purchase->taxable_amount,
            'credit'      => 0,
            'description' => 'Compra de materiales',
        ]);

        if ($purchase->credit_fiscal > 0) {
            $entry->lines()->create([
                'account_id'  => Account::where('code', '1106')->first()->id, // IVA crédito fiscal (activo)
                'debit'       => $purchase->credit_fiscal,
                'credit'      => 0,
                'description' => 'IVA crédito fiscal',
            ]);
        }

        $entry->lines()->create([
            'account_id'  => Account::where('code', '2105')->first()->id, // Proveedores
            'debit'       => 0,
            'credit'      => $purchase->total_amount,
            'description' => 'Deuda con proveedor',
        ]);
    }
}
