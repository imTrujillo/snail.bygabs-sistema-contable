<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Purchase extends Model
{
    use LogsActivity;

    protected $fillable = [
        'supplier_id',
        'tax_document_id',
        'purchase_date',
        'exempt_amount',
        'non_taxable_amount',
        'taxable_amount',
        'credit_fiscal',
        'total_amount',
        'account_id',
        'document_number',
        'notes',
    ];

    protected $casts = [
        'purchase_date'      => 'date',
        'exempt_amount'      => 'decimal:2',
        'non_taxable_amount' => 'decimal:2',
        'taxable_amount'     => 'decimal:2',
        'credit_fiscal'      => 'decimal:2',
        'total_amount'       => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Venta {$eventName}");
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function taxDocument(): BelongsTo
    {
        return $this->belongsTo(TaxDocument::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function journalEntry(): MorphOne
    {
        return $this->morphOne(JournalEntry::class, 'reference');
    }
}
