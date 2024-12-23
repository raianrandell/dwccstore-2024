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
        Schema::table('services_items', function (Blueprint $table) {
            if (Schema::hasColumn('services_items', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services_items', function (Blueprint $table) {
            if (!Schema::hasColumn('services_items', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('number_of_hours');
            }
        });
    }
};
