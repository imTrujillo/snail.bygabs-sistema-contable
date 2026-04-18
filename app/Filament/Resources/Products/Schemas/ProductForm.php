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

                        TextInput::make('cost_price')
                            ->label('Precio de costo')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')

                    ]),
            ]);
    }
}
