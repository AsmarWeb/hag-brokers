<?php

namespace Tests\Feature;

use App\Models\Port;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaritimeBoardWizardFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Port::query()->create([
            'name' => 'Jebel Ali Port',
            'country' => 'United Arab Emirates',
            'country_code' => 'AE',
            'port_code' => 'JEA',
            'unlocode' => 'AEJEA',
            'timezone' => 'Asia/Dubai',
            'latitude' => 25.0118,
            'longitude' => 55.0617,
            'is_gulf' => true,
            'is_uae' => true,
            'is_active' => true,
        ]);

        Port::query()->create([
            'name' => 'Umm Qasr Port',
            'country' => 'Iraq',
            'country_code' => 'IQ',
            'port_code' => 'UQR',
            'unlocode' => 'IQUQR',
            'timezone' => 'Asia/Baghdad',
            'latitude' => 30.02737,
            'longitude' => 47.97300,
            'is_gulf' => true,
            'is_uae' => false,
            'is_active' => true,
        ]);
    }

    public function test_cargo_form_uses_the_five_stage_wizard_and_cargo_choice_cards(): void
    {
        $this->get(route('maritime-board.submit.create', 'cargo'))
            ->assertOk()
            ->assertSee('maritimeBoardWizard', false)
            ->assertSee('trade-wizard-nav', false)
            ->assertSee(__('maritime_board.form_steps.type'))
            ->assertSee('name="cargo_type"', false)
            ->assertSee('name="quantity"', false)
            ->assertSee('name="loading_port_id"', false)
            ->assertSee('marine-port-picker', false)
            ->assertSee(__('maritime_board.port_search_placeholder'))
            ->assertSee('name="terms_accepted"', false);
    }

    public function test_cargo_form_explains_port_selection_and_cargo_particulars(): void
    {
        $this->get(route('maritime-board.submit.create', 'cargo'))
            ->assertOk()
            ->assertSee(__('maritime_board.description_checklist_title'))
            ->assertSee(__('maritime_board.port_selection_note'))
            ->assertSee(__('maritime_board.port_filter_uae'))
            ->assertSee(__('maritime_board.port_filter_gulf'));
    }

    public function test_vessel_form_explains_dwt_and_vessel_position(): void
    {
        $this->get(route('maritime-board.submit.create', 'vessel'))
            ->assertOk()
            ->assertSee(__('maritime_board.vessel_dwt_explained'))
            ->assertSee(__('maritime_board.current_position_example'));
    }

    public function test_vessel_form_uses_the_five_stage_wizard_and_vessel_choice_cards(): void
    {
        $this->get(route('maritime-board.submit.create', 'vessel'))
            ->assertOk()
            ->assertSee('maritimeBoardWizard', false)
            ->assertSee('trade-wizard-nav', false)
            ->assertSee(__('maritime_board.form_steps.type'))
            ->assertSee('name="vessel_type"', false)
            ->assertSee('name="dwt_capacity"', false)
            ->assertSee('name="current_position"', false)
            ->assertSee('name="current_port_id"', false)
            ->assertSee('marine-port-picker', false)
            ->assertSee('name="terms_accepted"', false);
    }
}
