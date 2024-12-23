<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBorrowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('borrowers', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->index();
            $table->string('student_name');
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity')->default(1);
            $table->date('date_issued');
            $table->date('expected_date_returned');
            $table->date('actual_date_returned')->nullable();
            $table->timestamps();

            // Foreign key relationship with item_for_rent table
            $table->foreign('item_id')->references('id')->on('item_for_rent')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('borrowers');
    }
}

