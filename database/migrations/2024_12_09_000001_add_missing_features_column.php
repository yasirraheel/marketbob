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
        // Check if columns don't exist before adding them
        if (!Schema::hasColumn('items', 'validity_prices')) {
            Schema::table('items', function (Blueprint $table) {
                $table->text('validity_prices')->nullable()->after('screenshots');
            });
        }

        if (!Schema::hasColumn('items', 'features')) {
            Schema::table('items', function (Blueprint $table) {
                $table->text('features')->nullable()->after('tags');
            });
        }

        if (!Schema::hasColumn('item_updates', 'validity_prices')) {
            Schema::table('item_updates', function (Blueprint $table) {
                $table->text('validity_prices')->nullable()->after('screenshots');
            });
        }

        if (!Schema::hasColumn('item_updates', 'features')) {
            Schema::table('item_updates', function (Blueprint $table) {
                $table->text('features')->nullable()->after('tags');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'validity_prices')) {
                $table->dropColumn('validity_prices');
            }
            if (Schema::hasColumn('items', 'features')) {
                $table->dropColumn('features');
            }
        });

        Schema::table('item_updates', function (Blueprint $table) {
            if (Schema::hasColumn('item_updates', 'validity_prices')) {
                $table->dropColumn('validity_prices');
            }
            if (Schema::hasColumn('item_updates', 'features')) {
                $table->dropColumn('features');
            }
        });
    }
};
