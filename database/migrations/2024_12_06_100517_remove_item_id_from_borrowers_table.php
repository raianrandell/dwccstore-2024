<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('borrowers', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['item_id']);
            // Drop the column
            $table->dropColumn('item_id');
        });
    }

    public function down()
    {
        Schema::table('borrowers', function (Blueprint $table) {
            // Add the column back
            $table->unsignedBigInteger('item_id')->nullable();
            // Recreate the foreign key constraint
            $table->foreign('item_id')->references('id')->on('item_for_rent')->onDelete('cascade');
        });
    }    
};
