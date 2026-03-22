<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\TaxDocument;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // 1. Extraer campos del documento (no van a la tabla purchases)
        $documentNumber = $data['document_number'];
        $documentType   = $data['document_type'];

        unset($data['document_number'], $data['document_type']);

        // 2. Crear la compra sin esos campos
        $purchase = static::getModel()::create($data);

        // 3. Crear el TaxDocument con los datos del form
        $taxDocument = TaxDocument::create([
            'type'               => $documentType,
            'series'             => $documentType,
            'correlative_number' => 0,
            'document_number'    => $documentNumber,
            'issue_date'         => $purchase->purchase_date,
            'supplier_id'        => $purchase->supplier_id,
            'reference_id'       => $purchase->id,
            'reference_type'     => 'purchase',
            'exempt_amount'      => $purchase->exempt_amount  ?? 0,
            'non_taxable_amount' => $purchase->non_taxable_amount ?? 0,
            'taxable_amount'     => $purchase->taxable_amount ?? 0,
            'iva_amount'         => $purchase->credit_fiscal  ?? 0,
            'total_amount'       => $purchase->total_amount,
            'is_voided'          => false,
        ]);

        // 4. Vincular el documento a la compra
        $purchase->update(['tax_document_id' => $taxDocument->id]);

        return $purchase;
    }
}
