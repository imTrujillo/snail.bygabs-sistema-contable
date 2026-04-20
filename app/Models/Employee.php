<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;


class Employee extends Model
{
    use LogsActivity;

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
        'payment_method',
        'bank_account',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'is_active'   => 'boolean',
        'hire_date'   => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Usuario {$eventName}");
    }

    public function isssDeduction(): float
    {
        return round($this->base_salary * 0.03, 2);   // 3% empleado
    }

    public function afpDeduction(): float
    {
        return round($this->base_salary * 0.0725, 2); // 7.25% empleado
    }

    public function netSalary(): float
    {
        return round($this->base_salary - $this->isssDeduction() - $this->afpDeduction(), 2);
    }

    public function payrollLines()
    {
        return $this->hasMany(PayrollLine::class);
    }
}
