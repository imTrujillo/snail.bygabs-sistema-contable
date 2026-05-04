<?php

namespace App\Filament\Resources\Payrolls\Pages;

use App\Filament\Resources\Payrolls\PayrollResource;
use App\Observers\PayrollObserver;
use Filament\Resources\Pages\CreateRecord;

class CreatePayroll extends CreateRecord
{
    protected static string $resource = PayrollResource::class;

    protected function afterCreate(): void
    {
        PayrollObserver::finalizeNewPayroll($this->record->fresh('payrollLines'));
    }
}
