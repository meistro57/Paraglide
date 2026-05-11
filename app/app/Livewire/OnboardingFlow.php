<?php

namespace App\Livewire;

use App\Models\OnboardingProgress;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class OnboardingFlow extends Component
{
    public string $step = 'welcome';

    public string $password = '';

    public string $password_confirmation = '';

    public string $recoveryCode = '';

    public string $recoveryCodeConfirmation = '';

    public string $hardwareTier = 'tier_1';

    public string $backend = 'ollama';

    public string $openrouterApiKey = '';

    protected function rules(): array
    {
        return match ($this->step) {
            'password' => [
                'password' => ['required', 'string', 'min:12', 'confirmed'],
            ],
            'recovery' => [
                'recoveryCodeConfirmation' => ['required', 'same:recoveryCode'],
            ],
            'backend' => [
                'backend' => ['required', 'in:ollama,openrouter,skip'],
                'openrouterApiKey' => ['nullable', 'string'],
            ],
            default => [],
        };
    }

    public function mount(): void
    {
        $progress = OnboardingProgress::query()->latest('id')->first();

        if ($progress === null) {
            $this->recoveryCode = $this->generateRecoveryCode();

            return;
        }

        if ($progress->isCompleted()) {
            $this->redirectRoute('home', navigate: true);

            return;
        }

        $this->step = $progress->current_step;
        $state = $progress->state ?? [];
        $this->recoveryCode = $state['recovery_code'] ?? $this->generateRecoveryCode();
        $this->hardwareTier = $state['hardware_tier'] ?? 'tier_1';
        $this->backend = $state['backend'] ?? 'ollama';
    }

    public function nextStep(): void
    {
        $rules = $this->rules();

        if ($rules !== []) {
            $this->validate($rules);
        }

        if ($this->step === 'welcome') {
            $this->step = 'password';
        } elseif ($this->step === 'password') {
            $this->step = 'recovery';
        } elseif ($this->step === 'recovery') {
            $this->step = 'hardware';
        } elseif ($this->step === 'hardware') {
            $this->step = 'backend';
        } elseif ($this->step === 'backend') {
            $this->step = 'done';
        }

        $this->persist();
    }

    public function completeOnboarding(): void
    {
        if ($this->step !== 'done') {
            return;
        }

        $progress = $this->persist();
        $progress->forceFill([
            'completed_at' => now(),
        ])->save();

        $this->redirectRoute('home', navigate: true);
    }

    public function render()
    {
        return view('livewire.onboarding-flow');
    }

    private function persist(): OnboardingProgress
    {
        return OnboardingProgress::query()->updateOrCreate(
            ['id' => 1],
            [
                'current_step' => $this->step,
                'state' => [
                    'password_hash' => $this->password !== '' ? Hash::make($this->password) : null,
                    'recovery_code' => $this->recoveryCode,
                    'hardware_tier' => $this->hardwareTier,
                    'backend' => $this->backend,
                    'has_openrouter_key' => $this->openrouterApiKey !== '',
                ],
            ],
        );
    }

    private function generateRecoveryCode(): string
    {
        $pool = [
            'anchor', 'balance', 'cedar', 'delta', 'ember', 'fable', 'glide', 'harbor',
            'island', 'jovial', 'kernel', 'lumen', 'matrix', 'noble', 'orbit', 'prairie',
            'quantum', 'ripple', 'signal', 'thrive', 'uplift', 'vector', 'willow', 'zenith',
        ];

        shuffle($pool);

        return implode(' ', array_slice($pool, 0, 24));
    }
}
