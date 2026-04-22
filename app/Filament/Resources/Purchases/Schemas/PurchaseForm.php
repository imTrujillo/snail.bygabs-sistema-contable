<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\FiscalPeriod;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        $period = FiscalPeriod::find(session('active_fiscal_period_id'));

        return $schema
            ->components([

                Section::make('Información de la Compra')
                    ->description('Proveedor, fecha y documento de respaldo.')
                    ->icon('heroicon-o-truck')
                    ->columns(2)
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->prefixIcon('heroicon-m-building-storefront')
                            ->columnSpanFull(),

                        DatePicker::make('purchase_date')
                            ->label('Fecha de compra')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default($period?->start_date ?? now())
                            ->minDate($period?->start_date ?? now())
                            ->maxDate($period?->end_date ?? now())
                            ->columnSpan(1),

                        Select::make('account_id')
                            ->label('Cuenta contable')
                            ->relationship(
                                name: 'account',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query
                                    ->where('is_group', false)
                                    ->whereIn('type', ['Activo', 'Gasto', 'Costo']) // ✅ solo cuentas destino válidas para compras
                                    ->orderBy('code'),
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->code} – {$record->name}")
                            ->searchable()
                            ->required()
                            ->preload()
                            ->prefixIcon('heroicon-m-building-library')
                            ->helperText('Ej: Mercancía (Activo) o Gasto de administración.')
                            ->columnSpan(1),
                    ]),

                Section::make('Documento Fiscal')
                    ->description('Tipo y número del documento recibido.')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('document_type_label')
                            ->label('Tipo de documento')
                            ->content('Crédito Fiscal (CCF)'),

                        Hidden::make('document_type')
                            ->default('CCF'),

                        TextInput::make('document_number')
                            ->label('Número de documento')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Ej: CCF-001234')
                            ->prefixIcon('heroicon-m-hashtag')
                            ->columnSpanFull()
                            ->regex('/^CCF-\d+$/')
                            ->helperText('Formato: CCF-001234 (prefijo fijo CCF + números)')

                    ]),

                Section::make('Ítems de la compra')
                    ->description('Detalle de insumos recibidos.')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->label('')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Insumo')
                                    ->options(Product::orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('description', $product->name);
                                            $set('unit_price', $product->cost_price);
                                            $quantity = floatval($get('quantity') ?: 1);
                                            $set('subtotal', round($quantity * $product->cost_price, 2));
                                        }
                                    })
                                    ->columnSpan(2),

                                TextInput::make('description')
                                    ->label('Descripción')
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $subtotal = round(floatval($state) * floatval($get('unit_price')), 2);
                                        $set('subtotal', $subtotal);
                                    })
                                    ->columnSpan(1),

                                TextInput::make('unit_price')
                                    ->label('Precio unitario')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ])
                            ->columns(7)
                            ->addActionLabel('Agregar ítem')
                            ->defaultItems(1),
                    ])->columnSpanFull(),


                // Reemplaza toda la sección Montos por esto:
                Section::make('Montos adicionales')
                    ->description('Solo si hay montos exentos o no sujetos a IVA.')
                    ->icon('heroicon-o-banknotes')
                    ->columns(2)
                    ->schema([
                        TextInput::make('exempt_amount')
                            ->label('Monto exento')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(1),

                        TextInput::make('non_taxable_amount')
                            ->label('Monto no sujeto')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(1),
                    ]),

                Section::make('Notas')
                    ->description('Observaciones internas de la compra.')
                    ->icon('heroicon-o-clipboard-document')
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notas')
                            ->placeholder('Detalles del proveedor, condiciones, número de orden...')
                            ->rows(3)
                            ->default(null)
                            ->columnSpanFull(),
                    ]),

            ]);
    }

    protected static function recalcTotal($set, $get): void
    {
        $total = floatval($get('exempt_amount'))
            + floatval($get('non_taxable_amount'))
            + floatval($get('taxable_amount'))
            + floatval($get('credit_fiscal'));

        $set('total_amount', round($total, 2));
    }
}
