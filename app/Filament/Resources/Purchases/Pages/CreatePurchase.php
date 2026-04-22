<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Purchases\PurchaseResource;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $this->form->getRawState()['items'] ?? [];

        $taxable = collect($items)
            ->sum(fn($item) => floatval($item['subtotal'] ?? 0));

        $taxable = round($taxable, 2);
        $iva     = round($taxable * 0.13, 2);

        $data['taxable_amount'] = $taxable;
        $data['credit_fiscal']  = $iva;
        $data['total_amount']   = round(
            floatval($data['exempt_amount'] ?? 0) +
                floatval($data['non_taxable_amount'] ?? 0) +
                $taxable + $iva,
            2
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->record->items as $item) {
            if ($item->product_id) {
                Product::where('id', $item->product_id)
                    ->increment('stock', $item->quantity);
            }
        }
    }
}
