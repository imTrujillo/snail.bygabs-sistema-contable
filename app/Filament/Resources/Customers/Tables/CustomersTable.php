<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Exports\CustomerExporter;
use App\Filament\Imports\CustomerImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                ImportAction::make()
                    ->importer(CustomerImporter::class)
                    ->label('Importar'),
                ExportAction::make()
                    ->exporter(CustomerExporter::class)
                    ->label('Exportar'),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->description(fn($record) => $record->email),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->copyMessage('Teléfono copiado')
                    ->copyMessageDuration(1500),

                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->copyMessage('Email copiado')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true), // ya se muestra en descripción de name

                TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->notes)
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_contributor')
                    ->label('Contribuyente')
                    ->boolean()
                    ->trueIcon('heroicon-o-building-office-2')
                    ->falseIcon('heroicon-o-user')
                    ->trueColor('warning')
                    ->falseColor('info'),

                TextColumn::make('nrc')
                    ->label('NRC')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sales_count')
                    ->label('Ventas')
                    ->counts('sales')
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_contributor')
                    ->label('Tipo')
                    ->trueLabel('Solo contribuyentes')
                    ->falseLabel('Solo consumidores finales'),
            ])

            ->defaultSort('name', 'asc')

            ->filters([
                Filter::make('has_appointments')
                    ->label('Con citas')
                    ->query(fn($query) => $query->has('appointments')),

                Filter::make('no_appointments')
                    ->label('Sin citas')
                    ->query(fn($query) => $query->doesntHave('appointments')),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->paginated([10, 25, 50])

            ->extremePaginationLinks();
    }
}
