<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Mahayana',
            'username' => 'mahayana',
            'email' => 'mahayana@example.com',
            'phone_number' => '085185008885',
            'role_id' => 1,
            'outlet_id' => null,
            'is_active' => true,
        ]);

        User::factory()->create([
            'name' => 'Chandra',
            'username' => 'chandra',
            'email' => 'chandra@example.com',
            'phone_number' => '085185008887',
            'role_id' => 2,
            'outlet_id' => 1,
            'is_active' => true,
        ]);
    }
}
