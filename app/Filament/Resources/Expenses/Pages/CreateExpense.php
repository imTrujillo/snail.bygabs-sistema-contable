<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Models\Account;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['payment_account_id']) && ! empty($data['paid_with'])) {
            $map = [
                'Efectivo' => '1102',
                'Transferencia' => '1101',
                'Tarjeta' => '1101',
            ];
            $code = $map[$data['paid_with']] ?? null;
            if ($code) {
                $data['payment_account_id'] = Account::where('code', $code)->value('id');
            }
        }

        return $data;
    }
}
