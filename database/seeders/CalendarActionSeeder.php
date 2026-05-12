<?php

namespace Database\Seeders;

use App\Models\CalendarAction;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CalendarActionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $actions = [
            'Follow-up',
            'Proposta',
            'Pedido de Informação',
            'Reclamação',
            'Negociação',
            'Fecho de Negócio',
            'Pós-Venda',
            'Suporte Técnico',
            'Apresentação de Produto',
            'Outro',
        ];

        foreach ($actions as $name) {
            CalendarAction::firstOrCreate(
                ['name' => $name],
                ['active' => true]
            );
        }
    }
}
