<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CoupleService;
use App\Services\RepairService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_repair_progress_persists_and_cannot_complete_without_required_inputs(): void
    {
        [$user, $partner, $session, $service] = $this->makeRepairSession();

        $service->updatePerspective($session, $user, 'my perspective');
        $service->updatePerspective($session->fresh(), $partner, 'partner perspective');
        $service->selectSharedGoals($session->fresh(), $user, ['communicate', 'listen', 'support']);

        $service->createAgreement($session->fresh(), $user, 'I will avoid interrupting');
        $service->createAgreement($session->fresh(), $partner, 'I will ask clarifying questions');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('All agreements must be acknowledged by your partner before completing.');
        $service->completeRepair($session->fresh(), $user);
    }

    public function test_repair_completion_grants_xp_only_once(): void
    {
        [$user, $partner, $session, $service] = $this->makeRepairSession();

        $service->updatePerspective($session, $user, 'my perspective');
        $service->updatePerspective($session->fresh(), $partner, 'partner perspective');
        $service->selectSharedGoals($session->fresh(), $user, ['communicate', 'listen', 'support']);

        $a = $service->createAgreement($session->fresh(), $user, 'I will avoid interrupting');
        $b = $service->createAgreement($session->fresh(), $partner, 'I will ask clarifying questions');

        $service->acknowledgeAgreement($a, $partner);
        $service->acknowledgeAgreement($b, $user);

        $service->completeRepair($session->fresh(), $user);

        $this->assertDatabaseHas('repair_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseCount('xp_events', 4); // 10 + 10 + 50 + 20
        $world = $session->couple->world()->firstOrFail();
        $meta = $world->cosmetics['__meta'] ?? [];
        $this->assertTrue(isset($meta['warmth_boost_until']) && Carbon::parse($meta['warmth_boost_until'])->isFuture());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This repair session is already completed.');
        $service->completeRepair($session->fresh(), $user);
    }

    public function test_agreement_acknowledgement_xp_is_idempotent(): void
    {
        [$user, $partner, $session, $service] = $this->makeRepairSession();
        $agreement = $service->createAgreement($session, $user, 'agreement text');

        $service->acknowledgeAgreement($agreement, $partner);
        $service->acknowledgeAgreement($agreement->fresh(), $partner);

        $this->assertDatabaseCount('xp_events', 1);
    }

    protected function makeRepairSession(): array
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->createCouple($user);
        $coupleService->joinCouple($partner, $couple->invite_code);

        $service = app(RepairService::class);
        $session = $service->initiateRepair($couple, $user, 'Conflicting communication');
        $service->joinRepair($session, $partner);

        return [$user, $partner, $session->fresh(), $service];
    }
}
