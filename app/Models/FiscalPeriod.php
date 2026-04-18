<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalPeriod extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_closed',
        'total_income',
        'total_expense',
        'net_result',
        'closed_by',
        'closed_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_closed'  => 'boolean',
        'total_income' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_result' => 'decimal:2',
    ];

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function accountBalances(): HasMany
    {
        return $this->hasMany(AccountPeriodBalance::class);
    }

    public function totalDebitoFiscal(): float
    {
        return $this->journalEntries()
            ->whereHas('lines', fn($q) => $q->whereHas(
                'account',
                fn($q) => $q->where('code', '2104-01')
            ))
            ->sum('iva_amount');
    }
}
