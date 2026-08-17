<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Outlet::factory()->create([
            'name' => 'Pandu',
            'address' => 'Jalan Pandu No. 12, Sumerta Kelod, Denpasar',
            'phone_number' => '0895413368766'
        ]);

        Outlet::factory()->create([
            'name' => 'Tukad Badung',
            'address' => 'Jalan Tukad Badung XX No. 8A, Renon, Denpasar',
            'phone_number' => '0881080498066'
        ]);
    }
}
