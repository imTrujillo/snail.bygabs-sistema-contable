<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TaxDocument extends Model
{
    protected $fillable = [
        'type',
        'series',
        'correlative_number',
        'document_number',
        'issue_date',
        'client_id',
        'supplier_id',
        'reference_id',
        'reference_type',
        'exempt_amount',
        'non_taxable_amount',
        'taxable_amount',
        'iva_amount',
        'total_amount',
        'is_voided',
        'voided_at',
    ];

    protected $casts = [
        'issue_date'         => 'date',
        'voided_at'          => 'datetime',
        'is_voided'          => 'boolean',
        'exempt_amount'      => 'decimal:2',
        'non_taxable_amount' => 'decimal:2',
        'taxable_amount'     => 'decimal:2',
        'iva_amount'         => 'decimal:2',
        'total_amount'       => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class);
    }
}
