<?php

namespace Database\Seeders;

use App\Models\Expense;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Expense::factory()->create([
            'expense_date' => now()->subDays(5)->format('Y-m-d'),
            'description' => 'Pembayaran Listrik dan Air',
            'amount' => 150000,
            'img_attachment' => null,
            'expense_category_id' => 4,
            'user_id' => 2,
            'outlet_id' => 2,
        ]);
    }
}
