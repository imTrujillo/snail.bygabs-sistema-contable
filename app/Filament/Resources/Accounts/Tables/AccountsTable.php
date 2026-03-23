<?php

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->width('100px'),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
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

                TextColumn::make('parent.name')
                    ->label('Cuenta padre')
                    ->placeholder('— Raíz —')
                    ->searchable(),

                IconColumn::make('is_group')
                    ->label('Es grupo')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_default')
                    ->label('Por defecto')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('code')
            ->modifyQueryUsing(fn($query) => $query->where('is_group', false))
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_group')
                    ->label('Mostrar grupos')
                    ->placeholder('Sin grupos')
                    ->trueLabel('Solo grupos')
                    ->falseLabel('Sin grupos'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
