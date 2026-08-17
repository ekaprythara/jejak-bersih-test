<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExpenseCategory::factory()->create([
            'category_name' => 'Bahan',
        ]);

        ExpenseCategory::factory()->create([
            'category_name' => 'Operasional',
        ]);

        ExpenseCategory::factory()->create([
            'category_name' => 'Pemasaran',
        ]);

        ExpenseCategory::factory()->create([
            'category_name' => 'Listrik & Air',
        ]);

        ExpenseCategory::factory()->create([
            'category_name' => 'Pemeliharaan',
        ]);

        ExpenseCategory::factory()->create([
            'category_name' => 'Transportasi',
        ]);

        ExpenseCategory::factory()->create([
            'category_name' => 'Sewa',
        ]);
    }
}
