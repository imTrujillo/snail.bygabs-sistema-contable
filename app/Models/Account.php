<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'subtype',
        'account_id',
        'is_group',
        'is_default',
    ];

    protected $casts = [
        'is_group'   => 'boolean',
        'is_default' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    // Contabilidad
    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function periodBalances(): HasMany
    {
        return $this->hasMany(AccountPeriodBalance::class);
    }

    public function currentBalance(): float
    {
        return $this->journalLines()->sum('debit')
            - $this->journalLines()->sum('credit');
    }
}
