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
        Schema::table('cart_items', function (Blueprint $table) {
            // Add validity_period column if it doesn't exist
            if (!Schema::hasColumn('cart_items', 'validity_period')) {
                $table->integer('validity_period')->default(1)->after('item_id');
            }
            // Drop license_type column if it exists
            if (Schema::hasColumn('cart_items', 'license_type')) {
                $table->dropColumn('license_type');
            }
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            // Add validity_period column if it doesn't exist
            if (!Schema::hasColumn('transaction_items', 'validity_period')) {
                $table->integer('validity_period')->default(1)->after('item_id');
            }
            // Drop license_type column if it exists
            if (Schema::hasColumn('transaction_items', 'license_type')) {
                $table->dropColumn('license_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'license_type')) {
                $table->integer('license_type')->default(1)->after('item_id');
            }
            if (Schema::hasColumn('cart_items', 'validity_period')) {
                $table->dropColumn('validity_period');
            }
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_items', 'license_type')) {
                $table->integer('license_type')->default(1)->after('item_id');
            }
            if (Schema::hasColumn('transaction_items', 'validity_period')) {
                $table->dropColumn('validity_period');
            }
        });
    }
};
