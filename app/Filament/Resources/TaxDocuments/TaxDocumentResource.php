<?php

namespace App\Filament\Resources\TaxDocuments;

use App\Filament\Resources\TaxDocuments\Pages\CreateTaxDocument;
use App\Filament\Resources\TaxDocuments\Pages\EditTaxDocument;
use App\Filament\Resources\TaxDocuments\Pages\ListTaxDocuments;
use App\Filament\Resources\TaxDocuments\Schemas\TaxDocumentForm;
use App\Filament\Resources\TaxDocuments\Tables\TaxDocumentsTable;
use App\Models\TaxDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TaxDocumentResource extends Resource
{
    protected static ?string $model = TaxDocument::class;

    protected static string|UnitEnum|null $navigationGroup = 'Fiscal';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'type';

    public static function form(Schema $schema): Schema
    {
        return TaxDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxDocumentsTable::configure($table);
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
            'index' => ListTaxDocuments::route('/'),
            'create' => CreateTaxDocument::route('/create'),
            'edit' => EditTaxDocument::route('/{record}/edit'),
        ];
    }
}
