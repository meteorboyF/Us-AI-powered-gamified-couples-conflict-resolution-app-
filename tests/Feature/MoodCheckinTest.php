<?php

namespace Tests\Feature;

use App\Livewire\MoodCheckin\Create as CreateCheckin;
use App\Livewire\MoodCheckin\PartnerView;
use App\Models\MoodCheckin;
use App\Models\User;
use App\Services\CoupleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MoodCheckinTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_same_day_checkin_without_creating_duplicate(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);

        $this->actingAs($user);

        Livewire::test(CreateCheckin::class)
            ->set('moodLevel', 2)
            ->set('reasonTags', ['work'])
            ->set('needs', ['talk'])
            ->set('note', 'first')
            ->call('submit');

        Livewire::test(CreateCheckin::class)
            ->set('moodLevel', 4)
            ->set('reasonTags', ['family'])
            ->set('needs', ['space'])
            ->set('note', 'updated')
            ->call('submit');

        $this->assertSame(1, MoodCheckin::where('user_id', $user->id)->whereDate('date', today())->count());
        $this->assertDatabaseHas('mood_checkins', [
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'mood_level' => 4,
            'note' => 'updated',
        ]);
        $this->assertDatabaseCount('xp_events', 1);
        $this->assertDatabaseHas('worlds', [
            'couple_id' => $couple->id,
            'xp_total' => 10,
        ]);
    }

    public function test_partner_view_shows_summary_but_hides_private_note(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        MoodCheckin::create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'date' => today(),
            'mood_level' => 2,
            'needs' => ['talk'],
            'note' => 'this should stay private',
        ]);

        $this->actingAs($partner);

        $component = app(PartnerView::class);
        $component->mount();

        $this->assertNotNull($component->partnerMood);
        $this->assertSame(2, $component->partnerMood['mood_level']);
        $this->assertSame(['talk'], $component->partnerMood['needs']);
        $this->assertArrayNotHasKey('note', $component->partnerMood);
    }
}
