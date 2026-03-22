<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options([
            'Activo' => 'Activo',
            'Pasivo' => 'Pasivo',
            'Patrimonio' => 'Patrimonio',
            'Ingreso' => 'Ingreso',
            'Costo' => 'Costo',
            'Gasto' => 'Gasto',
        ])
                    ->required(),
                Select::make('subtype')
                    ->options([
            'Corriente' => 'Corriente',
            'No Corriente' => 'No corriente',
            'Operativo' => 'Operativo',
            'Administrativo' => 'Administrativo',
            'Venta' => 'Venta',
            'Financiero' => 'Financiero',
            'No Operativo' => 'No operativo',
        ])
                    ->required(),
                TextInput::make('account_id')
                    ->numeric()
                    ->default(null),
                Toggle::make('is_group')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
            ]);
    }
}
