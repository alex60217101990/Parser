<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        DB::table('roles')->delete();
        DB::table('permissions')->delete();
        DB::table('role_has_permissions')->delete();
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // create permissions
        Permission::create(['name' => 'Admin panel']);
        Permission::create(['name' => 'Look ads list']);
        Permission::create(['name' => 'Upload a photo']);
        Permission::create(['name' => 'Update ads list']);

        // create roles and assign existing permissions
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(['Admin panel', 'Look ads list', 'Upload a photo', 'Update ads list']);

        $role = Role::create(['name' => 'user']);
        $role->givePermissionTo(['Look ads list', 'Upload a photo']);
    }
}
