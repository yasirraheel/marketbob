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
            $table->text('features')->nullable()->after('tags');
        });

        Schema::table('item_updates', function (Blueprint $table) {
            $table->text('validity_prices')->nullable()->after('screenshots');
            $table->text('features')->nullable()->after('tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['validity_prices', 'features']);
        });

        Schema::table('item_updates', function (Blueprint $table) {
            $table->dropColumn(['validity_prices', 'features']);
        });
    }
};
