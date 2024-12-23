<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemNameToDamageTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('damage_transactions', function (Blueprint $table) {
            // Adding the item_name column after item_id for better readability
            $table->string('item_name')->after('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('damage_transactions', function (Blueprint $table) {
            $table->dropColumn('item_name');
        });
    }
}
