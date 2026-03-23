<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información de la Venta')
                    ->description('Cliente, cita asociada y tipo de documento fiscal.')
                    ->icon('heroicon-o-shopping-bag')
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->label('Nombre')->required(),
                                TextInput::make('phone')->label('Teléfono')->tel(),
                                TextInput::make('email')->label('Email')->email(),
                            ])
                            ->prefixIcon('heroicon-m-user')
                            ->columnSpanFull(),

                        Select::make('appointment_id')
                            ->label('Cita asociada')
                            ->relationship(
                                name: 'appointment',
                                titleAttribute: 'appointment_date',
                                modifyQueryUsing: fn($query) => $query->orderBy('appointment_date', 'desc'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => $record->appointment_date->format('d/m/Y H:i')
                                    . ' — ' . ($record->customer->name ?? 'Sin cliente')
                            )
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->placeholder('Opcional')
                            ->prefixIcon('heroicon-m-calendar-days')
                            ->columnSpanFull(),

                        ToggleButtons::make('document_type')
                            ->label('Tipo de documento')
                            ->required()
                            ->inline()
                            ->options([
                                'FCF' => 'Consumidor Final (FCF)',
                                'CCF' => 'Crédito Fiscal (CCF)',
                            ])
                            ->icons([
                                'FCF' => 'heroicon-m-user',
                                'CCF' => 'heroicon-m-building-office-2',
                            ])
                            ->colors([
                                'FCF' => 'info',
                                'CCF' => 'warning',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Pago')
                    ->description('Monto total y método de pago.')
                    ->icon('heroicon-o-banknotes')
                    ->columns(2)
                    ->schema([
                        TextInput::make('total')
                            ->label('Total')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('$')
                            ->columnSpanFull(),

                        ToggleButtons::make('payment_method')
                            ->label('Método de pago')
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
                    ]),

            ]);
    }
}
