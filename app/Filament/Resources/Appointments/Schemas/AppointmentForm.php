<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\AppointmentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Ramsey\Collection\Set;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
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
                            ->createOptionForm([
                                \Filament\Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required(),
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
                            ->minDate(now())
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
                            ->inline(),

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
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $service = \App\Models\Service::find($get('service_id'));
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
