<?php

namespace Database\Seeders;

use App\Http\Controllers\CustomerController;
use App\Models\Outlet;
use App\Models\Role;
use App\Models\User;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Service;
use App\Models\ExpenseCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Customer::factory()->count(25)->create();

        $this->call([
            RoleSeeder::class,
            OutletSeeder::class,
            StatusSeeder::class,
            UserSeeder::class,
            ServiceSeeder::class,
            ExpenseCategorySeeder::class,
            // ExpenseSeeder::class
        ]);

        // Pastikan relasi utama sudah ada (atau buat secara otomatis)
        // Contoh jika ingin membuat 50 data pengeluaran sekaligus:
        // Expense::factory(125)->create();
    }
}
