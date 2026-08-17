<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date("expense_date");
            $table->text("description")->nullable();
            $table->unsignedBigInteger('amount');
            $table->text("image_url")->nullable();
            $table->string('image_public_id')->nullable();
            $table->foreignId("expense_category_id")
                ->constrained('expense_categories')
                ->restrictOnDelete();
            $table->foreignId("user_id")
                ->constrained("users")
                ->cascadeOnDelete();
            $table->foreignId("outlet_id")
                ->nullable()
                ->constrained("outlets")
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
