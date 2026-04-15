<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodClosing extends Model
{
    protected $fillable = [
        'fiscal_period_id',
        'user_id',
        'closed_at',
        'total_income',
        'total_expense',
        'net_result'
    ];

    public function fiscalPeriod()
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
