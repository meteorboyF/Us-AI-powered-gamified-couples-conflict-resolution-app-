<?php

namespace Tests\Feature\UI\Gifts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\UI\Gifts\Concerns\CreatesGiftsUiContext;
use Tests\TestCase;

class GiftsUiPageTest extends TestCase
{
    use CreatesGiftsUiContext;
    use RefreshDatabase;

    public function test_user_with_current_couple_can_view_gifts_ui_page(): void
    {
        $this->enableGiftsFeature();

        $user = User::factory()->create();
        $this->createCoupleWithPartner($user);

        $this->actingAs($user)
            ->get('/gifts-ui')
            ->assertOk()
            ->assertSee('Gift Suggestions');
    }

    public function test_user_without_current_couple_sees_no_couple_selected_state(): void
    {
        $this->enableGiftsFeature();
        $user = User::factory()->create(['current_couple_id' => null]);

        $this->actingAs($user)
            ->get('/gifts-ui')
            ->assertOk()
            ->assertSee('No couple selected');
    }
}
