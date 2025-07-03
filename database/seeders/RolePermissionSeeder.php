<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create roles
        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'manager']);
        $defaultUser = Role::create(['name' => 'defaultUser']);

        // create permissions
        $bookDesks = Permission::create(['name' => 'can book rooms']);
        $booksRooms = Permission::create(['name' => 'can book desks']);
        $manageUsers = Permission::create(['name' => 'manage users']);

        $admin->givepermissionTo([$bookDesks, $booksRooms, $manageUsers]);
        $manager->givepermissionTo([$bookDesks, $booksRooms]);
        $defaultUser->givepermissionTo([$bookDesks]);
    }
}
