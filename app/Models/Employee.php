<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'email',
        'position',
        'dui',
        'isss',
        'afp',
        'base_salary',
        'pay_frequency',
        'is_active',
        'hire_date',
    ];

    public function payrollLines()
    {
        return $this->hasMany(PayrollLine::class);
    }
}
