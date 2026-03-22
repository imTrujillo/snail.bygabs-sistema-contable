<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\Sale;
use App\Models\TaxDocument;
use Illuminate\Support\Facades\Auth;

use function Symfony\Component\Clock\now;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {

        $period = FiscalPeriod::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$period) return;

        $docNumber = $sale->taxDocument?->document_number ?? 'S/N';
        $ivaAmount  = $sale->total / 1.13 * 0.13;
        $baseAmount = $sale->total - $ivaAmount;

        // 3. Cuenta de cobro según pago
        $debitAccount = match ($sale->payment_method) {
            'Efectivo'      => Account::where('code', '1102')->first(),
            'Transferencia' => Account::where('code', '1101')->first(),
            'Tarjeta'       => Account::where('code', '1101')->first(),
        };

        if (!$debitAccount) return;

        // 4. Crear asiento
        $entry = JournalEntry::create([
            'entry_date'       => now(),
            'description'      => "Venta - {$docNumber}",
            'reference_type'   => 'sale',
            'reference_id'     => $sale->id,
            'fiscal_period_id' => $period->id,
            'user_id'          => Auth::check() ? Auth::id() : null,
        ]);

        $entry->lines()->create([
            'account_id' => $debitAccount->id,
            'debit' => $sale->total,
            'credit' => 0,
            'description' => 'Cobro por servicio'
        ]);

        $ventasAccount = Account::where('code', '4100')->first();
        if (!$ventasAccount) return;

        $entry->lines()->create([
            'account_id'  => $ventasAccount->id,
            'debit'       => 0,
            'credit'      => $baseAmount,
            'description' => 'Ingreso por servicio',
        ]);

        // CRÉDITO → IVA por pagar
        $ivaAccount = Account::where('code', '2104-01')->first();
        if ($ivaAccount && $ivaAmount > 0) {
            $entry->lines()->create([
                'account_id'  => $ivaAccount->id,
                'debit'       => 0,
                'credit'      => $ivaAmount,
                'description' => 'IVA débito fiscal',
            ]);
        }
    }
}
