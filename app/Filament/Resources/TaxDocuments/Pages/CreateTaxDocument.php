<?php

namespace App\Filament\Resources\TaxDocuments\Pages;

use App\Filament\Resources\TaxDocuments\TaxDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxDocument extends CreateRecord
{
    protected static string $resource = TaxDocumentResource::class;
}
