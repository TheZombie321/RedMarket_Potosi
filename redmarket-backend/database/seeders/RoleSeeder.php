<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleAdmin = Role::firstOrCreate(['name' => 'Administrador']);
        $roleEncargado = Role::firstOrCreate(['name' => 'Encargado']);
        $rolePicking = Role::firstOrCreate(['name' => 'Picking']);
        $roleRepartidor = Role::firstOrCreate(['name' => 'Repartidor']);
        $roleCliente = Role::firstOrCreate(['name' => 'Cliente']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@redmarket.com'],
            ['name' => 'Admin RedMarket', 'password' => bcrypt('password123')]
        );
        $admin->assignRole($roleAdmin);

        $cliente = User::firstOrCreate(
            ['email' => 'cliente@ejemplo.com'],
            ['name' => 'Cliente Potosí', 'password' => bcrypt('password123')]
        );
        $cliente->assignRole($roleCliente);

        $repartidor = User::firstOrCreate(
            ['email' => 'repartidor@redmarket.com'],
            ['name' => 'Repartidor Pérez', 'password' => bcrypt('password123')]
        );
        $repartidor->assignRole($roleRepartidor);

        $picker = User::firstOrCreate(
            ['email' => 'picker@test.com'],
            ['name' => 'Picker García', 'password' => bcrypt('password123')]
        );
        $picker->assignRole($rolePicking);

        $encargado = User::firstOrCreate(
            ['email' => 'juan@redmarket.com'],
            ['name' => 'Juan Encargado', 'password' => bcrypt('password123')]
        );
        $encargado->assignRole($roleEncargado);
    }
}