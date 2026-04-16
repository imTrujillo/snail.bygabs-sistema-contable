<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_date',
        'description',
        'reference_type',
        'reference_id',
        'fiscal_period_id',
        'user_id',
        'journal_entry_type_id'
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public static function booted(): void
    {
        static::created(function (JournalEntry $entry) {
            $entry->mayorizarLineas();
        });
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function fiscalPeriod(): BelongsTo
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journalEntryType(): BelongsTo
    {
        return $this->belongsTo(JournalEntryType::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function mayorizarLineas(): void
    {
        foreach ($this->lines as $line) {
            $balance = AccountPeriodBalance::firstOrCreate(
                [
                    'account_id'       => $line->account_id,
                    'fiscal_period_id' => $this->fiscal_period_id,
                ],
                [
                    'opening_balance' => 0,
                    'total_debit'     => 0,
                    'total_credit'    => 0,
                    'closing_balance' => 0,
                ]
            );

            $balance->increment('total_debit', $line->debit);
            $balance->increment('total_credit', $line->credit);

            // Recalcular saldo según naturaleza de la cuenta
            $account = Account::find($line->account_id);
            $isDebitNature = in_array($account->type, ['Activo', 'Costo', 'Gasto']);

            $balance->closing_balance = $isDebitNature
                ? $balance->opening_balance + $balance->total_debit - $balance->total_credit
                : $balance->opening_balance + $balance->total_credit - $balance->total_debit;

            $balance->save();
        }
    }

    public function isBalanced(): bool
    {
        $lines = $this->lines;
        return round($lines->sum('debit'), 2) === round($lines->sum('credit'), 2);
    }
}
