<?php

namespace Tests\Feature;

use App\Livewire\OnboardingFlow;
use App\Models\OnboardingProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_onboarding_until_completed(): void
    {
        $this->get('/')->assertRedirectToRoute('onboarding');

        OnboardingProgress::query()->create([
            'current_step' => 'done',
            'state' => [],
            'completed_at' => now(),
        ]);

        $this->get('/')->assertRedirectToRoute('home');
    }

    public function test_password_step_requires_minimum_length_and_confirmation(): void
    {
        Livewire::test(OnboardingFlow::class)
            ->set('step', 'password')
            ->set('password', 'short')
            ->set('password_confirmation', 'mismatch')
            ->call('nextStep')
            ->assertHasErrors(['password']);
    }

    public function test_onboarding_persists_progress_on_step_change(): void
    {
        Livewire::test(OnboardingFlow::class)
            ->call('nextStep');

        $progress = OnboardingProgress::query()->find(1);

        $this->assertNotNull($progress);
        $this->assertSame('password', $progress->current_step);
        $this->assertIsArray($progress->state);
        $this->assertArrayHasKey('recovery_code', $progress->state);
    }

    public function test_complete_onboarding_sets_completed_at(): void
    {
        Livewire::test(OnboardingFlow::class)
            ->set('step', 'done')
            ->call('completeOnboarding')
            ->assertRedirectToRoute('home');

        $progress = OnboardingProgress::query()->find(1);

        $this->assertNotNull($progress?->completed_at);
    }
}
