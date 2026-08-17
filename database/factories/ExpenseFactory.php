<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_date' => $this->faker->date(),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->numberBetween(10000, 500000),
            'img_attachment' => $this->faker->optional()->imageUrl(640, 480, 'receipts', true),

            // Mengambil ID secara acak dari data kategori yang sudah ada di database
            'expense_category_id' => ExpenseCategory::inRandomOrder()->first()?->id ?? ExpenseCategory::factory(),

            // (Opsional) Lakukan hal yang sama untuk User dan Outlet jika sudah ada datanya
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'outlet_id' => Outlet::inRandomOrder()->first()?->id ?? Outlet::factory(),
        ];
    }
}
