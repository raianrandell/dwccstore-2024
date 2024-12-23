<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expired_items', function (Blueprint $table) {
            $table->id();
            $table->string('barcode');
            $table->string('item_name');
            $table->string('category');
            $table->integer('quantity');
            $table->date('date_encoded');
            $table->date('expiration_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expired_items');
    }
};

