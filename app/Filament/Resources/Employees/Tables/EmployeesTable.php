<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Filament\Exports\EmployeeExporter;
use App\Filament\Imports\EmployeeImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                ImportAction::make()
                    ->importer(EmployeeImporter::class)
                    ->label('Importar'),
                ExportAction::make()
                    ->exporter(EmployeeExporter::class)
                    ->label('Exportar'),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('position')
                    ->label('Cargo')
                    ->searchable(),

                TextColumn::make('base_salary')
                    ->label('Salario base')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('pay_frequency')
                    ->label('Frecuencia')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Mensual' => 'info',
                        'Quincenal' => 'warning',
                        'Semanal' => 'success',
                    }),
                TextColumn::make('dui')
                    ->label('DUI')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                TextColumn::make('hire_date')
                    ->label('Contratado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),

                SelectFilter::make('pay_frequency')
                    ->label('Frecuencia')
                    ->options([
                        'Semanal' => 'Semanal',
                        'Quincenal' => 'Quincenal',
                        'Mensual' => 'Mensual',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(EmployeeExporter::class),
                ]),
            ]);
    }
}
