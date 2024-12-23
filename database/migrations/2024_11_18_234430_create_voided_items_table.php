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
        Schema::create('void_records', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->decimal('price', 10, 2);
            $table->unsignedBigInteger('user_id'); // Column for User ID
            $table->string('voided_by'); // Column for full name
            $table->timestamp('voided_at')->useCurrent(); // When it was voided
            
            // Foreign key for user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('void_records');
    }
};
