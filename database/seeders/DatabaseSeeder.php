<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reference data — order matters (countries before entities, etc.)
        $this->call([
            CountrySeeder::class,
            TaxRateSeeder::class,
            ContactFunctionSeeder::class,
            CalendarTypeSeeder::class,
            CalendarActionSeeder::class,
            PermissionSeeder::class,
            \Database\Seeders\EntitySeeder::class,
            UserSeeder::class,
        ]);
    }
}
