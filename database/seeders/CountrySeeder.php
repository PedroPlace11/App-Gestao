<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CountrySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $countries = [
            ['name' => 'Portugal',          'code' => 'PT'],
            ['name' => 'Espanha',           'code' => 'ES'],
            ['name' => 'França',            'code' => 'FR'],
            ['name' => 'Alemanha',          'code' => 'DE'],
            ['name' => 'Reino Unido',       'code' => 'GB'],
            ['name' => 'Itália',            'code' => 'IT'],
            ['name' => 'Países Baixos',     'code' => 'NL'],
            ['name' => 'Bélgica',           'code' => 'BE'],
            ['name' => 'Suíça',             'code' => 'CH'],
            ['name' => 'Áustria',           'code' => 'AT'],
            ['name' => 'Polónia',           'code' => 'PL'],
            ['name' => 'Suécia',            'code' => 'SE'],
            ['name' => 'Noruega',           'code' => 'NO'],
            ['name' => 'Dinamarca',         'code' => 'DK'],
            ['name' => 'Finlândia',         'code' => 'FI'],
            ['name' => 'Brasil',            'code' => 'BR'],
            ['name' => 'Estados Unidos',    'code' => 'US'],
            ['name' => 'Angola',            'code' => 'AO'],
            ['name' => 'Moçambique',        'code' => 'MZ'],
            ['name' => 'Cabo Verde',        'code' => 'CV'],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(
                ['code' => $country['code']],
                array_merge($country, ['active' => true])
            );
        }
    }
}
