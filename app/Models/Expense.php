<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model
{
    use LogsActivity;

    protected $fillable = [
        'description',
        'category',
        'amount',
        'expense_date',
        'paid_with',
        'account_id',
        'payment_account_id',
        'notes',
        'document_type',
        'supplier_id',
        'supplier_name',
        'supplier_nrc',
        'iva_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Gasto {$eventName}");
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function journalEntry(): HasOne
    {
        return $this->hasOne(JournalEntry::class, 'reference_id')
            ->where('reference_type', 'expense');
    }

    protected static function booted(): void
    {
        static::saving(function (Expense $expense) {
            if ($expense->supplier_id) {
                $expense->loadMissing('supplier');
                if ($expense->supplier) {
                    $expense->supplier_name = $expense->supplier->name;
                    $expense->supplier_nrc = $expense->supplier->nrc;
                }
            }
        });
    }
}
