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
        Schema::create('transaction_shoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('shoe_brand');
            $table->string('shoe_color');
            $table->string('shoe_size')->nullable();
            $table->foreignId('status_id')->constrained('statuses');
            $table->text('shoe_condition')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_shoes');
    }
};
