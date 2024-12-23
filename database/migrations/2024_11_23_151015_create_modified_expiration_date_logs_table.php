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
        Schema::create('modified_expiration_date_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id'); // Foreign key to items table
            $table->string('item_name');       
            $table->integer('qty_in_stock')->default(0);
            $table->integer('quantity_added')->nullable();
            $table->date('new_expiration_date')->nullable();   
            $table->string('modified_by'); 
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modified_expiration_date_logs');
    }
};
