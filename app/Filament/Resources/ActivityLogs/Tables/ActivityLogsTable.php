<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label('Módulo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Acción')
                    ->searchable(),

                TextColumn::make('subject_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn($state) => class_basename($state ?? ''))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->default('Sistema'),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Módulo')
                    ->options(
                        ActivityLog::distinct()
                            ->pluck('log_name', 'log_name')
                    ),
            ]);
    }
}
