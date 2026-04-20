<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Identificación')
                    ->description('Código y nombre de la cuenta contable.')
                    ->icon('heroicon-o-book-open')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(20)
                            ->unique(table: 'accounts', column: 'code', ignoreRecord: true)
                            ->regex('/^\d{4}(-\d{2})*$/')   // formato 1101, 1101-01, 1101-01-01
                            ->helperText('Formato: 1101 o 1101-01')
                            ->prefixIcon('heroicon-m-hashtag'),

                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->minLength(3)
                            ->maxLength(255)
                            ->unique(table: 'accounts', column: 'name', ignoreRecord: true)
                            ->prefixIcon('heroicon-m-tag'),
                    ]),

                Section::make('Clasificación')
                    ->description('Tipo, subtipo y cuenta padre.')
                    ->icon('heroicon-o-squares-2x2')
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->label('Tipo')
                            ->required()
                            ->options([
                                'Activo'     => 'Activo',
                                'Pasivo'     => 'Pasivo',
                                'Patrimonio' => 'Patrimonio',
                                'Ingreso'    => 'Ingreso',
                                'Costo'      => 'Costo',
                                'Gasto'      => 'Gasto',
                            ])
                            ->prefixIcon('heroicon-m-rectangle-stack')
                            ->columnSpan(1),

                        Select::make('subtype')
                            ->label('Subtipo')
                            ->required()
                            ->options([
                                'Corriente'      => 'Corriente',
                                'No Corriente'   => 'No Corriente',
                                'Operativo'      => 'Operativo',
                                'Administrativo' => 'Administrativo',
                                'Venta'          => 'Venta',
                                'Financiero'     => 'Financiero',
                                'No Operativo'   => 'No Operativo',
                            ])
                            ->prefixIcon('heroicon-m-adjustments-horizontal')
                            ->columnSpan(1),

                        Select::make('account_id')
                            ->label('Cuenta Padre')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->prefixIcon('heroicon-m-arrow-up-circle')
                            ->helperText('Déjalo vacío si es una cuenta raíz')
                            ->columnSpanFull(),
                    ]),

                Section::make('Opciones')
                    ->description('Configuración adicional de la cuenta.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_group')
                            ->label('Es cuenta de grupo')
                            ->helperText('Las cuentas de grupo agrupan otras cuentas y no reciben movimientos directos.')
                            ->columnSpan(1),

                        Toggle::make('is_default')
                            ->label('Cuenta por defecto')
                            ->helperText('Se usará automáticamente en operaciones del sistema.')
                            ->columnSpan(1),
                    ]),

            ]);
    }
}
