<?php

namespace App\Filament\Resources\FiscalPeriods\Tables;

use App\Models\FiscalPeriod;
use App\Services\PeriodClosingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FiscalPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Período')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date('d/m/Y'),

                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y'),

                IconColumn::make('is_closed')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('journal_entries_count')
                    ->label('Transacciones')
                    ->counts('journalEntries')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'gray'),
            ])
            ->recordActions([

                EditAction::make()
                    ->tooltip(function (FiscalPeriod $record): string {
                        if ($record->is_closed) return 'Período cerrado, no editable.';
                        if ($record->hasTransactions()) return 'Tiene transacciones registradas, no editable.';
                        return 'Editar período';
                    }),

                // ✅ Acción de Cierre de Período
                Action::make('cerrar_periodo')
                    ->label('Cerrar Período')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Cerrar este período?')
                    ->modalDescription('Esta acción es irreversible. Se calcularán los saldos finales y se trasladarán al siguiente período.')
                    ->modalSubmitActionLabel('Sí, cerrar período')
                    ->visible(fn(FiscalPeriod $record) => !$record->is_closed)
                    ->action(function (FiscalPeriod $record) {
                        $service = new PeriodClosingService();

                        // Validar antes de cerrar
                        $errors = $service->validate($record);

                        if (!empty($errors)) {
                            Notification::make()
                                ->title('No se puede cerrar el período')
                                ->body(implode("\n", array_slice($errors, 0, 3)))
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            $closing = $service->close($record);

                            Notification::make()
                                ->title("Período {$record->name} cerrado")
                                ->body("Resultado neto: $" . number_format($closing->net_result, 2))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al cerrar período')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ✅ Acción de Re-mayorización manual
                Action::make('remayorizar')
                    ->label('Re-mayorizar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Re-mayorizar período')
                    ->modalDescription('Recalcula todos los saldos del mayor desde cero. Útil si hubo ajustes manuales.')
                    ->visible(fn(FiscalPeriod $record) => !$record->is_closed)
                    ->action(function (FiscalPeriod $record) {
                        try {
                            (new PeriodClosingService())->remayorizar($record);

                            Notification::make()
                                ->title('Mayor recalculado')
                                ->body("Se actualizaron los saldos del período {$record->name}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
