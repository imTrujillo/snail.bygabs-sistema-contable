<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Supplier extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'nrc',
        'nit',
        'phone',
        'email',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Venta {$eventName}");
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function taxDocuments(): HasMany
    {
        return $this->hasMany(TaxDocument::class);
    }
}
