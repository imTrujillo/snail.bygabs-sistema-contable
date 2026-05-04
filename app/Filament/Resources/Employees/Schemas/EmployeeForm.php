<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\FiscalPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        $period = FiscalPeriod::find(session('active_fiscal_period_id'));

        return $schema->components([

            Section::make('Información Personal')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre completo')
                        ->required()
                        ->minLength(3)
                        ->maxLength(100)
                        ->columnSpanFull(),

                    TextInput::make('position')
                        ->label('Cargo')
                        ->minLength(2)
                        ->maxLength(100)
                        ->placeholder('Ej: Cajero, Técnico, Vendedor')
                        ->columnSpan(1),

                    DatePicker::make('hire_date')
                        ->label('Fecha de contratación')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default($period?->start_date ?? now())
                        ->minDate($period?->start_date ?? now())   // ← dentro del período
                        ->maxDate($period?->end_date ?? now())
                        ->columnSpan(1),

                    TextInput::make('dui')
                        ->label('DUI')
                        ->unique(table: 'employees', column: 'dui', ignoreRecord: true)
                        ->regex('/^\d{8}-\d$/')
                        ->helperText('Formato: 00000000-0')
                        ->placeholder('00000000-0')
                        ->maxLength(10)
                        ->columnSpan(1),

                    Toggle::make('is_active')
                        ->label('Activo')
                        ->default(true)
                        ->columnSpan(1),
                ]),

            Section::make('Seguridad Social')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->description('Números de afiliación ISSS y AFP.')
                ->schema([
                    TextInput::make('isss')   // ← alineado con la migración
                        ->label('ISSS')
                        ->unique(table: 'employees', column: 'isss', ignoreRecord: true)
                        ->maxLength(20)
                        ->placeholder('Número de afiliación ISSS'),

                    TextInput::make('afp')    // ← alineado con la migración
                        ->label('AFP')
                        ->unique(table: 'employees', column: 'afp', ignoreRecord: true)
                        ->maxLength(20)
                        ->placeholder('Número de afiliación AFP'),
                ]),

            Section::make('Salario y Pago')
                ->icon('heroicon-o-banknotes')
                ->columns(2)
                ->schema([
                    TextInput::make('base_salary')
                        ->label('Salario base')
                        ->numeric()
                        ->prefix('$')
                        ->required()
                        ->live(onBlur: true)
                        ->step(0.01)
                        ->minValue(fn (Get $get): float => (($get('pay_frequency') ?? 'Mensual') === 'Mensual') ? 365.00 : 0.01)
                        ->maxValue(99999.99)
                        ->helperText(fn (Get $get) => (($get('pay_frequency') ?? 'Mensual') === 'Mensual')
                            ? 'Salario mínimo nacional (mensual): $365.'
                            : 'Registre la remuneración correspondiente al período de pago seleccionado; el umbral legal de $365 aplica según tabla mensual.')
                        ->columnSpan(1),

                    Select::make('pay_frequency')
                        ->label('Frecuencia de pago')
                        ->options([
                            'Semanal' => 'Semanal',
                            'Quincenal' => 'Quincenal',
                            'Mensual' => 'Mensual',
                        ])
                        ->required()
                        ->live()
                        ->columnSpan(1),

                    Select::make('payment_method')
                        ->label('Método de pago')
                        ->options([
                            'Efectivo' => 'Efectivo',
                            'Transferencia' => 'Transferencia',
                        ])
                        ->required()
                        ->live()
                        ->columnSpan(1),

                    // ← campo nuevo
                    TextInput::make('bank_name')
                        ->label('Banco')
                        ->placeholder('Ej: Banco Agrícola, Davivienda...')
                        ->maxLength(100)
                        ->visible(fn (Get $get) => $get('payment_method') === 'Transferencia')
                        ->required(fn (Get $get) => $get('payment_method') === 'Transferencia')
                        ->columnSpan(1),

                    TextInput::make('bank_account')
                        ->label('Número de cuenta')
                        ->placeholder('Número de cuenta bancaria')
                        ->minLength(10)
                        ->maxLength(30)
                        ->visible(fn (Get $get) => $get('payment_method') === 'Transferencia')
                        ->required(fn (Get $get) => $get('payment_method') === 'Transferencia')
                        ->columnSpan(1),
                ]),
        ]);
    }
}
