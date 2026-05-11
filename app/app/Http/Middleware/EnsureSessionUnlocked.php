<?php

namespace App\Http\Middleware;

use App\Models\OnboardingProgress;
use App\Services\Security\SessionUnlockManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionUnlocked
{
    public function __construct(private readonly SessionUnlockManager $unlockManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! Schema::hasTable('onboarding_progress')) {
            return $next($request);
        }

        $progress = OnboardingProgress::query()->find(1);

        if (! $progress?->isCompleted()) {
            return $next($request);
        }

        if (! $this->unlockManager->isUnlocked()) {
            $this->unlockManager->lock();

            return redirect()->guest(route('lock.show'));
        }

        $this->unlockManager->touch();

        return $next($request);
    }
}
