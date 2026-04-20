<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Models\FiscalPeriod;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        $period = FiscalPeriod::find(session('active_fiscal_period_id'));

        return $schema->components([

            Section::make('Detalle del Gasto')
                ->description('Información principal del gasto registrado.')
                ->icon('heroicon-o-receipt-percent')
                ->columns(2)
                ->schema([
                    TextInput::make('description')
                        ->label('Descripción')
                        ->required()
                        ->minLength(3)
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Select::make('account_id')
                        ->label('Cuenta de gasto')
                        ->relationship(
                            name: 'account',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn($query) => $query
                                ->where('is_group', false)
                                ->where('type', 'Gasto'),
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->prefixIcon('heroicon-m-building-library')
                        ->helperText('La categoría se asignará automáticamente.')
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $account = \App\Models\Account::find($get('account_id'));
                            if (!$account) return;

                            $map = [
                                '6100' => 'Administrativo',
                                '6200' => 'Operativo',
                                '6300' => 'Otros',
                                '6400' => 'Otros',
                            ];

                            // Usa el map si existe, si no asigna 'Otros' como fallback
                            $set('category', $map[$account->code] ?? 'Otros');
                        })
                        ->columnSpan(1),

                    Select::make('category')
                        ->hidden()
                        ->dehydrated()
                        ->options([
                            'Operativo'      => 'Operativo',
                            'Administrativo' => 'Administrativo',
                            'Marketing'      => 'Marketing',
                            'Nomina'         => 'Nómina',
                            'Servicios'      => 'Servicios (agua, luz, internet)',
                            'Alquiler'       => 'Alquiler',
                            'Transporte'     => 'Transporte',
                            'Insumos'        => 'Insumos / Materiales',
                            'Impuestos'      => 'Impuestos',
                            'Otros'          => 'Otros',
                        ]),

                    DateTimePicker::make('expense_date')
                        ->label('Fecha del gasto')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y H:i')
                        ->default($period?->start_date ?? now())
                        ->minDate($period?->start_date ?? now())
                        ->maxDate($period?->end_date ?? now())
                        ->columnSpan(1),

                    TextInput::make('amount')
                        ->label('Monto')
                        ->required()
                        ->numeric()
                        ->minValue(0.01)
                        ->maxValue(99999.99)
                        ->step(0.01)
                        ->prefix('$')
                        ->live(onBlur: true)
                        ->columnSpan(1),
                ]),

            Section::make('Documento Fiscal')
                ->description('Indique si el gasto viene respaldado por un documento fiscal.')
                ->icon('heroicon-o-document-text')
                ->columns(2)
                ->schema([
                    ToggleButtons::make('document_type')
                        ->label('Tipo de documento')
                        ->inline()
                        ->live()
                        ->options([
                            'FCF' => 'Consumidor Final (FCF)',
                            'CCF' => 'Crédito Fiscal (CCF)',
                        ])
                        ->icons([
                            'FCF' => 'heroicon-m-user',
                            'CCF' => 'heroicon-m-building-office',
                        ])
                        ->colors([
                            'FCF' => 'info',
                            'CCF' => 'success',
                        ])
                        ->columnSpanFull(),

                    TextInput::make('supplier_name')
                        ->label('Nombre del proveedor')
                        ->minLength(3)
                        ->maxLength(255)
                        ->visible(fn(Get $get) => $get('document_type') === 'CCF')
                        ->required(fn(Get $get) => $get('document_type') === 'CCF')
                        ->columnSpan(1),

                    TextInput::make('supplier_nrc')
                        ->label('NRC del proveedor')
                        ->regex('/^\d{1,6}-\d$/')   // ← faltaba formato
                        ->helperText('Formato: 123456-7')
                        ->maxLength(20)
                        ->visible(fn(Get $get) => $get('document_type') === 'CCF')
                        ->required(fn(Get $get) => $get('document_type') === 'CCF'),

                    TextInput::make('iva_amount')
                        ->label('IVA crédito fiscal (13%)')
                        ->prefix('$')
                        ->numeric()
                        ->step(0.01)
                        ->disabled()
                        ->dehydrated()
                        ->visible(fn(Get $get) => $get('document_type') === 'CCF')
                        ->formatStateUsing(
                            fn(Get $get) =>
                            $get('document_type') === 'CCF'
                                ? round(($get('amount') ?? 0) * 0.13, 2)
                                : 0
                        )
                        ->columnSpanFull(),
                ]),

            Section::make('Método de Pago')
                ->description('Cómo y desde qué cuenta se realizó el gasto.')
                ->icon('heroicon-o-credit-card')
                ->schema([
                    ToggleButtons::make('paid_with')
                        ->label('Pagado con')
                        ->helperText('La cuenta de pago se asignará automáticamente.')
                        ->required()
                        ->inline()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $map = [
                                'Efectivo'      => '1102',
                                'Transferencia' => '1101',
                                'Tarjeta'       => '1101',
                            ];
                            $code    = $map[$get('paid_with')] ?? null;
                            $account = \App\Models\Account::where('code', $code)->first();
                            $set('payment_account_id', $account?->id);
                        })
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

                    // ✅ Sin el Select duplicado de account_id
                    Select::make('payment_account_id')
                        ->hidden()
                        ->dehydrated()
                        ->relationship(
                            name: 'paymentAccount',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn($query) => $query->whereIn('code', ['1101', '1102']),
                        ),
                ]),

            Section::make('Notas')
                ->description('Observaciones adicionales sobre el gasto.')
                ->icon('heroicon-o-clipboard-document')
                ->collapsed()
                ->schema([
                    Textarea::make('notes')
                        ->label('Notas')
                        ->placeholder('Detalles adicionales, número de factura...')
                        ->rows(3)
                        ->default(null)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
