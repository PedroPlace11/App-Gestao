<?php

namespace Database\Seeders;

use App\Models\TaxRate;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TaxRateSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $rates = [
            ['name' => 'Isento (0%)',        'rate' => 0.00],
            ['name' => 'Taxa Reduzida (6%)', 'rate' => 6.00],
            ['name' => 'Taxa Intermédia (13%)', 'rate' => 13.00],
            ['name' => 'Taxa Normal (23%)',  'rate' => 23.00],
        ];

        foreach ($rates as $rate) {
            TaxRate::firstOrCreate(
                ['rate' => $rate['rate']],
                array_merge($rate, ['active' => true])
            );
        }
    }
}
