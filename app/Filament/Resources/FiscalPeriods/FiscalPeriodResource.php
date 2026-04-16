<?php

namespace App\Filament\Resources\FiscalPeriods;

use App\Filament\Resources\FiscalPeriodResource\Pages;
use App\Filament\Resources\FiscalPeriods\Pages\CreateFiscalPeriod;
use App\Filament\Resources\FiscalPeriods\Pages\EditFiscalPeriod;
use App\Filament\Resources\FiscalPeriods\Pages\ListFiscalPeriods;
use App\Models\FiscalPeriod;
use App\Services\PeriodClosingService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class FiscalPeriodResource extends Resource
{
    protected static ?string $model = FiscalPeriod::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Períodos Fiscales';
    protected static ?string $modelLabel = 'Período';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->placeholder('Ej: Enero 2025'),

            DatePicker::make('start_date')
                ->label('Fecha inicio')
                ->required(),

            DatePicker::make('end_date')
                ->label('Fecha fin')
                ->required()
                ->after('start_date'),

            Toggle::make('is_closed')
                ->label('Cerrado')
                ->disabled(), // solo se cierra con la acción
        ])->columns(2);
    }

    public static function table(Table $table): Table
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

                Tables\Columns\IconColumn::make('is_closed')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->actions([
                EditAction::make(),

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
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListFiscalPeriods::route('/'),
            'create' => CreateFiscalPeriod::route('/create'),
            'edit'   => EditFiscalPeriod::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->isAdmin();
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->isAdmin();
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->isAdmin();
    }
}
