<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrador',
                'email' => 'admin@inovcorp.pt',
                'password' => Hash::make('password'),
                'active' => true,
                'role' => 'admin',
            ],
            [
                'name' => 'Joao Gestor',
                'email' => 'joao.gestor@inovcorp.pt',
                'password' => Hash::make('Password123'),
                'active' => true,
                'role' => 'manager',
            ],
            [
                'name' => 'Marta Comercial',
                'email' => 'marta.comercial@inovcorp.pt',
                'password' => Hash::make('Password123'),
                'active' => true,
                'role' => 'manager',
            ],
            [
                'name' => 'Rui Consulta',
                'email' => 'rui.consulta@inovcorp.pt',
                'password' => Hash::make('Password123'),
                'active' => false,
                'role' => 'viewer',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            $user->syncRoles([$role]);
        }
    }
}
