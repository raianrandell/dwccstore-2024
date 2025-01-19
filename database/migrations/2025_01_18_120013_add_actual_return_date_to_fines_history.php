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
            $table->timestamp('actual_return_date')->nullable()->after('gcash_reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fines_history', function (Blueprint $table) {
            $table->dropColumn('actual_return_date');
        });
    }
};
