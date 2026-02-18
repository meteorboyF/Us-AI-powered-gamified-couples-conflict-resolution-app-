<?php

namespace Tests\Feature\Vault;

use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Vault\Concerns\CreatesVaultContext;
use Tests\TestCase;

class VaultUploadTest extends TestCase
{
    use CreatesVaultContext;
    use RefreshDatabase;

    public function test_valid_upload_succeeds_and_persists_metadata(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $item = VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'type' => 'note',
            'title' => 'With media',
            'body' => 'Body',
        ]);

        $file = UploadedFile::fake()->create('memory.jpg', 50, 'image/jpeg');

        $this->actingAs($user)
            ->post('/vault/'.$item->id.'/media', ['media' => $file], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('item.media.mime', 'image/jpeg');

        $this->assertDatabaseHas('vault_items', [
            'id' => $item->id,
            'media_mime' => 'image/jpeg',
            'type' => 'photo',
        ]);
    }

    public function test_invalid_upload_mime_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        [$couple] = $this->createCoupleWithPartner($user);

        $item = VaultItem::query()->create([
            'couple_id' => $couple->id,
            'created_by_user_id' => $user->id,
            'type' => 'note',
            'title' => 'Upload target',
            'body' => 'Body',
        ]);

        $file = UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload');

        $this->actingAs($user)
            ->post('/vault/'.$item->id.'/media', ['media' => $file], ['Accept' => 'application/json'])
            ->assertStatus(422);
    }
}
