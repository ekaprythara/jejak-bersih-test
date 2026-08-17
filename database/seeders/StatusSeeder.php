<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Status untuk Transaksi Utama (Transaction Progress)
        Status::factory()->create([
            'name' => 'Pesanan Diterima',
            'type' => 'transaction_progress',
            'step' => 1,
            'isFinalStep' => false,
        ]);

        Status::factory()->create([
            'name' => 'Dalam Pengerjaan',
            'type' => 'transaction_progress',
            'step' => 2,
            'isFinalStep' => false,
        ]);

        Status::factory()->create([
            'name' => 'Siap Diambil',
            'type' => 'transaction_progress',
            'step' => 3,
            'isFinalStep' => false,
        ]);

        Status::factory()->create([
            'name' => 'Pesanan Selesai',
            'type' => 'transaction_progress',
            'step' => 4,
            'isFinalStep' => true,
        ]);

        // Status untuk Progress Per Sepatu (Shoes Progress)
        Status::factory()->create([
            'name' => 'Dalam Antrean',
            'type' => 'shoes_progress',
            'step' => 1,
            'isFinalStep' => false,
        ]);

        Status::factory()->create([
            'name' => 'Proses Pembersihan',
            'type' => 'shoes_progress',
            'step' => 2,
            'isFinalStep' => false,
        ]);

        Status::factory()->create([
            'name' => 'Pengeringan & Detailing',
            'type' => 'shoes_progress',
            'step' => 3,
            'isFinalStep' => false,
        ]);

        Status::factory()->create([
            'name' => 'Selesai Diperiksa',
            'type' => 'shoes_progress',
            'step' => 4,
            'isFinalStep' => true,
        ]);
    }
}
