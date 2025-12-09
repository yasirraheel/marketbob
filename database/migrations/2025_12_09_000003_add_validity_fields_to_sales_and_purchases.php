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
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'validity_period')) {
                $table->integer('validity_period')->default(1)->after('item_id');
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'validity_period')) {
                $table->integer('validity_period')->default(1)->after('item_id');
            }
            if (!Schema::hasColumn('purchases', 'validity_expiry_at')) {
                $table->dateTime('validity_expiry_at')->nullable()->after('support_expiry_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'validity_period')) {
                $table->dropColumn('validity_period');
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'validity_period')) {
                $table->dropColumn('validity_period');
            }
            if (Schema::hasColumn('purchases', 'validity_expiry_at')) {
                $table->dropColumn('validity_expiry_at');
            }
        });
    }
};
