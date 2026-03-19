<?php

namespace App\Filament\Resources\AccountingEntries\Pages;

use App\Filament\Resources\AccountingEntries\AccountingEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountingEntries extends ListRecords
{
    protected static string $resource = AccountingEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
