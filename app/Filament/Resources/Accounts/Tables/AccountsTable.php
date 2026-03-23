<?php

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-hashtag')
                    ->description(
                        fn($record) => $record->parent?->name
                            ? 'Padre: ' . $record->parent->name
                            : '— Raíz —'
                    ),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Activo'     => 'success',
                        'Pasivo'     => 'danger',
                        'Patrimonio' => 'warning',
                        'Ingreso'    => 'info',
                        'Costo'      => 'gray',
                        'Gasto'      => 'gray',
                        default      => 'gray',
                    }),

                TextColumn::make('subtype')
                    ->label('Subtipo')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_group')
                    ->label('Grupo')
                    ->boolean()
                    ->trueIcon('heroicon-m-folder-open')
                    ->falseIcon('heroicon-m-document')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_default')
                    ->label('Por defecto')
                    ->boolean()
                    ->trueIcon('heroicon-m-star')
                    ->falseIcon('heroicon-m-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('code')

            ->modifyQueryUsing(fn($query) => $query->where('is_group', false))

            ->filters([
                TernaryFilter::make('is_group')
                    ->label('Tipo de cuenta')
                    ->placeholder('Todas')
                    ->trueLabel('Solo grupos')
                    ->falseLabel('Sin grupos'),

                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'Activo'     => 'Activo',
                        'Pasivo'     => 'Pasivo',
                        'Patrimonio' => 'Patrimonio',
                        'Ingreso'    => 'Ingreso',
                        'Costo'      => 'Costo',
                        'Gasto'      => 'Gasto',
                    ]),

                SelectFilter::make('subtype')
                    ->label('Subtipo')
                    ->options([
                        'Corriente'      => 'Corriente',
                        'No Corriente'   => 'No Corriente',
                        'Operativo'      => 'Operativo',
                        'Administrativo' => 'Administrativo',
                        'Venta'          => 'Venta',
                        'Financiero'     => 'Financiero',
                        'No Operativo'   => 'No Operativo',
                    ]),
            ])

            ->recordActions([
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
