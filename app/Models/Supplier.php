<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'nrc',
        'nit',
        'phone',
        'email',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function taxDocuments(): HasMany
    {
        return $this->hasMany(TaxDocument::class);
    }
}
