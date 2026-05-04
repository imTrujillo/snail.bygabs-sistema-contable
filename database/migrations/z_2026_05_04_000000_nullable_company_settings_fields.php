<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE company_settings MODIFY nrc VARCHAR(255) NULL');
        DB::statement('ALTER TABLE company_settings MODIFY nit VARCHAR(255) NULL');
        DB::statement('ALTER TABLE company_settings MODIFY address VARCHAR(255) NULL');
        DB::statement('ALTER TABLE company_settings MODIFY logo VARCHAR(255) NULL');
        DB::statement('ALTER TABLE company_settings MODIFY tax_regime VARCHAR(50) NULL');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE company_settings MODIFY nrc VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE company_settings MODIFY nit VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE company_settings MODIFY address VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE company_settings MODIFY logo VARCHAR(255) NOT NULL');
        DB::statement("ALTER TABLE company_settings MODIFY tax_regime ENUM('Consumidor Final','Contribuyente') NOT NULL");
    }
};
