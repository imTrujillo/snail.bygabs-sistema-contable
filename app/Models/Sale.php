<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sale extends Model
{
    use LogsActivity;

    protected $fillable = ['appointment_id', 'customer_id', 'tax_document_id', 'total', 'payment_method', 'document_type', 'status'];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Venta {$eventName}");
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

    /**
     * Asiento generado por el sistema (reference_type = sale en journal_entries).
     */
    public function journalEntry(): HasOne
    {
        return $this->hasOne(JournalEntry::class, 'reference_id')
            ->where('reference_type', 'sale');
    }

    public function anular(): void
    {
        $originalEntry = $this->journalEntry()->with('lines')->first();

        if ($originalEntry) {
            $reversal = JournalEntry::create([
                'entry_date' => now(),
                'description' => "Anulación de venta #{$this->id}",
                'reference_type' => 'manual',
                'reference_id' => 0,
                'fiscal_period_id' => $originalEntry->fiscal_period_id,
                'user_id' => Auth::id() ?? $originalEntry->user_id,
                'journal_entry_type_id' => $originalEntry->journal_entry_type_id,
            ]);

            foreach ($originalEntry->lines as $line) {
                $reversal->lines()->create([
                    'account_id' => $line->account_id,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'description' => 'Reversa: '.$line->description,
                ]);
            }
        }

        if ($this->taxDocument && ! $this->taxDocument->is_voided) {
            $this->taxDocument->update([
                'is_voided' => true,
                'voided_at' => now(),
            ]);
        }

        $this->update(['status' => 'anulada']);

        Notification::make()
            ->title('Venta anulada')
            ->body("Venta #{$this->id} anulada y asiento revertido.")
            ->warning()
            ->sendToDatabase(Auth::user());
    }
}
