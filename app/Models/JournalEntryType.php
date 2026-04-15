<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryType extends Model
{
    protected $fillable = ['name'];

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
