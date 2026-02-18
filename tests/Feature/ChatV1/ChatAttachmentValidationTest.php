<?php

namespace Tests\Feature\ChatV1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\ChatV1\Concerns\CreatesChatV1Context;
use Tests\TestCase;

class ChatAttachmentValidationTest extends TestCase
{
    use CreatesChatV1Context;
    use RefreshDatabase;

    public function test_disallowed_attachment_mime_is_rejected(): void
    {
        Storage::fake('public');
        config(['us.chat.attachments_disk' => 'public']);

        $ctx = $this->createCouplePair();

        $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/messages', [
                'type' => 'attachment',
                'attachment' => UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload'),
            ])
            ->assertStatus(422);
    }

    public function test_allowed_image_attachment_is_accepted(): void
    {
        Storage::fake('public');
        config(['us.chat.attachments_disk' => 'public']);

        $ctx = $this->createCouplePair();

        $response = $this->actingAs($ctx['user'])
            ->postJson('/chat-v1/messages', [
                'type' => 'attachment',
                'attachment' => UploadedFile::fake()->create('photo.jpg', 120, 'image/jpeg'),
            ])
            ->assertCreated();

        $response->assertJsonPath('message.attachments.0.kind', 'image');

        $storedPath = data_get($response->json(), 'message.attachments.0.path');
        $this->assertNotNull($storedPath);
        Storage::disk('public')->assertExists($storedPath);
    }
}
