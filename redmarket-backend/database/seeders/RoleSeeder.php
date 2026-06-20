<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear los 5 roles operativos
        $roleAdmin = Role::create(['name' => 'Administrador']);
        $roleEncargado = Role::create(['name' => 'Encargado']);
        $rolePicking = Role::create(['name' => 'Picking']);
        $roleRepartidor = Role::create(['name' => 'Repartidor']);
        $roleCliente = Role::create(['name' => 'Cliente']);

        // 2. Crear usuario Administrador de prueba
        $admin = User::create([
            'name' => 'Admin RedMarket',
            'email' => 'admin@redmarket.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->assignRole($roleAdmin);

        // 3. Crear usuario Cliente de prueba
        $cliente = User::create([
            'name' => 'Cliente Potosí',
            'email' => 'cliente@ejemplo.com',
            'password' => bcrypt('password123'),
        ]);
        $cliente->assignRole($roleCliente);

        // 4. Staff test users
        $repartidor = User::create([
            'name' => 'Repartidor Pérez',
            'email' => 'repartidor@redmarket.com',
            'password' => bcrypt('password123'),
        ]);
        $repartidor->assignRole($roleRepartidor);

        $picker = User::create([
            'name' => 'Picker García',
            'email' => 'picker@test.com',
            'password' => bcrypt('password123'),
        ]);
        $picker->assignRole($rolePicking);

        $encargado = User::create([
            'name' => 'Juan Encargado',
            'email' => 'juan@redmarket.com',
            'password' => bcrypt('password123'),
        ]);
        $encargado->assignRole($roleEncargado);
    }
}