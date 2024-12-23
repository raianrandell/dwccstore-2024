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
        Schema::create('transfer_item_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_item_id');
            $table->unsignedBigInteger('target_item_id');
            $table->string('item_name');
            $table->string('transfer_to'); 
            $table->integer('transferred_quantity');
            $table->decimal('base_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->string('transferred_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_item_logs');
    }
};
