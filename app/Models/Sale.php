<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['appointment_id', 'customer_id', 'payment_method'];
}
