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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('sec_id')->constrained('sections')->onDelete('cascade'); // sec_id INT, foreign key referencing sections(id)
            $table->string('category_name', 100)->unique();
            $table->string('stock_no', 50)->nullable(false);
            $table->date('created_at'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
