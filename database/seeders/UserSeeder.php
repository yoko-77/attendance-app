<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'ユーザー1（一般）',
            'email' => 'user1@example.com',
        ]);
        User::factory()->create([
            'name' => 'ユーザー2（一般）',
            'email' => 'user2@example.com',
        ]);
        User::factory()->create([
            'name' => 'ユーザー3（管理者）',
            'email' => 'user3@example.com',
            'admin_status' => true,
        ]);
    }
}
