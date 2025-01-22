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
        Schema::table('fines_history', function (Blueprint $table) {
            $table->string('cashier_name')->nullable()->after('actual_return_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fines_history', function (Blueprint $table) {
            $table->dropColumn('cashier_name');
        });
    }
};
