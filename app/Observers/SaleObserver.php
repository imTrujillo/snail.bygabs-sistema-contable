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
        $this->generateTaxDocument($sale);

        $period = FiscalPeriod::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        $ivaAmount = $sale->total / 1.13 * 0.13;
        $baseAmount = $sale->total - $ivaAmount;

        $debitAccount = match ($sale->payment_method) {
            'Efectivo' => Account::where('code', '1102')->first(),
            'Transferencia' => Account::where('code', '1101')->first(),
            'Tarjeta' => Account::where('code', '1101')->first(),
        };

        $entry = JournalEntry::create([
            'entry_date' => now(),
            'description' => "Venta - {$sale->taxDocument->document_number}",
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'fiscal_period_id' => $period->id,
            'user_id' => Auth::user()->id
        ]);

        $entry->lines()->create([
            'account_id' => $debitAccount->id,
            'debit' => $sale->total,
            'credit' => 0,
            'description' => 'Cobro por servicio'
        ]);

        $entry->lines()->create([
            'account_id' => Account::where('code', '4100')->first()->id,
            'debit' => 0,
            'credit' => $baseAmount,
            'description' => 'Ingreso por servicio'
        ]);

        if ($sale->taxDocument->taxable_amount > 0) {
            $entry->lines()->create([
                'account_id' => Account::where('code', '2104-01')->first()->id,
                'debit'       => 0,
                'credit'      => $ivaAmount,
                'description' => 'IVA débito fiscal',
            ]);
        }
    }

    private function generateTaxDocument(Sale $sale): void
    {
        $type   = $sale->document_type;
        $series = $type;

        $lastCorrelative = TaxDocument::where('series', $series)->max('correlative_number') ?? 0;
        $newCorrelative  = $lastCorrelative + 1;

        $doc = TaxDocument::create([
            'type'               => $type,
            'series'             => $series,
            'correlative_number' => $newCorrelative,
            'document_number'    => "{$series}-" . str_pad($newCorrelative, 6, '0', STR_PAD_LEFT),
            'issue_date'         => now(),
            'client_id'          => $sale->client_id,
            'reference_id'       => $sale->id,
            'reference_type'     => 'sale',
            'taxable_amount'     => $sale->total / 1.13,
            'iva_amount'         => $sale->total / 1.13 * 0.13,
            'total_amount'       => $sale->total,
        ]);

        $sale->update(['tax_document_id' => $doc->id]);
    }
}
