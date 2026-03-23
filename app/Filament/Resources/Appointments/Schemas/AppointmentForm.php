<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\AppointmentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
            ]);
    }
}
