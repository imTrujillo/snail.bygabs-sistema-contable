<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JournalEntryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Apertura'],
            ['name' => 'Diario'],
            ['name' => 'Ajuste'],
            ['name' => 'Cierre'],
        ];

        foreach ($types as $type) {
            DB::table('journal_entry_types')->updateOrInsert(
                ['name' => $type['name']],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
