<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppointmentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'appointment_index',
            'appointment_store',
            'appointment_show',
            'appointment_update',
            'appointment_destroy',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api']
            );
        }

        // (Opcional) Asignar permisos al rol admin
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);

        $adminRole->syncPermissions($permissions);
    }
}
