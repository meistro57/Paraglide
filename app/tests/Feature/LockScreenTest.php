<?php

namespace Tests\Feature;

use App\Models\OnboardingProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LockScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_requires_unlock_after_onboarding_completion(): void
    {
        $this->seedCompletedOnboarding();

        $this->get('/home')->assertRedirectToRoute('lock.show');
    }

    public function test_unlock_with_valid_password_sets_session_and_allows_home(): void
    {
        $this->withoutVite();
        $this->seedCompletedOnboarding('correct-horse-battery');

        $this->get('/home')->assertRedirectToRoute('lock.show');

        $this->post('/unlock', [
            'password' => 'correct-horse-battery',
        ])->assertRedirect('/home');

        $this->get('/home')->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user_unlocked_app',
            'resource_type' => 'onboarding_progress',
        ]);
    }

    public function test_idle_timeout_redirects_back_to_lock_screen(): void
    {
        config()->set('paraglide.unlock_idle_timeout_minutes', 1);

        $this->seedCompletedOnboarding();

        $this->withSession([
            'paraglide.unlocked_at' => now()->subMinutes(2)->timestamp,
        ])->get('/home')->assertRedirectToRoute('lock.show');
    }

    public function test_manual_lock_clears_unlock_state(): void
    {
        $this->seedCompletedOnboarding();

        $this->withSession([
            'paraglide.unlocked_at' => now()->timestamp,
        ])->post('/lock')->assertRedirectToRoute('lock.show');

        $this->get('/chat')->assertRedirectToRoute('lock.show');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user_locked_app',
            'resource_type' => 'onboarding_progress',
        ]);
    }

    private function seedCompletedOnboarding(string $password = 'super-secure-password'): void
    {
        OnboardingProgress::query()->create([
            'id' => 1,
            'current_step' => 'done',
            'state' => [
                'password_hash' => Hash::make($password),
            ],
            'completed_at' => now(),
        ]);
    }
}
