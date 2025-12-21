<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFacebookPixelExtension extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('extensions')->insert([
            'name' => 'Facebook Pixel',
            'alias' => 'facebook_pixel',
            'logo' => 'images/extensions/facebook-pixel.png',
            'settings' => json_encode([
                'pixel_id' => ''
            ]),
            'instructions' => '<p>Follow these steps to setup Facebook Pixel:</p>
<ol>
    <li>Go to <a href="https://facebook.com/business" target="_blank">Facebook Business Manager</a></li>
    <li>Navigate to Events Manager</li>
    <li>Select your website data source</li>
    <li>Copy your Pixel ID from the Web Setup section</li>
    <li>Paste it in the Pixel ID field below</li>
    <li>Enable the extension and save settings</li>
</ol>',
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
        DB::table('extensions')->where('alias', 'facebook_pixel')->delete();
    }
}
