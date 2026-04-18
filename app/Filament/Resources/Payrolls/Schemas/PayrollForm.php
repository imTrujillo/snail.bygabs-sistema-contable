<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use App\Models\FiscalPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos de la Planilla')
                ->columns(2)
                ->schema([
                    Select::make('fiscal_period_id')
                        ->label('Período fiscal')
                        ->options(FiscalPeriod::where('is_closed', false)->pluck('name', 'id'))
                        ->required(),

                    DatePicker::make('pay_date')
                        ->label('Fecha de pago')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    Select::make('period_type')
                        ->label('Tipo de período')
                        ->options([
                            'Semanal'   => 'Semanal',
                            'Quincenal' => 'Quincenal',
                            'Mensual'   => 'Mensual',
                        ])
                        ->required(),
                ]),
        ]);
    }
}
