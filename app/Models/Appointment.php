<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['customer_id', 'service_id', 'appointment_date', 'status', 'notes'];
}
