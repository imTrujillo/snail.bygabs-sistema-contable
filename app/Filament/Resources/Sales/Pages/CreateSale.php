<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\TaxDocument;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $documentType = $data['document_type'];
        unset($data['document_type']); // ← no va a la tabla sales

        // 1. Crear la venta
        $sale = static::getModel()::create($data);

        // 2. Generar correlativo por serie
        $lastCorrelative = TaxDocument::where('series', $documentType)
            ->max('correlative_number') ?? 0;
        $newCorrelative = $lastCorrelative + 1;

        $taxableAmount = $sale->total / 1.13;
        $ivaAmount     = $sale->total - $taxableAmount;

        // 3. Crear TaxDocument
        $taxDocument = TaxDocument::create([
            'type'               => $documentType,
            'series'             => $documentType,
            'correlative_number' => $newCorrelative,
            'document_number'    => "{$documentType}-" . str_pad($newCorrelative, 6, '0', STR_PAD_LEFT),
            'issue_date'         => now(),
            'customer_id'          => $sale->customer_id,
            'reference_id'       => $sale->id,
            'reference_type'     => 'sale',
            'exempt_amount'      => 0,
            'non_taxable_amount' => 0,
            'taxable_amount'     => $taxableAmount,
            'iva_amount'         => $ivaAmount,
            'total_amount'       => $sale->total,
            'is_voided'          => false,
        ]);

        // 4. Vincular a la venta
        $sale->update(['tax_document_id' => $taxDocument->id]);

        return $sale;
    }
}
