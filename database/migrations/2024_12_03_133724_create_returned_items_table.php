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
        Schema::create('returned_items', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no');
            $table->string('item_name');
            $table->integer('return_quantity');
            $table->text('reason');
            $table->string('replacement_item')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returned_items');
    }
};

