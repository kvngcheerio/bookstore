<?php

use Illuminate\Database\Seeder;
use App\Api\V1\Models\Role;
use App\Api\V1\Models\Permission;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Role $role, Permission $permission)
    {
        // Seed the default permissions
        $allPermissions = $permission->defaultPermissions();

        foreach ($allPermissions as $perms) {
            Permission::firstOrCreate(['name' => $perms]);
        }
        $this->command->info('Default Permissions added.');

        //super admin
        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $role->syncPermissions($permission->all());
        $this->command->info('Super Admin role created and granted all permissions'."\n");
        $this->createUser($role);
        $this->command->info('Super Admin User created');

        //admin
        $role = Role::firstOrCreate(['name' => 'admin']);
        $allPermissions = $permission->getAdminPermissions();
        $admin_permissions = $permission->whereIn('name', $allPermissions)->pluck('name');
        $role->syncPermissions($admin_permissions);
        $this->command->info('Admin User role created successfully.');
        $this->createUser($role);
        $this->command->info('Admin User created');
        

        //internal user
        $role = Role::firstOrCreate(['name' => 'internal_user']);
        $allPermissions = $permission->getInternalUserPermissions();
        $internal_user_permissions = $permission->whereIn('name', $allPermissions)->pluck('name');
        $role->syncPermissions($internal_user_permissions);
        $this->command->info('Internal user role created successfully.');

        //readers
        $role = Role::firstOrCreate(['name' => 'reader']);
        $allPermissions = $permission->getReaderPermissions();
        $reader_permissions  = $permission->whereIn('name', $allPermissions)->pluck('name');
        $role->syncPermissions($reader_permissions);

        $this->command->info('Default roles created successfully.');
        $this->command->warn('All done :)');
    }

    /**
     * Create a user with given role
     *
     * @param $role
     */
    private function createUser($role)
    {
        $name = $role->name;
        $user = factory(App\Api\V1\Models\User::class)
                    ->create([
                        'username' => $name,
                        'email' => $name . '@bookstore.com',
                        'first_name' => function () use ($name) {
                            return ($name == 'super_admin') ? 'Super' : 'Admin';
                        },
                        'last_name' => 'Admin'
                    ]);
        $user->internal_user()
            ->save(factory(App\Api\V1\Models\InternalUser::class)
            ->make([
                'user_id' => $user->id
            ]));
        $user->assignRole($name);

        if ($name == 'super_admin') {
            $this->command->info('Here is the super admin details to login:');
            $this->command->warn('Super Admin Username: \'' . $name . '@bookstore.com\'');
            $this->command->warn('Password: \'default1234\''."\n");
        }
    }
}
