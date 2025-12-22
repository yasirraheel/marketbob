<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddGeminiAiExtension extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('extensions')->insert([
            'name' => 'Gemini AI',
            'alias' => 'gemini_ai',
            'logo' => 'images/extensions/gemini-ai.png',
            'settings' => json_encode([
                'api_key' => '',
                'model' => 'gemini-pro',
                'enabled_features' => [
                    'description_generation' => true,
                    'tag_suggestions' => true,
                    'content_improvement' => true,
                ]
            ]),
            'instructions' => '<p>Follow these steps to setup Gemini AI:</p>
<ol>
    <li>Go to <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a></li>
    <li>Click on "Create API Key"</li>
    <li>Select your Google Cloud project or create a new one</li>
    <li>Copy your API key</li>
    <li>Paste it in the API Key field below</li>
    <li>Select your preferred model (default: gemini-pro)</li>
    <li>Enable the extension and save settings</li>
</ol>
<p><strong>Features:</strong></p>
<ul>
    <li>AI-generated product descriptions</li>
    <li>Automatic tag suggestions</li>
    <li>Content improvement and SEO optimization</li>
</ul>',
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('extensions')->where('alias', 'gemini_ai')->delete();
    }
}
