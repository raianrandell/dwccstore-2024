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
        Schema::create('items', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key (item_id)
            $table->foreignId('cat_id')->constrained('categories')->onDelete('cascade'); // Foreign key to categories(id)
            $table->string('barcode', 255)->nullable();
            $table->string('item_name', 100);
            $table->text('item_description')->nullable();
            $table->string('item_brand', 100)->nullable();
            $table->integer('qtyInStock')->default(0);
            $table->integer('low_stock_limit')->default(0);
            $table->string('unit_of_measurement', 45);
            $table->decimal('base_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->date('expiration_date')->nullable();
            $table->string('supplier_info', 100)->nullable();
            $table->string('status', 50);
            $table->string('size', 50)->nullable();
            $table->string('color', 50)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->boolean('is_perishable')->default(0);
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
