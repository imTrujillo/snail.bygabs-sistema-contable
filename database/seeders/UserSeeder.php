<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Administrador']);
        Role::create(['name' => 'Contador']);
        Role::create(['name' => 'Auxiliar']);
        Role::create(['name' => 'Empleado']);

        User::create([
            'name' => 'Administrador',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => 1
        ]);
        User::create([
            'name' => 'Contador',
            'email' => 'contador@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => 2
        ]);
        User::create([
            'name' => 'Auxiliar',
            'email' => 'auxiliar@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => 3
        ]);
        User::create([
            'name' => 'Empleado',
            'email' => 'empleado@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => 4
        ]);
    }
}
