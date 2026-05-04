<?php

namespace App\Filament\Resources\Payrolls\Tables;

use App\Filament\Exports\PayrollExporter;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->exporter(PayrollExporter::class)
                    ->label('Exportar'),
            ])
            ->columns([
                TextColumn::make('pay_date')
                    ->label('Fecha de pago')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('period_type')
                    ->label('Tipo'),

                TextColumn::make('fiscalPeriod.name')
                    ->label('Período fiscal'),

                TextColumn::make('total_gross')
                    ->label('Salario bruto')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('total_net')
                    ->label('A pagar')
                    ->money('USD')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
