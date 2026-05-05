<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\AppointmentStatus;
use App\Models\FiscalPeriod;
use App\Models\Service;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $period = FiscalPeriod::find(session('active_fiscal_period_id'));

        return $schema
            ->components([

                Section::make('Información de la Cita')
                    ->description('Datos principales de la cita agendada.')
                    ->icon('heroicon-o-calendar-days')

                    ->schema([
                        Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionModalHeading('Nuevo cliente')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->minLength(3)
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->required()
                                    ->regex('/^[67]\d{7}$/')
                                    ->maxLength(8)
                                    ->helperText('Ej: 71234567'),

                                TextInput::make('email')
                                    ->label('Correo electrónico')
                                    ->email()
                                    ->unique(table: 'customers', column: 'email', ignoreRecord: true)
                                    ->maxLength(255),

                                Textarea::make('notes')
                                    ->label('Notas')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Toggle::make('is_contributor')
                                    ->label('Es contribuyente (emite CCF)')
                                    ->live()
                                    ->columnSpanFull(),

                                TextInput::make('nrc')
                                    ->label('NRC')
                                    ->visible(fn (Get $get) => $get('is_contributor'))
                                    ->required(fn (Get $get) => $get('is_contributor'))
                                    ->unique(table: 'customers', column: 'nrc', ignoreRecord: true)
                                    ->regex('/^\d{1,6}-\d$/')
                                    ->helperText('Formato: 123456-7')
                                    ->maxLength(20),

                                TextInput::make('nit')
                                    ->label('NIT')
                                    ->visible(fn (Get $get) => $get('is_contributor'))
                                    ->required(fn (Get $get) => $get('is_contributor'))
                                    ->unique(table: 'customers', column: 'nit', ignoreRecord: true)
                                    ->regex('/^\d{4}-\d{6}-\d{3}-\d$/')
                                    ->helperText('Formato: 0614-290786-102-3')
                                    ->maxLength(17),
                            ])
                            ->columnSpan(1),

                        Select::make('user_id')
                            ->label('Responsable')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        DateTimePicker::make('appointment_date')
                            ->label('Fecha y Hora')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minutesStep(15)
                            ->default($period?->start_date ?? now())
                            ->minDate($period?->start_date ?? now())
                            ->maxDate($period?->end_date ?? now())
                            ->columnSpanFull(),
                    ]),

                Section::make('Estado y Notas')
                    ->description('Estado actual de la cita y observaciones adicionales.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(1)
                    ->schema([
                        ToggleButtons::make('status')
                            ->label('Estado')
                            ->options(AppointmentStatus::class)
                            ->required()
                            ->inline()
                            ->hiddenOn('create'),

                        ToggleButtons::make('payment_method')
                            ->label('Método de pago (al completar)')
                            ->helperText('Se usará al marcar la cita como completada y generar la venta.')
                            ->inline()
                            ->options([
                                'Efectivo' => 'Efectivo',
                                'Transferencia' => 'Transferencia',
                                'Tarjeta' => 'Tarjeta',
                            ])
                            ->icons([
                                'Efectivo' => 'heroicon-m-banknotes',
                                'Transferencia' => 'heroicon-m-arrow-right-circle',
                                'Tarjeta' => 'heroicon-m-credit-card',
                            ])
                            ->default('Efectivo')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->placeholder('Observaciones, indicaciones especiales...')
                            ->rows(4)
                            ->default(null)
                            ->columnSpanFull(),
                    ]),

                Section::make('Servicios')
                    ->description('Selecciona los servicios de esta cita.')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        Repeater::make('appointmentServices')
                            ->relationship('appointmentServices')
                            ->label('Servicios')
                            ->schema([
                                Select::make('service_id')
                                    ->label('Servicio')
                                    ->relationship('service', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionModalHeading('Nuevo servicio')
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nombre del servicio')
                                            ->required()
                                            ->minLength(3)
                                            ->maxLength(255)
                                            ->unique(table: 'services', column: 'name', ignoreRecord: true)
                                            ->columnSpanFull(),

                                        Textarea::make('description')
                                            ->label('Descripción')
                                            ->required()
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('Describe qué incluye este servicio...')
                                            ->columnSpanFull(),

                                        TextInput::make('price')
                                            ->label('Precio')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->maxValue(99999.99)
                                            ->step(0.01)
                                            ->prefix('$'),

                                        TextInput::make('duration_minutes')
                                            ->label('Duración (minutos)')
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(480)
                                            ->integer()
                                            ->suffix('min'),
                                    ])
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $service = Service::find($get('service_id'));
                                        $set('price', $service?->price ?? 0);
                                    }),

                                TextInput::make('price')
                                    ->label('Precio')
                                    ->numeric()
                                    ->readOnly()
                                    ->prefix('$')
                                    ->required()
                                    ->step(0.01),
                            ])
                            ->columns(2)
                            ->addActionLabel('Agregar servicio')
                            ->minItems(1),
                    ]),

            ]);
    }
}
