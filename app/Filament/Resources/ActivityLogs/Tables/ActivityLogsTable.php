<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Filament\Exports\ActivityLogExporter;
use App\Models\ActivityLog;
use Filament\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function logNameLabel(?string $state): string
    {
        return match ($state) {
            'default', null, '' => 'General',
            default => (string) str($state)->replace(['_', '-'], ' ')->title(),
        };
    }

    public static function translateDescription(?string $state): string
    {
        if ($state === null || $state === '') {
            return '';
        }

        $eventMap = [
            'created' => 'registrado',
            'updated' => 'actualizado',
            'deleted' => 'eliminado',
            'restored' => 'restaurado',
        ];

        return preg_replace_callback(
            '/\b('.implode('|', array_keys($eventMap)).')\b/i',
            static fn (array $m) => $eventMap[strtolower($m[1])] ?? $m[1],
            $state
        );
    }

    public static function subjectLabel(?string $state): string
    {
        if ($state === null || $state === '') {
            return '';
        }

        $map = [
            'App\\Models\\Appointment' => 'Cita',
            'App\\Models\\Customer' => 'Cliente',
            'App\\Models\\Employee' => 'Empleado',
            'App\\Models\\Expense' => 'Gasto',
            'App\\Models\\Product' => 'Insumo',
            'App\\Models\\Purchase' => 'Compra',
            'App\\Models\\Sale' => 'Venta',
            'App\\Models\\Service' => 'Servicio',
            'App\\Models\\Supplier' => 'Proveedor',
            'App\\Models\\User' => 'Usuario',
            'App\\Models\\TaxDocument' => 'Documento fiscal',
            'App\\Models\\JournalEntry' => 'Partida contable',
            'App\\Models\\Account' => 'Cuenta',
            'App\\Models\\FiscalPeriod' => 'Período fiscal',
            'App\\Models\\Payroll' => 'Planilla',
        ];

        return $map[$state] ?? class_basename($state);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label('Módulo')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => self::logNameLabel($state)),

                TextColumn::make('description')
                    ->label('Acción')
                    ->searchable()
                    ->formatStateUsing(fn (?string $state): string => self::translateDescription($state)),

                TextColumn::make('subject_type')
                    ->label('Entidad')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => self::subjectLabel($state)),

                TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->default('Sistema'),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                ExportAction::make()
                    ->exporter(ActivityLogExporter::class)
                    ->label('Exportar'),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Módulo')
                    ->options(
                        fn () => ActivityLog::query()
                            ->whereNotNull('log_name')
                            ->distinct()
                            ->orderBy('log_name')
                            ->pluck('log_name')
                            ->mapWithKeys(fn (string $name) => [$name => self::logNameLabel($name)])
                            ->all()
                    ),
            ]);
    }
}
