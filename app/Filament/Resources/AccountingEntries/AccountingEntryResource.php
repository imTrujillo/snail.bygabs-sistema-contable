<?php

namespace App\Filament\Resources\AccountingEntries;

use App\Filament\Resources\AccountingEntries\Pages\CreateAccountingEntry;
use App\Filament\Resources\AccountingEntries\Pages\EditAccountingEntry;
use App\Filament\Resources\AccountingEntries\Pages\ListAccountingEntries;
use App\Filament\Resources\AccountingEntries\Schemas\AccountingEntryForm;
use App\Filament\Resources\AccountingEntries\Tables\AccountingEntriesTable;
use App\Models\AccountingEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AccountingEntryResource extends Resource
{
    protected static ?string $model = AccountingEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'type';

    public static function form(Schema $schema): Schema
    {
        return AccountingEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountingEntriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountingEntries::route('/'),
            'create' => CreateAccountingEntry::route('/create'),
            'edit' => EditAccountingEntry::route('/{record}/edit'),
        ];
    }
}
