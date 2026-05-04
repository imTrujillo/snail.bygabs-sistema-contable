<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('accounts')) {
            return;
        }

        DB::table('accounts')
            ->where('subtype', 'administrativo')
            ->update(['subtype' => 'Administrativo']);
    }

    public function down(): void
    {
        DB::table('accounts')
            ->where('subtype', 'Administrativo')
            ->where('code', '6100')
            ->update(['subtype' => 'administrativo']);
    }
};
