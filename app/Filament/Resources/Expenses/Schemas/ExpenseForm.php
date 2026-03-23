<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Detalle del Gasto')
                    ->description('Información principal del gasto registrado.')
                    ->icon('heroicon-o-receipt-percent')
                    ->columns(2)
                    ->schema([
                        TextInput::make('description')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('category')
                            ->label('Categoría')
                            ->required()
                            ->options([
                                'Operativo'      => 'Operativo',
                                'Administrativo' => 'Administrativo',
                                'Marketing'      => 'Marketing',
                                'Nomina'         => 'Nómina',
                                'Servicios'      => 'Servicios (agua, luz, internet)',
                                'Alquiler'       => 'Alquiler',
                                'Transporte'     => 'Transporte',
                                'Insumos'        => 'Insumos / Materiales',
                                'Impuestos'      => 'Impuestos',
                                'Otros'          => 'Otros',
                            ])
                            ->searchable()
                            ->columnSpan(1),

                        DateTimePicker::make('expense_date')
                            ->label('Fecha del gasto')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->default(now())
                            ->maxDate(now())
                            ->columnSpan(1),

                        TextInput::make('amount')
                            ->label('Monto')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('$')
                            ->columnSpanFull(),
                    ]),

                Section::make('Método de Pago')
                    ->description('Cómo y desde qué cuenta se realizó el gasto.')
                    ->icon('heroicon-o-credit-card')
                    ->columns(2)
                    ->schema([
                        ToggleButtons::make('paid_with')
                            ->label('Pagado con')
                            ->required()
                            ->inline()
                            ->options([
                                'Efectivo'      => 'Efectivo',
                                'Transferencia' => 'Transferencia',
                                'Tarjeta'       => 'Tarjeta',
                            ])
                            ->icons([
                                'Efectivo'      => 'heroicon-m-banknotes',
                                'Transferencia' => 'heroicon-m-arrow-right-circle',
                                'Tarjeta'       => 'heroicon-m-credit-card',
                            ])
                            ->colors([
                                'Efectivo'      => 'success',
                                'Transferencia' => 'info',
                                'Tarjeta'       => 'warning',
                            ])
                            ->columnSpanFull(),

                        Select::make('account_id')
                            ->label('Cuenta')
                            ->relationship(
                                name: 'account',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->where('is_group', false),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon('heroicon-m-building-library')
                            ->columnSpanFull(),
                    ]),

                Section::make('Notas')
                    ->description('Observaciones adicionales sobre el gasto.')
                    ->icon('heroicon-o-clipboard-document')
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notas')
                            ->placeholder('Detalles adicionales, número de factura...')
                            ->rows(3)
                            ->default(null)
                            ->columnSpanFull(),
                    ]),

            ]);
    }
}
