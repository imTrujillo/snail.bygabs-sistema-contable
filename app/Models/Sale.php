<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sale extends Model
{
    use LogsActivity;

    protected $fillable = ['appointment_id', 'customer_id', 'tax_document_id', 'total', 'payment_method', 'document_type'];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Venta {$eventName}");
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function taxDocument(): BelongsTo
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

    public function anular(): void
    {
        // 1. Revertir asiento contable
        $originalEntry = $this->journalEntries()->first();

        if ($originalEntry) {
            $reversal = JournalEntry::create([
                'entry_date'       => now(),
                'description'      => "Anulación de venta #{$this->id}",
                'reference_type'   => 'sale_reversal',
                'reference_id'     => $this->id,
                'fiscal_period_id' => $originalEntry->fiscal_period_id,
                'user_id'          => Auth::id(),
            ]);

            // Invertir cada línea
            foreach ($originalEntry->lines as $line) {
                $reversal->lines()->create([
                    'account_id'  => $line->account_id,
                    'debit'       => $line->credit,   // invertido
                    'credit'      => $line->debit,    // invertido
                    'description' => 'Reversa: ' . $line->description,
                ]);
            }
        }

        // 2. Marcar como anulada
        $this->update(['status' => 'anulada']);

        Notification::make()
            ->title('Venta anulada')
            ->body("Venta #{$this->id} anulada y asiento revertido.")
            ->warning()
            ->sendToDatabase(Auth::user());
    }
}
