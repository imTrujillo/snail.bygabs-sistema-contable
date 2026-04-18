<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'pay_date',
        'fiscal_period_id',
        'user_id',
        'period_type',
        'total_gross',
        'total_isss',
        'total_afp',
        'total_renta',
        'total_net',

    ];

    public function fiscalPeriod()
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    public function payrollLines()
    {
        return $this->hasMany(PayrollLine::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
