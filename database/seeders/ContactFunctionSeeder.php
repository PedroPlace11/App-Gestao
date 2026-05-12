<?php

namespace Database\Seeders;

use App\Models\ContactFunction;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ContactFunctionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $functions = [
            'Gerente',
            'Diretor Geral',
            'Diretor Comercial',
            'Diretor Financeiro',
            'Diretor de Operações',
            'Técnico',
            'Comercial',
            'Administrativo',
            'Contabilista',
            'Responsável de Compras',
            'Responsável de Logística',
            'Responsável de Qualidade',
            'Engenheiro',
            'Assistente',
            'Outro',
        ];

        foreach ($functions as $name) {
            ContactFunction::firstOrCreate(
                ['name' => $name],
                ['active' => true]
            );
        }
    }
}
