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
        Schema::create('item_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('new_item_id')->nullable(); // New variant item ID
            $table->unsignedBigInteger('user_id')->nullable(); // Add the user_id column
            $table->string('item_name');
            $table->decimal('old_base_price', 10, 2)->nullable();
            $table->decimal('new_base_price', 10, 2)->nullable();
            $table->decimal('old_selling_price', 10, 2)->nullable();
            $table->decimal('new_selling_price', 10, 2)->nullable();
            $table->integer('old_qty_in_stock')->nullable();
            $table->integer('new_qty_in_stock')->nullable();
            $table->string('old_barcode')->nullable();
            $table->string('new_barcode')->nullable();
            $table->date('old_expiration_date')->nullable();
            $table->date('new_expiration_date')->nullable();
            $table->string('update_by');
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('new_item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_logs');
    }
};
