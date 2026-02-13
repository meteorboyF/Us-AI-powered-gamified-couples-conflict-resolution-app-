<?php

namespace Tests\Feature;

use App\Livewire\Gifts\Index as GiftsIndex;
use App\Livewire\Gifts\Suggestions as GiftSuggestionsComponent;
use App\Livewire\Gifts\WishlistForm;
use App\Models\GiftSuggestion;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\CoupleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class GiftsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_access_gifts_route_and_non_member_is_denied(): void
    {
        $member = User::factory()->create();
        app(CoupleService::class)->createCouple($member);

        $this->actingAs($member);
        Livewire::test(GiftsIndex::class)->assertSet('tab', 'wishlist');

        $nonMember = User::factory()->create();
        $this->actingAs($nonMember)
            ->get(route('gifts.index'))
            ->assertRedirect(route('couple.create-or-join'));
    }

    public function test_user_can_create_or_update_own_wishlist_and_not_partner_wishlist(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        Wishlist::create([
            'couple_id' => $couple->id,
            'user_id' => $partner->id,
            'likes' => ['partner-only'],
            'share_with_partner' => true,
        ]);

        $this->actingAs($user);
        Livewire::test(WishlistForm::class)
            ->set('budgetMin', 20)
            ->set('budgetMax', 80)
            ->set('currency', 'usd')
            ->set('likes', 'coffee, books')
            ->set('dislikes', 'crowds')
            ->set('loveLanguages', 'quality time, words of affirmation')
            ->set('shareWithPartner', true)
            ->call('save');

        $this->assertDatabaseHas('wishlists', [
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'budget_min' => 20,
            'budget_max' => 80,
            'currency' => 'USD',
        ]);

        $partnerWishlist = Wishlist::query()
            ->where('couple_id', $couple->id)
            ->where('user_id', $partner->id)
            ->firstOrFail();

        $this->assertSame(['partner-only'], $partnerWishlist->likes);
    }

    public function test_gemini_success_stores_source_gemini(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);

        Wishlist::create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'budget_min' => 25,
            'budget_max' => 100,
            'currency' => 'USD',
            'likes' => ['hiking', 'coffee'],
            'dislikes' => ['seafood'],
            'share_with_partner' => true,
        ]);

        config()->set('services.gemini.key', 'test-gemini-key');
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode($this->validSuggestionCards()),
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $this->actingAs($user);
        Livewire::test(GiftSuggestionsComponent::class)
            ->call('generate')
            ->assertSet('source', 'gemini');

        $stored = GiftSuggestion::query()->latest('id')->firstOrFail();
        $this->assertSame('gemini', $stored->source);
    }

    public function test_gemini_failure_stores_source_fallback_and_returns_valid_structure(): void
    {
        $user = User::factory()->create();
        $couple = app(CoupleService::class)->createCouple($user);

        Wishlist::create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'likes' => ['music'],
            'dislikes' => ['seafood'],
            'share_with_partner' => true,
        ]);

        config()->set('services.gemini.key', 'test-gemini-key');
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response(['error' => 'timeout'], 503),
        ]);

        $this->actingAs($user);
        $component = Livewire::test(GiftSuggestionsComponent::class)
            ->call('generate')
            ->assertSet('source', 'fallback');

        $stored = GiftSuggestion::query()->latest('id')->firstOrFail();
        $this->assertSame('fallback', $stored->source);

        $cards = $component->get('cards');
        $this->assertGreaterThanOrEqual(10, count($cards));
        $this->assertArrayHasKey('title', $cards[0]);
        $this->assertArrayHasKey('category', $cards[0]);
        $this->assertArrayHasKey('description', $cards[0]);
        $this->assertArrayHasKey('why_it_fits', $cards[0]);
        Http::assertSentCount(3);
    }

    public function test_partner_wishlist_not_shared_is_excluded_from_input_snapshot(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        Wishlist::create([
            'couple_id' => $couple->id,
            'user_id' => $user->id,
            'likes' => ['tea'],
            'share_with_partner' => true,
        ]);

        Wishlist::create([
            'couple_id' => $couple->id,
            'user_id' => $partner->id,
            'likes' => ['private-like'],
            'share_with_partner' => false,
        ]);

        config()->set('services.gemini.key', 'test-gemini-key');
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode($this->validSuggestionCards()),
                        ]],
                    ],
                ]],
            ], 200),
        ]);

        $this->actingAs($user);
        Livewire::test(GiftSuggestionsComponent::class)->call('generate');

        $stored = GiftSuggestion::query()->latest('id')->firstOrFail();
        $snapshot = $stored->input_snapshot;

        $this->assertNull($snapshot['partner_wishlist']);
        $this->assertStringNotContainsString('private-like', json_encode($snapshot));
    }

    protected function validSuggestionCards(): array
    {
        $cards = [];

        for ($i = 1; $i <= 10; $i++) {
            $cards[] = [
                'title' => 'Idea '.$i,
                'category' => ['low-effort', 'experience', 'budget', 'thoughtful', 'physical'][($i - 1) % 5],
                'description' => 'Simple idea description '.$i,
                'why_it_fits' => 'Fits shared preferences.',
                'estimated_cost' => '$'.(10 + $i),
                'time_required' => '1 hour',
            ];
        }

        return $cards;
    }
}
