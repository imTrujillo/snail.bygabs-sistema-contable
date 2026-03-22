<?php

namespace App\Filament\Resources\TaxDocuments\Pages;

use App\Filament\Resources\TaxDocuments\TaxDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxDocuments extends ListRecords
{
    protected static string $resource = TaxDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
