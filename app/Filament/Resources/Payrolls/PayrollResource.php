<?php

namespace App\Filament\Resources\Payrolls;

use App\Filament\Resources\Payrolls\Pages\ListPayrolls;
use App\Filament\Resources\Payrolls\Schemas\PayrollForm;
use App\Filament\Resources\Payrolls\Tables\PayrollsTable;
use App\Models\Payroll;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;
    protected static string|UnitEnum|null $navigationGroup = 'Operativo';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'Planilla';
    protected static ?string $pluralModelLabel = 'Planillas';
    protected static ?string $navigationLabel = 'Planillas';
    protected static ?string $breadcrumb = 'Planillas';

    protected static ?string $recordTitleAttribute = 'pay_date';

    public static function form(Schema $schema): Schema
    {
        return PayrollForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrolls::route('/'),
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
