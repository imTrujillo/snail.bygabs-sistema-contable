<?php

namespace App\Filament\Resources\Appointments\Schemas;

use BackedEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentInfolist
{
    public static ?string $heading = 'Vista de la Cita';

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Información de la Cita')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Cliente'),
                        TextEntry::make('user.name')
                            ->label('Responsable'),
                        TextEntry::make('appointment_date')
                            ->label('Fecha y hora')
                            ->dateTime('d/m/Y H:i')
                            ->columnSpanFull(),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->formatStateUsing(fn (mixed $state): string => $state instanceof BackedEnum ? (string) $state->value : (string) $state),
                        TextEntry::make('payment_method')
                            ->label('Método de pago')
                            ->placeholder('—'),
                        TextEntry::make('notes')
                            ->label('Notas')
                            ->placeholder('Sin notas')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Servicios')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        RepeatableEntry::make('appointmentServices')
                            ->label('')
                            ->schema([
                                TextEntry::make('service.name')
                                    ->label('Servicio'),
                                TextEntry::make('price')
                                    ->label('Precio')
                                    ->money('USD'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
