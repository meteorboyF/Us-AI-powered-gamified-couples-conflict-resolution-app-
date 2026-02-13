<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use App\Services\CoupleService;
use App\Services\VaultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VaultFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_upload_stores_file_and_awards_first_upload_xp_once(): void
    {
        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);
        $service = app(VaultService::class);

        $service->uploadPhoto($couple, $user, UploadedFile::fake()->create('first.jpg', 100, 'image/jpeg'), [
            'visibility' => 'shared',
        ]);
        $service->uploadPhoto($couple, $user, UploadedFile::fake()->create('second.jpg', 100, 'image/jpeg'), [
            'visibility' => 'shared',
        ]);

        $this->assertDatabaseCount('memories', 2);
        $this->assertDatabaseCount('xp_events', 1);
        $this->assertDatabaseHas('xp_events', [
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'type' => 'vault',
            'xp_amount' => 5,
        ]);

        $memory = Memory::where('couple_id', $couple->id)->firstOrFail();
        Storage::disk('local')->assertExists($memory->file_path);
    }

    public function test_private_memory_is_hidden_from_partner(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        $memory = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $user->id,
            'type' => 'text',
            'description' => 'private memory',
            'visibility' => 'private',
        ]);

        $this->assertTrue($memory->canBeViewedBy($user));
        $this->assertFalse($memory->canBeViewedBy($partner));
    }

    public function test_locked_memory_cannot_be_changed_or_deleted(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);
        $service = app(VaultService::class);

        $memory = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $user->id,
            'type' => 'text',
            'description' => 'important memory',
            'visibility' => 'shared',
        ]);

        $service->lockMemory($memory, $user);
        $lockedMemory = $memory->fresh();
        $this->assertSame('locked', $lockedMemory->visibility);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Locked memories cannot have their visibility changed.');
        $service->changeVisibility($lockedMemory, $user, 'private');
    }
}
