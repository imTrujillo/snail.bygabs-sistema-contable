<?php

namespace App\Filament\Resources\TaxDocuments\Pages;

use App\Filament\Resources\TaxDocuments\TaxDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTaxDocument extends EditRecord
{
    protected static string $resource = TaxDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
