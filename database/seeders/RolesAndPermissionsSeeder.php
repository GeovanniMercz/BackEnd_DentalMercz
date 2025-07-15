<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User\User; // Asegúrate que esta ruta es correcta según tu proyecto
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear permisos con guard api
        $permissions = [
            // Productos
            'view products',
            'create products',
            'edit products',
            'delete products',
            // Categorías
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api', // 👈 Aquí se indica el guard correcto
            ]);
        }

        // 2. Crear rol Admin con guard api y asignar permisos
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api', // 👈 También se indica aquí
        ]);

        $adminRole->syncPermissions($permissions);

        // 3. Crear usuario admin y asignar rol
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        $adminUser->assignRole($adminRole); // Laravel detecta el rol con guard api

        // 4. Crear token (opcional)
        $adminUser->createToken('Personal Access Token');

        $this->command->info('✅ Roles, permisos y usuario admin creados correctamente.');
    }
}
//a