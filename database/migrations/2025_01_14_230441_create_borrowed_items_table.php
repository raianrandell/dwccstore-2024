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
        Schema::create('borrowed_items', function (Blueprint $table) {
            $table->id(); // Primary key (auto-incrementing)
            $table->foreignId('borrower_id')->constrained('borrowers')->onDelete('cascade'); // Foreign key to borrowers table
            $table->foreignId('item_id')->constrained('item_for_rent')->onDelete('cascade'); // Foreign key to item_for_rent table
            $table->string('condition')->default('Good'); // Condition of the item (Good, Damaged, Lost)
            $table->date('borrowed_date'); // Date of borrowing
            $table->date('return_date')->nullable(); // Date of return (nullable)
            $table->date('actual_return_date')->nullable(); // Actual date of return (nullable)
            $table->string('status')->default('Borrowed'); // Borrowed, Returned
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowed_items');
    }
};
