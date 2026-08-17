<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Reguler Cleaning',
                'description' => 'Membersihkan debu, kotoran ringan, noda pemakaian harian, bagian luar sepatu, dan sol sepatu.',
                'price' => 30000,
                'estimated_days' => 2,
            ],
            [
                'name' => 'Deep Cleaning',
                'description' => 'Pembersihan menyeluruh untuk menghilangkan noda membandel dan membersihkan detail sepatu.',
                'price' => 50000,
                'estimated_days' => 3,
            ],
            [
                'name' => 'Unyellowing',
                'description' => 'Perawatan khusus untuk menghilangkan warna kuning pada bagian sol sepatu.',
                'price' => 75000,
                'estimated_days' => 5,
            ],
            [
                'name' => 'Repaint',
                'description' => 'Pengecatan ulang sepatu untuk memperbaiki warna yang pudar, lecet, atau rusak.',
                'price' => 100000,
                'estimated_days' => 7,
            ],
            [
                'name' => 'Repair',
                'description' => 'Perbaikan seperti lem ulang sol, jahitan kecil, atau perbaikan bagian sepatu tertentu.',
                'price' => 50000,
                'estimated_days' => 5,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
