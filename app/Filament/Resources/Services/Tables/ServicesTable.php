<?php

namespace App\Filament\Resources\Services\Tables;

use App\Filament\Exports\ServiceExporter;
use App\Filament\Imports\ServiceImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-sparkles')
                    ->description(fn($record) => \Illuminate\Support\Str::limit($record->description, 60)),

                TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Average::make()
                            ->money('USD')
                            ->label('Promedio'),
                    ]),

                TextColumn::make('duration_minutes')
                    ->label('Duración')
                    ->sortable()
                    ->formatStateUsing(
                        fn($state): string =>
                        $state >= 60
                            ? floor($state / 60) . 'h ' . ($state % 60 > 0 ? ($state % 60) . 'min' : '')
                            : $state . ' min'
                    )
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-m-clock'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('name', 'asc')

            ->filters([
                Filter::make('short_duration')
                    ->label('Duración corta (≤ 30 min)')
                    ->query(fn($query) => $query->where('duration_minutes', '<=', 30)),

                Filter::make('long_duration')
                    ->label('Duración larga (> 60 min)')
                    ->query(fn($query) => $query->where('duration_minutes', '>', 60)),

                Filter::make('price_range')
                    ->label('Rango de precio')
                    ->form([
                        TextInput::make('min_price')->label('Precio mínimo')->numeric()->prefix('$'),
                        TextInput::make('max_price')->label('Precio máximo')->numeric()->prefix('$'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min_price'], fn($q) => $q->where('price', '>=', $data['min_price']))
                            ->when($data['max_price'], fn($q) => $q->where('price', '<=', $data['max_price']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['min_price'] ?? null) $indicators[] = 'Precio mín: $' . $data['min_price'];
                        if ($data['max_price'] ?? null) $indicators[] = 'Precio máx: $' . $data['max_price'];
                        return $indicators;
                    }),
            ])

            ->headerActions([
                ImportAction::make()
                    ->importer(ServiceImporter::class)
                    ->label('Importar'),

                ExportAction::make()
                    ->exporter(ServiceExporter::class)
                    ->label('Exportar'),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(ServiceExporter::class),
                ]),
            ])

            ->paginated([10, 25, 50])

            ->extremePaginationLinks();
    }
}
