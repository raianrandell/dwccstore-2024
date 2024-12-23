<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * This method creates the 'services_items' table with the necessary columns
     * and establishes foreign key relationships with the 'transactions' and
     * 'services' tables.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Foreign key to transactions table
            $table->unsignedBigInteger('transaction_id');
            $table->foreign('transaction_id')
                  ->references('id')->on('transactions')
                  ->onDelete('cascade'); // Deletes service items if the transaction is deleted

            // Foreign key to services table
            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                  ->references('id')->on('services')
                  ->onDelete('restrict'); // Prevents deletion of a service if it's linked to service items

            // Service details
            $table->decimal('price', 10, 2);
            $table->string('service_type');

            // Optional fields based on fee structure
            $table->integer('number_of_copies')->nullable();
            $table->decimal('number_of_hours', 5, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();

            // Total amount for this service item
            $table->decimal('total', 10, 2);

            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method drops the 'services_items' table if it exists.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services_items');
    }
}
