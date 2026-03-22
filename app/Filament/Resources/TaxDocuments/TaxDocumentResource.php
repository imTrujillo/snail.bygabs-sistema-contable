<?php

namespace App\Filament\Resources\TaxDocuments;

use App\Filament\Resources\TaxDocuments\Pages\CreateTaxDocument;
use App\Filament\Resources\TaxDocuments\Pages\EditTaxDocument;
use App\Filament\Resources\TaxDocuments\Pages\ListTaxDocuments;
use App\Filament\Resources\TaxDocuments\Schemas\TaxDocumentForm;
use App\Filament\Resources\TaxDocuments\Tables\TaxDocumentsTable;
use App\Models\TaxDocument;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class TaxDocumentResource extends Resource
{
    protected static ?string $model = TaxDocument::class;

    protected static string|UnitEnum|null $navigationGroup = 'Fiscal';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'type';

    // Solo muestra, no permite crear ni editar documentos generados automáticamente
    public static function canCreate(): bool
    {
        return false; // ← deshabilita el botón "Crear"
    }

    // Table — lo que el usuario VE
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('document_number')->label('Documento')->searchable(),
            TextColumn::make('type')->label('Tipo')->badge(),
            TextColumn::make('issue_date')->label('Fecha')->date(),
            TextColumn::make('client.name')->label('Cliente'),
            TextColumn::make('supplier.name')->label('Proveedor'),
            TextColumn::make('taxable_amount')->label('Gravado')->money('USD'),
            TextColumn::make('iva_amount')->label('IVA')->money('USD'),
            TextColumn::make('total_amount')->label('Total')->money('USD'),
            IconColumn::make('is_voided')->label('Anulado')->boolean(),
        ])
            ->filters([
                SelectFilter::make('type')->options(['FCF' => 'FCF', 'CCF' => 'CCF']),
                Filter::make('is_voided')->query(fn($q) => $q->where('is_voided', false)),
            ]);
    }

    // Solo permite ver detalle y anular
    public static function getActions(): array
    {
        return [
            Action::make('anular')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn(TaxDocument $record) => $record->update([
                    'is_voided' => true,
                    'voided_at' => now(),
                ]))
                ->visible(fn(TaxDocument $record) => !$record->is_voided),
        ];
    }


    public static function form(Schema $schema): Schema
    {
        return TaxDocumentForm::configure($schema);
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
