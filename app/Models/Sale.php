<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Sale extends Model
{
    protected $fillable = ['appointment_id', 'customer_id', 'tax_document_id', 'total', 'payment_method'];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    protected function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    protected function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected function taxDocument(): BelongsTo
    {
        return $this->belongsTo(TaxDocument::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function journalEntry(): MorphOne
    {
        return $this->morphOne(JournalEntry::class, 'reference');
    }
}
