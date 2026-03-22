<?php

namespace Database\Seeders;

use App\Models\FiscalPeriod;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FiscalPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = now()->year;

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();

            FiscalPeriod::create([
                'name' => $start->locale('es')->isoFormat('MMMM-YY'),
                'start_date' => $start,
                'end_date' => $end,
                'is_closed' => false
            ]);
        }
    }
}
