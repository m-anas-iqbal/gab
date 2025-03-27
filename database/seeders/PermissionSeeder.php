<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;


class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [

            // Member Permissions
            ['name' => 'member_management'],
            ['name' => 'create_member'],
            ['name' => 'edit_member'],
            ['name' => 'delete_member'],
            ['name' => 'view_member'],
            ['name' => 'update_member_status'],
            ['name' => 'manage_permissions'],
            ['name' => 'assign_roles'],
            ['name' => 'manage_roles'],

            // General Permissions
            ['name' => 'organization_management'],
            ['name' => 'organization_profile_view'],
            ['name' => 'organization_profile_update'],
            ['name' => 'view_dashboard'],
            ['name' => 'view_transaction'],
            ['name' => 'upgrade_plan'],


            // Group Member Permissions
            ['name' => 'group_management'],
            // Group Permissions
            ['name' => 'create_group'],
            ['name' => 'edit_group'],
            ['name' => 'delete_group'],
            ['name' => 'view_group'],
            ['name' => 'reset_group_code'],
            ['name' => 'view_group_members'],
            ['name' => 'update_group_join_requests'],
            ['name' => 'add_members_to_group'],
            ['name' => 'generate_group_invite_link'],
            ['name' => 'generate_group_invite_code'],
            ['name' => 'make_group_member_admin'],
            ['name' => 'remove_group_member_admin'],
            ['name' => 'remove_member_from_group'],
            ['name' => 'leave_group'],
            ['name' => 'archive'],
        ];


        // Insert permissions into the database
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission['name'],
                // 'guard' => 'api'
            ]);
        }
    }
}
