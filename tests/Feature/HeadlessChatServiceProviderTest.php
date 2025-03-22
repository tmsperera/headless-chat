<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use TMSPerera\HeadlessChat\Models\Conversation;
use TMSPerera\HeadlessChat\Models\Message;
use TMSPerera\HeadlessChat\Models\Participation;
use TMSPerera\HeadlessChat\Models\ReadReceipt;

class HeadlessChatServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations()
    {
        $this->assertEmpty(Conversation::all());
        $this->assertEmpty(Participation::all());
        $this->assertEmpty(Message::all());
        $this->assertEmpty(ReadReceipt::all());
    }

    public function test_when_publishes_migrations()
    {
        $filePath = database_path('migrations/create_headless_chat_tables.php');
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        Artisan::call('vendor:publish', [
            '--tag' => 'headless-chat-migrations',
        ]);

        $this->assertTrue(File::exists($filePath));
        File::delete($filePath);
    }

    public function test_when_publishes_config()
    {
        $filePath = config_path('headless-chat.php');
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        Artisan::call('vendor:publish', [
            '--tag' => 'headless-chat-config',
        ]);

        $this->assertTrue(File::exists($filePath));
        File::delete($filePath);
    }
}
