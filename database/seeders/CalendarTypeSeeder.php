<?php

namespace Database\Seeders;

use App\Models\CalendarType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CalendarTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $types = [
            'Reunião',
            'Visita',
            'Telefonema',
            'Email',
            'Videoconferência',
            'Feira / Evento',
            'Entrega',
            'Outro',
        ];

        foreach ($types as $name) {
            CalendarType::firstOrCreate(
                ['name' => $name],
                ['active' => true]
            );
        }
    }
}
