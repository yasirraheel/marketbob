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
        Schema::table('items', function (Blueprint $table) {
            $table->text('validity_prices')->nullable()->after('screenshots');
        });

        Schema::table('item_updates', function (Blueprint $table) {
            $table->text('validity_prices')->nullable()->after('screenshots');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('validity_prices');
        });

        Schema::table('item_updates', function (Blueprint $table) {
            $table->dropColumn('validity_prices');
        });
    }
};
