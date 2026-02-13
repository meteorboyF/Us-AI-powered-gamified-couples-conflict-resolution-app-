<?php

namespace Tests\Feature;

use App\Models\Memory;
use App\Models\User;
use App\Services\CoupleService;
use App\Services\VaultService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class VaultFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_upload_stores_file_and_awards_first_upload_xp_once(): void
    {
        Storage::fake('public');

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
        Storage::disk('public')->assertExists($memory->file_path);
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

    public function test_dual_memory_cannot_be_changed_or_deleted(): void
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
        $this->assertSame('dual', $lockedMemory->visibility);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Dual-consent memories cannot have their visibility changed.');
        $service->changeVisibility($lockedMemory, $user, 'private');
    }

    public function test_dual_memory_requires_both_approvals_to_unlock(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($owner);
        $coupleService->joinCouple($partner, $couple->invite_code);
        $service = app(VaultService::class);

        $memory = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'description' => 'dual memory',
            'visibility' => 'dual',
        ]);

        $service->approveDualUnlock($memory, $owner);
        $this->assertFalse($memory->fresh()->canBeViewedBy($owner));
        $this->assertFalse($memory->fresh()->canBeViewedBy($partner));

        $service->approveDualUnlock($memory, $partner);
        $this->assertTrue($memory->fresh()->canBeViewedBy($owner));
        $this->assertTrue($memory->fresh()->canBeViewedBy($partner));
    }

    public function test_user_cannot_approve_dual_unlock_twice_while_active(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($owner);
        $coupleService->joinCouple($partner, $couple->invite_code);
        $service = app(VaultService::class);

        $memory = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'description' => 'dual memory',
            'visibility' => 'dual',
        ]);

        $service->approveDualUnlock($memory, $owner);

        $this->expectException(AuthorizationException::class);
        $service->approveDualUnlock($memory, $owner);
    }

    public function test_dual_unlock_expires_when_approvals_expire(): void
    {
        Carbon::setTestNow(now());
        try {
            $owner = User::factory()->create();
            $partner = User::factory()->create();
            $coupleService = app(CoupleService::class);
            $couple = $coupleService->createCouple($owner);
            $coupleService->joinCouple($partner, $couple->invite_code);
            $service = app(VaultService::class);

            $memory = Memory::create([
                'couple_id' => $couple->id,
                'created_by' => $owner->id,
                'type' => 'text',
                'description' => 'expiring memory',
                'visibility' => 'dual',
            ]);

            $service->approveDualUnlock($memory, $owner);
            $service->approveDualUnlock($memory, $partner);
            $this->assertTrue($memory->fresh()->canBeViewedBy($owner));

            Carbon::setTestNow(now()->addMinutes(VaultService::DUAL_UNLOCK_TTL_MINUTES + 1));
            $this->assertFalse($memory->fresh()->canBeViewedBy($owner));
            $this->assertFalse($memory->fresh()->canBeViewedBy($partner));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_comfort_toggle_permissions_for_private_shared_and_dual(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();
        $outsider = User::factory()->create();
        $outsiderPartner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($owner);
        $coupleService->joinCouple($partner, $couple->invite_code);
        $outsiderCouple = $coupleService->createCouple($outsider);
        $coupleService->joinCouple($outsiderPartner, $outsiderCouple->invite_code);
        $service = app(VaultService::class);

        $privateMemory = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'description' => 'private',
            'visibility' => 'private',
        ]);

        $sharedMemory = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'description' => 'shared',
            'visibility' => 'shared',
        ]);

        $dualMemory = Memory::create([
            'couple_id' => $couple->id,
            'created_by' => $owner->id,
            'type' => 'text',
            'description' => 'dual',
            'visibility' => 'dual',
        ]);

        $service->toggleComfort($privateMemory, $owner);
        $this->assertTrue($privateMemory->fresh()->comfort);

        try {
            $service->toggleComfort($privateMemory, $partner);
            $this->fail('Partner should not toggle comfort on private memory they do not own.');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('This action is unauthorized.', $e->getMessage());
        }

        $service->toggleComfort($sharedMemory, $partner);
        $this->assertTrue($sharedMemory->fresh()->comfort);

        $service->toggleComfort($dualMemory, $partner);
        $this->assertTrue($dualMemory->fresh()->comfort);

        try {
            $service->toggleComfort($sharedMemory, $outsider);
            $this->fail('Outsider should not toggle comfort on another couple memory.');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Unauthorized couple access.', $e->getMessage());
        }
    }

    public function test_upload_failure_shows_friendly_message_without_throwing(): void
    {
        $user = User::factory()->create();
        app(CoupleService::class)->createCouple($user);

        $this->mock(VaultService::class, function ($mock) {
            $mock->shouldReceive('createTextMemory')
                ->once()
                ->andThrow(new \RuntimeException('internal failure'));
        });

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Vault\Upload::class)
            ->set('uploadType', 'text')
            ->set('description', 'A small memory')
            ->set('visibility', 'shared')
            ->call('save')
            ->assertSee('Upload failed, please try again.');
    }
}
