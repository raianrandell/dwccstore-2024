<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemForRentTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_for_rent', function (Blueprint $table) {
            $table->id();
            $table->string('item_name'); // Name of the item
            $table->integer('total_quantity'); // Total available quantity
            $table->integer('quantity_borrowed')->default(0); // Quantity currently borrowed
            $table->timestamps(); // Created at and updated at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_for_rent');
    }
}
