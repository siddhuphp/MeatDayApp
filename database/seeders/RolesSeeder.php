<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'role_id' => 'd266e700-1aab-4bd8-9987-8b8272e56e0d',
                'name' => 'Admin',
                'fixed_role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 'f41f0ea4-535f-48a0-89bc-53e049b0ab76',
                'name' => 'User',
                'fixed_role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => '8a9adc27-af9a-4c1e-a37d-7f7151c0bdcf',
                'name' => 'Content Creator',
                'fixed_role' => 'content_creator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => '8a9xcd67-af9a-4c1e-a37d-7f7151c0bder',
                'name' => 'Manager',
                'fixed_role' => 'manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => 'z51f0ea4-535f-48a0-89bc-53e049b0ab76',
                'name' => 'Tester',
                'fixed_role' => 'tester',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
        DB::table('roles')->insert($data);
    }
}
