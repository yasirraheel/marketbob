<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixGeminiAiExtensionSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update the existing Gemini AI extension to fix the settings structure
        $extension = DB::table('extensions')->where('alias', 'gemini_ai')->first();

        if ($extension) {
            // Decode existing settings
            $currentSettings = json_decode($extension->settings, true);

            // Extract only the flat values
            $newSettings = [
                'api_key' => $currentSettings['api_key'] ?? '',
                'model' => $currentSettings['model'] ?? 'gemini-pro',
            ];

            // Update with flat structure
            DB::table('extensions')
                ->where('alias', 'gemini_ai')
                ->update([
                    'settings' => json_encode($newSettings),
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to reverse this fix
    }
}
