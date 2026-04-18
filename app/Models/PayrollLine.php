<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollLine extends Model
{
    protected $fillable = [
        'payroll_id',
        'employee_id',
        'amount',
        'gross_salary',
        'isss_deduction',
        'afp_deduction',
        'renta_deduction',
        'net_salary',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
