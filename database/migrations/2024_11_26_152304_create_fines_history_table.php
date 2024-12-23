<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinesHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fines_history', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('student_name');
            $table->string('item_borrowed');
            $table->integer('quantity')->default(1);
            $table->integer('days_late')->default(0);
            $table->decimal('fines_amount', 8, 2);
            $table->string('payment_method');
            $table->decimal('cash_tendered', 8, 2)->nullable();
            $table->decimal('change', 8, 2)->nullable();
            $table->string('gcash_reference_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fines_history');
    }
}

