<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountPeriodBalance extends Model
{
    protected $fillable = [
        'account_id',
        'fiscal_period_id',
        'opening_balance',
        'total_debit',
        'total_credit',
        'closing_balance',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'total_debit'     => 'decimal:2',
        'total_credit'    => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function fiscalPeriod(): BelongsTo
    {
        return $this->belongsTo(FiscalPeriod::class);
    }
}
