<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = ["customer_id", 'user_id', 'appointment_date', 'status', 'notes'];

    protected $casts = [
        'appointment_date' => 'datetime',
        'status' => AppointmentStatus::class
    ];


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'appointment_services')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function appointmentServices(): HasMany
    {
        return $this->hasMany(AppointmentService::class);
    }

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class);
    }
}

enum AppointmentStatus: string
{
    case Pendiente  = 'Pendiente';
    case Completada = 'Completada';
    case Cancelada  = 'Cancelada';
}
