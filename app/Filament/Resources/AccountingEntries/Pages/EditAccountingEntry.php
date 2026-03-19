<?php

namespace App\Filament\Resources\AccountingEntries\Pages;

use App\Filament\Resources\AccountingEntries\AccountingEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountingEntry extends EditRecord
{
    protected static string $resource = AccountingEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
