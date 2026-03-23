<?php

namespace App\Filament\Resources\FiscalPeriods\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FiscalPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Período')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-calendar')
                    ->description(
                        fn($record): string =>
                        \Carbon\Carbon::parse($record->start_date)->format('d/m/Y')
                            . ' → '
                            . \Carbon\Carbon::parse($record->end_date)->format('d/m/Y')
                    ),

                TextColumn::make('duration')
                    ->label('Duración')
                    ->state(
                        fn($record): string =>
                        \Carbon\Carbon::parse($record->start_date)
                            ->diffInDays(\Carbon\Carbon::parse($record->end_date)) + 1 . ' días'
                    )
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-m-clock'),

                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('end_date')
                    ->label('Cierre')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_closed')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-m-lock-closed')
                    ->falseIcon('heroicon-m-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->tooltip(fn($state): string => $state ? 'Período cerrado' : 'Período abierto'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('start_date', 'desc')

            ->filters([
                TernaryFilter::make('is_closed')
                    ->label('Estado del período')
                    ->placeholder('Todos')
                    ->trueLabel('Cerrados')
                    ->falseLabel('Abiertos'),

                Filter::make('current_year')
                    ->label('Año actual')
                    ->query(fn($query) => $query->whereYear('start_date', now()->year)),

                Filter::make('active_period')
                    ->label('Período activo ahora')
                    ->query(
                        fn($query) => $query
                            ->where('is_closed', false)
                            ->whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now())
                    ),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
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
