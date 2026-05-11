<?php

namespace App\Services\Security;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Session\Session;

class SessionUnlockManager
{
    private const SESSION_KEY = 'paraglide.unlocked_at';

    public function __construct(private readonly Session $session)
    {
    }

    public function isUnlocked(): bool
    {
        $value = $this->session->get(self::SESSION_KEY);

        if (! is_int($value)) {
            return false;
        }

        $unlockedAt = CarbonImmutable::createFromTimestamp($value);

        return $unlockedAt->addMinutes($this->idleTimeoutMinutes())->isFuture();
    }

    public function unlock(): void
    {
        $this->session->put(self::SESSION_KEY, now()->timestamp);
    }

    public function touch(): void
    {
        if ($this->isUnlocked()) {
            $this->unlock();
        }
    }

    public function lock(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    private function idleTimeoutMinutes(): int
    {
        return (int) config('paraglide.unlock_idle_timeout_minutes', 15);
    }
}
