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
        // Check if column doesn't exist before adding it
        if (!Schema::hasColumn('items', 'original_price')) {
            Schema::table('items', function (Blueprint $table) {
                $table->decimal('original_price', 10, 2)->nullable()->after('validity_prices');
            });
        }

        if (!Schema::hasColumn('item_updates', 'original_price')) {
            Schema::table('item_updates', function (Blueprint $table) {
                $table->decimal('original_price', 10, 2)->nullable()->after('validity_prices');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'original_price')) {
                $table->dropColumn('original_price');
            }
        });

        Schema::table('item_updates', function (Blueprint $table) {
            if (Schema::hasColumn('item_updates', 'original_price')) {
                $table->dropColumn('original_price');
            }
        });
    }
};
