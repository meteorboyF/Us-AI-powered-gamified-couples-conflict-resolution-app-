<?php

namespace Tests\Feature\Checkins;

use App\Models\Couple;
use App\Models\CoupleMember;
use App\Models\DailyCheckin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyCheckinEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_checkins_today_returns_own_and_partner_when_available(): void
    {
        Carbon::setTestNow('2026-02-18');

        [$user, $partner, $couple] = $this->createCouplePair();

        DailyCheckin::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'checkin_date' => Carbon::today()->toDateString(),
            'mood' => 'good',
            'note' => 'Feeling okay',
        ]);

        DailyCheckin::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $partner->id,
            'checkin_date' => Carbon::today()->toDateString(),
            'mood' => 'great',
            'note' => 'Excited',
        ]);

        $this->actingAs($user)
            ->getJson('/checkins/today')
            ->assertOk()
            ->assertJsonPath('own.mood', 'good')
            ->assertJsonPath('partner.mood', 'great');
    }

    public function test_post_checkins_upserts_current_users_checkin_for_today(): void
    {
        Carbon::setTestNow('2026-02-18');

        [$user, $partner, $couple] = $this->createCouplePair();

        $payload = [
            'mood' => 'okay',
            'note' => 'First note',
        ];

        $this->actingAs($user)->postJson('/checkins', $payload)->assertOk();

        $this->actingAs($user)->postJson('/checkins', [
            'mood' => 'great',
            'note' => 'Updated note',
        ])->assertOk();

        $this->assertDatabaseCount('daily_checkins', 1);
        $checkin = \App\Models\DailyCheckin::query()
            ->where('couple_id', $couple->id)
            ->where('user_id', $user->id)
            ->whereDate('checkin_date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($checkin);
        $this->assertSame('great', $checkin->mood);
        $this->assertSame('Updated note', $checkin->note);
    }

    public function test_user_not_in_current_couple_gets_forbidden_on_checkins(): void
    {
        [$owner, $partner, $couple] = $this->createCouplePair();
        $intruder = User::factory()->create();
        $intruder->forceFill(['current_couple_id' => $couple->id])->save();

        $this->actingAs($intruder)
            ->getJson('/checkins/today')
            ->assertForbidden();
    }

    public function test_user_with_no_current_couple_gets_conflict_on_checkins(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/checkins', [
                'mood' => 'good',
                'note' => 'No couple',
            ])
            ->assertStatus(409);
    }

    /**
     * @return array{User,User,Couple}
     */
    private function createCouplePair(): array
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $couple = Couple::query()->create([
            'name' => 'Checkin Couple',
            'invite_code' => 'CHK'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'created_by_user_id' => $user->id,
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        CoupleMember::query()->create([
            'couple_id' => $couple->id,
            'user_id' => $partner->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $user->forceFill(['current_couple_id' => $couple->id])->save();
        $partner->forceFill(['current_couple_id' => $couple->id])->save();

        return [$user, $partner, $couple];
    }
}
