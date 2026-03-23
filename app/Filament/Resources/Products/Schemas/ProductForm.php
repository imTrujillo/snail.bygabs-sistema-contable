<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información del Producto')
                    ->description('Datos generales del producto o servicio.')
                    ->icon('heroicon-o-cube')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('unit')
                            ->label('Unidad de medida')
                            ->required()
                            ->options([
                                'unidad'   => 'Unidad',
                                'kg'       => 'Kilogramo (kg)',
                                'g'        => 'Gramo (g)',
                                'lb'       => 'Libra (lb)',
                                'l'        => 'Litro (l)',
                                'ml'       => 'Mililitro (ml)',
                                'caja'     => 'Caja',
                                'paquete'  => 'Paquete',
                                'servicio' => 'Servicio',
                                'hora'     => 'Hora',
                            ])
                            ->searchable()
                            ->columnSpan(1),

                        TextInput::make('stock')
                            ->label('Stock actual')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->default(0)
                            ->prefixIcon('heroicon-m-archive-box')
                            ->columnSpan(1),
                    ]),

                Section::make('Precios')
                    ->description('Precio de costo y precio de venta.')
                    ->icon('heroicon-o-banknotes')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cost_price')
                            ->label('Precio de costo')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Sugerir margen del 30% automáticamente si sale_price está vacío
                                if (! $get('sale_price') && $state > 0) {
                                    $set('sale_price', round($state * 1.30, 2));
                                }
                            }),

                        TextInput::make('sale_price')
                            ->label('Precio de venta')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->helperText(
                                fn($get): string =>
                                $get('cost_price') > 0 && $get('sale_price') > 0
                                    ? 'Margen: ' . round((($get('sale_price') - $get('cost_price')) / $get('cost_price')) * 100, 1) . '%'
                                    : ''
                            ),
                    ]),

            ]);
    }
}
