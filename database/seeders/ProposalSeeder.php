<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProposalSeeder extends Seeder
{
    public function run(): void
    {
        $entity = Entity::first();
        $user   = User::first();

        if (! $entity || ! $user) {
            $this->command->warn('Sem entidades ou utilizadores. Cria primeiro esses registos.');
            return;
        }

        $proposals = [
            [
                'number'      => 'PROP-2026-001',
                'date'        => '2026-05-08',
                'client_id'   => $entity->id,
                'validity'    => '2026-06-08',
                'total_value' => 1450.00,
                'status'      => 'draft',
                'user_id'     => $user->id,
            ],
            [
                'number'      => 'PROP-2026-002',
                'date'        => '2026-05-09',
                'client_id'   => $entity->id,
                'validity'    => '2026-06-09',
                'total_value' => 2890.50,
                'status'      => 'closed',
                'user_id'     => $user->id,
            ],
        ];

        foreach ($proposals as $data) {
            Proposal::firstOrCreate(['number' => $data['number']], $data);
        }

        $this->command->info('Propostas de teste criadas.');
    }
}
