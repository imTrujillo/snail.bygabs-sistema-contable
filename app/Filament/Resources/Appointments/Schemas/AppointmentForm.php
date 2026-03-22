<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\AppointmentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                DateTimePicker::make('appointment_date')
                    ->required(),
                Select::make('status')
                    ->options(AppointmentStatus::class)
                    ->required(),
                TextInput::make('notes')
                    ->default(null),
            ]);
    }
}
