<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanySetting::create([
            'name' => 'Emprendimiento snail.bygabs',
            'nrc' => '',
            'nit' => '',
            'address' => 'San Salvador',
            'tax_regime' => 'Consumidor Final',
            'logo' => 'images/logo.png'
        ]);
    }
}
