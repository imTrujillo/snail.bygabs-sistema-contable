<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use App\Models\Employee;
use App\Models\FiscalPeriod;
use App\Support\SalaryRentaRetentionElSalvador;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        $period = FiscalPeriod::find(session('active_fiscal_period_id'));

        return $schema->components([

            Section::make('Datos de la Planilla')
                ->icon('heroicon-o-banknotes')
                ->columns(2)
                ->schema([
                    Select::make('fiscal_period_id')
                        ->label('Período fiscal')
                        ->options(FiscalPeriod::where('is_closed', false)->pluck('name', 'id'))
                        ->default($period?->id)
                        ->required()
                        ->disabled()
                        ->dehydrated(),

                    Select::make('period_type')
                        ->label('Tipo de período')
                        ->options([
                            'Semanal' => 'Semanal',
                            'Quincenal' => 'Quincenal',
                            'Mensual' => 'Mensual',
                        ])
                        ->required()
                        ->live(),

                    DatePicker::make('pay_date')
                        ->label('Fecha de pago')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default($period?->start_date ?? now())
                        ->minDate($period?->start_date ?? now())
                        ->maxDate($period?->end_date ?? now())
                        ->rules(['date'])
                        ->columnSpanFull(),
                ]),

            Section::make('Empleados')
                ->icon('heroicon-o-users')
                ->description('Agrega los empleados incluidos en esta planilla.')
                ->schema([
                    Repeater::make('payrollLines')
                        ->relationship('payrollLines')
                        ->label('Detalle por empleado')
                        ->minItems(1)
                        ->addActionLabel('Agregar empleado')
                        ->columns(2)
                        ->schema([
                            Select::make('employee_id')
                                ->label('Empleado')
                                ->relationship(
                                    name: 'employee',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn ($query) => $query->where('is_active', true),
                                )
                                ->required()
                                ->searchable()
                                ->preload()
                                ->createOptionModalHeading('Nuevo empleado')
                                ->createOptionForm([
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
                                        ->columnSpanFull(),

                                    TextInput::make('isss')
                                        ->label('ISSS')
                                        ->unique(table: 'employees', column: 'isss', ignoreRecord: true)
                                        ->maxLength(20)
                                        ->placeholder('Número de afiliación ISSS'),

                                    TextInput::make('afp')
                                        ->label('AFP')
                                        ->unique(table: 'employees', column: 'afp', ignoreRecord: true)
                                        ->maxLength(20)
                                        ->placeholder('Número de afiliación AFP'),

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
                                            : 'Registre la remuneración correspondiente al período de pago seleccionado.')
                                        ->columnSpan(1),

                                    Select::make('pay_frequency')
                                        ->label('Frecuencia de pago')
                                        ->options([
                                            'Semanal' => 'Semanal',
                                            'Quincenal' => 'Quincenal',
                                            'Mensual' => 'Mensual',
                                        ])
                                        ->required()
                                        ->default('Mensual')
                                        ->live()
                                        ->columnSpan(1),

                                    Select::make('payment_method')
                                        ->label('Método de pago')
                                        ->options([
                                            'Efectivo' => 'Efectivo',
                                            'Transferencia' => 'Transferencia',
                                        ])
                                        ->required()
                                        ->default('Efectivo')
                                        ->live()
                                        ->columnSpan(1),

                                    TextInput::make('bank_name')
                                        ->label('Banco')
                                        ->placeholder('Ej: Banco Agrícola, Davivienda...')
                                        ->maxLength(100)
                                        ->visible(fn (Get $get) => $get('payment_method') === 'Transferencia')
                                        ->required(fn (Get $get) => $get('payment_method') === 'Transferencia'),

                                    TextInput::make('bank_account')
                                        ->label('Número de cuenta')
                                        ->placeholder('Número de cuenta bancaria')
                                        ->minLength(10)
                                        ->maxLength(30)
                                        ->visible(fn (Get $get) => $get('payment_method') === 'Transferencia')
                                        ->required(fn (Get $get) => $get('payment_method') === 'Transferencia'),
                                ])
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get) {
                                    $employee = Employee::find($get('employee_id'));
                                    if (! $employee) {
                                        return;
                                    }

                                    $gross = (float) $employee->base_salary;
                                    $isss = $employee->isssDeduction();
                                    $afp = $employee->afpDeduction();
                                    $periodType = filled($get('../../period_type')) ? $get('../../period_type') : ($get('../../../period_type') ?: 'Mensual');
                                    $rent = SalaryRentaRetentionElSalvador::retentionForPeriod(
                                        $gross,
                                        $isss,
                                        $afp,
                                        (string) $periodType
                                    );
                                    $net = round(max(0.0, $gross - $isss - $afp - $rent), 2);

                                    $set('gross_salary', $gross);
                                    $set('isss_deduction', $isss);
                                    $set('afp_deduction', $afp);
                                    $set('renta_deduction', $rent);
                                    $set('net_salary', $net);
                                })
                                ->columnSpan(2),

                            TextInput::make('gross_salary')
                                ->label('Salario bruto')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->minValue(365)
                                ->readOnly()
                                ->columnSpan(1),

                            TextInput::make('isss_deduction')
                                ->label('ISSS (3%)')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->minValue(0)
                                ->readOnly()
                                ->columnSpan(1),

                            TextInput::make('afp_deduction')
                                ->label('AFP (7.25%)')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->minValue(0)
                                ->readOnly()
                                ->columnSpan(1),

                            TextInput::make('renta_deduction')
                                ->label('Renta ISR (retención)')
                                ->numeric()
                                ->prefix('$')
                                ->default(0)
                                ->disabled()
                                ->dehydrated()
                                ->step(0.01)
                                ->helperText(
                                    'Tablas de retención MH (mensual/quincenal/semanal) aplicadas sobre gravada después de ISSS y AFP.'
                                )
                                ->columnSpan(1),

                            TextInput::make('net_salary')
                                ->label('Neto a pagar')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->minValue(0)
                                ->readOnly()
                                ->columnSpan(2),
                        ]),
                ]),
        ]);
    }
}
