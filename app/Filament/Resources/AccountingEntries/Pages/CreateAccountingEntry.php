<?php

namespace App\Filament\Resources\AccountingEntries\Pages;

use App\Filament\Resources\AccountingEntries\AccountingEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountingEntry extends CreateRecord
{
    protected static string $resource = AccountingEntryResource::class;
}
