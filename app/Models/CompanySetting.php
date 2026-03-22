<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'name',
        'nrc',
        'nit',
        'address',
        'tax_regime',
        'logo',
    ];

    public static function current(): self
    {
        return static::firstOrFail();
    }
}
