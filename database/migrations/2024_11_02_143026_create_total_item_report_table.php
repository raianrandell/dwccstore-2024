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
        Schema::create('total_item_report', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('item_name');
            $table->unsignedBigInteger('cat_id');
            $table->string('category_name');
            $table->integer('quantity');
            $table->string('unit');
            $table->decimal('base_price', 8, 2);
            $table->decimal('selling_price', 8, 2);
            $table->decimal('total_base_price', 10, 2)->storedAs('quantity * base_price');
            $table->decimal('total_selling_price', 10, 2)->storedAs('quantity * selling_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('total_item_report');
    }
};
