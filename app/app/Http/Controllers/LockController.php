<?php

namespace App\Http\Controllers;

use App\Models\OnboardingProgress;
use App\Services\Audit\AuditLogger;
use App\Services\Security\SessionUnlockManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LockController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (! Schema::hasTable('onboarding_progress')) {
            return redirect()->route('onboarding');
        }

        $progress = OnboardingProgress::query()->find(1);

        if (! $progress?->isCompleted()) {
            return redirect()->route('onboarding');
        }

        return view('lock');
    }

    public function unlock(Request $request, SessionUnlockManager $unlockManager, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $progress = OnboardingProgress::query()->find(1);
        $passwordHash = $progress?->state['password_hash'] ?? null;

        if (! is_string($passwordHash) || ! Hash::check($validated['password'], $passwordHash)) {
            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        $unlockManager->unlock();

        $auditLogger->log('user_unlocked_app', 'onboarding_progress', $progress?->id, []);

        return redirect()->intended(route('home'));
    }

    public function lock(SessionUnlockManager $unlockManager, AuditLogger $auditLogger): RedirectResponse
    {
        $progress = OnboardingProgress::query()->find(1);

        $unlockManager->lock();

        $auditLogger->log('user_locked_app', 'onboarding_progress', $progress?->id, []);

        return redirect()->route('lock.show');
    }
}
