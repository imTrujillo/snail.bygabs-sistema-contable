<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = ['name', 'description', 'duration_minutes', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function appointmentServices(): HasMany
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
