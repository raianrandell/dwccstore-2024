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
        Schema::create('charge_transaction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->string('charge_to'); // 'department' or 'faculty'
            $table->string('full_name')->nullable();
            $table->string('id_number')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('department')->nullable();
            $table->string('faculty_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charge_transaction');
    }
};
