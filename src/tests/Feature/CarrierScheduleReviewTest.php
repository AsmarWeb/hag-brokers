<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\CarrierInvitation;
use App\Models\CarrierScheduleSubmission;
use App\Models\FreightSailing;
use App\Models\Port;
use App\Models\Role;
use App\Models\User;
use App\Models\Vessel;
use App\Services\Freight\SailingImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class CarrierScheduleReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_carrier_submits_and_admin_publishes_or_returns_scoped_sailing_changes(): void
    {
        $this->freezeTime();
        $carrier = Carrier::create(['name' => 'Partner', 'code' => 'PARTNER', 'is_active' => true]);
        $other = Carrier::create(['name' => 'Other', 'code' => 'OTHER', 'is_active' => true]);
        Vessel::create(['name' => 'Partner vessel', 'imo' => '1234567', 'carrier_id' => $carrier->id]);
        Vessel::create(['name' => 'Other vessel', 'imo' => '7654321', 'carrier_id' => $other->id]);
        Port::create(['name' => 'Jebel Ali', 'unlocode' => 'AEJEA', 'country_code' => 'AE', 'is_active' => true]);
        Port::create(['name' => 'Dammam', 'unlocode' => 'SADMM', 'country_code' => 'SA', 'is_active' => true]);
        $partner = User::factory()->create();
        CarrierInvitation::create([
            'carrier_id' => $carrier->id, 'email' => $partner->email,
            'token_hash' => hash('sha256', 'schedule-fixture'), 'expires_at' => now()->addDay(),
        ])->forceFill(['user_id' => $partner->id, 'accepted_at' => now()])->save();
        $admin = User::factory()->create();
        $admin->addRole(Role::firstOrCreate(['name' => 'admin']));

        $this->actingAs(User::factory()->create())->get(route('carrier.schedules.index'))->assertForbidden();
        $this->actingAs($partner)->get(route('carrier.schedules.index'))->assertOk();
        $preview = route('carrier.schedules.preview');
        $submit = route('carrier.schedules.submit');
        $this->postJson($preview, ['schedule' => $this->csv(3, 'OTHER', '7654321')])->assertUnprocessable();
        $this->postJson($preview, ['schedule' => $this->csv(3, 'PARTNER', '7654321')])->assertUnprocessable();
        $previewResponse = $this->post($preview, ['schedule' => $this->csv(3)])->assertOk()->assertSee('CARRIER-VOYAGE');
        $this->assertDatabaseCount('carrier_schedule_submissions', 0);
        $this->assertDatabaseCount('freight_sailings', 0);
        $token = $previewResponse->viewData('token');
        $this->post($submit, ['token' => $token])->assertRedirect(route('carrier.schedules.index'));
        $this->postJson($submit, ['token' => $token])->assertUnprocessable();
        $submission = CarrierScheduleSubmission::sole();
        $this->assertEquals($carrier->id, $submission->carrier_id);
        $this->assertDatabaseCount('freight_sailings', 0);
        $review = route('admin.freight-sailings.carrier-review.store', $submission);
        $this->patch($review, ['decision' => 'publish'])->assertForbidden();
        $this->actingAs($admin)->get(route('admin.freight-sailings.carrier-review', $submission))
            ->assertOk()->assertSee('CARRIER-VOYAGE');
        $this->patch($review, ['decision' => 'publish'])->assertRedirect();
        $this->assertSame('published', $submission->fresh()->status);
        $sailing = FreightSailing::sole();
        $this->getJson('/api/v1/freight/sailings')->assertOk()->assertJsonPath('data.0.voyage_number', 'CARRIER-VOYAGE');

        foreach ([4, 5] as $days) {
            $response = $this->actingAs($partner)->post($preview, ['schedule' => $this->csv($days)])
                ->assertOk()->assertViewHas('plan.0.action', 'update');
            $this->post($submit, ['token' => $response->viewData('token')])->assertRedirect();
        }
        $updates = CarrierScheduleSubmission::where('status', 'pending')->oldest('id')->get();
        $this->actingAs($admin)->patch(route('admin.freight-sailings.carrier-review.store', $updates[0]),
            ['decision' => 'publish'])->assertRedirect();
        $this->assertDatabaseCount('freight_sailings', 1);
        $this->assertEquals($sailing->departure_at->addDay()->timestamp, $sailing->fresh()->departure_at->timestamp);
        $review = route('admin.freight-sailings.carrier-review.store', $updates[1]);
        $this->patchJson($review, ['decision' => 'publish'])->assertUnprocessable();
        $this->patch($review, ['decision' => 'return', 'review_note' => 'Preview the latest departure again.'])->assertRedirect();
        $this->actingAs($partner)->get(route('carrier.schedules.index'))->assertOk()->assertSee('Preview the latest departure again.');
    }

    private function csv(int $days, string $carrier = 'PARTNER', string $imo = '1234567'): UploadedFile
    {
        $departure = now('Asia/Dubai')->addDays($days);

        return UploadedFile::fake()->createWithContent('schedule.csv', implode(',', SailingImport::HEADERS)."\n".implode(',', [
            $carrier, $imo, 'AEJEA', 'SADMM', 'CARRIER-VOYAGE',
            $departure->format('Y-m-d H:i'),
            $departure->copy()->addDay()->format('Y-m-d H:i'),
            $departure->copy()->subDay()->format('Y-m-d H:i'),
            '20ft',
        ])."\n");
    }
}
