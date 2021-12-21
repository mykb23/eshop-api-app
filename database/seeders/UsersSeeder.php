<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert(
            [
                'first_name'           => 'Admin',
                'last_name'           => 'User',
                'email'          => 'admin@admin.com',
                'password'       => bcrypt('password'),
                'role'           => 'admin',
                'remember_token' => null,
            ]
        );
        DB::table('users')->insert(
            [
                'first_name'           => 'Agent',
                'last_name'           => 'User',
                'email'          => 'agent@agent.com',
                'password'       => bcrypt('password'),
                'role'           => 'agent',
                'remember_token' => null,

            ]
        );
        DB::table('users')->insert(
            [
                'first_name'           => 'Customer',
                'last_name'           => 'User',
                'email'          => 'customer@cutomer.com',
                'password'       => bcrypt('password'),
                'remember_token' => null,
            ],
        );
        // $users = [];

        // User::insert($users);
    }
}
