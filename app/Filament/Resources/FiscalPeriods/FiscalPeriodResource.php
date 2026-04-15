<?php

namespace App\Filament\Resources\FiscalPeriods;

use App\Filament\Resources\FiscalPeriods\Pages\CreateFiscalPeriod;
use App\Filament\Resources\FiscalPeriods\Pages\EditFiscalPeriod;
use App\Filament\Resources\FiscalPeriods\Pages\ListFiscalPeriods;
use App\Filament\Resources\FiscalPeriods\Schemas\FiscalPeriodForm;
use App\Filament\Resources\FiscalPeriods\Tables\FiscalPeriodsTable;
use App\Models\FiscalPeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class FiscalPeriodResource extends Resource
{
    protected static ?string $model = FiscalPeriod::class;

    protected static string|UnitEnum|null $navigationGroup = 'Fiscal';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $modelLabel = 'Período Fiscal';
    protected static ?string $pluralModelLabel = 'Períodos Fiscales';
    protected static ?string $navigationLabel = 'Períodos Fiscales';
    protected static ?string $breadcrumb = 'Períodos Fiscales';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FiscalPeriodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FiscalPeriodsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFiscalPeriods::route('/'),
            'create' => CreateFiscalPeriod::route('/create'),
            'edit' => EditFiscalPeriod::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin();
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
