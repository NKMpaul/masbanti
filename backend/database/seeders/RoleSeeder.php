<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $roles = [
            'SUPER_ADMIN',
            'ADMIN_AGENCE',
            'EMPLOYE',
            'LIVREUR',
            'CLIENT',
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        } 
    }
}
