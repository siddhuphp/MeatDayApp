<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'user_id' => '4bace869-9bc0-424c-9292-111dac5b7ee7',
                'first_name' => 'Admin',
                'last_name' => 'Doe',
                'email' => 'admin@mailinator.com',
                'password' => Hash::make('India@123'),
                'role_id' => 'd266e700-1aab-4bd8-9987-8b8272e56e0d',
                'phone_no' => 991223385,
                'created_at' => now(),
                'updated_at' => now(),
                'email_verified_at' => now(),
            ],
            [
                'user_id' => Str::uuid(),
                'first_name' => 'Sid',
                'last_name' => 'Esunuri',
                'email' => 'sid@mailinator.com',
                'password' => Hash::make('India@123'),
                'role_id' => 'f41f0ea4-535f-48a0-89bc-53e049b0ab76',
                'phone_no' => 991223387,
                'created_at' => now(),
                'updated_at' => now(),
                'email_verified_at' => now(),
            ],
            [
                'user_id' => Str::uuid(),
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'email' => 'alice.johnson@example.com',
                'password' => Hash::make('password'),
                'role_id' => 'f41f0ea4-535f-48a0-89bc-53e049b0ab76',
                'phone_no' => 991223384,
                'created_at' => now(),
                'updated_at' => now(),
                'email_verified_at' => now(),
            ],
            // Add more users as needed
        ];

        DB::table('users')->insert($users);
    }
}
