<?php

use App\Http\Controllers\LockController;
use App\Models\OnboardingProgress;
use App\Services\Security\SessionUnlockManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    if (! Schema::hasTable('onboarding_progress')) {
        return redirect()->route('onboarding');
    }

    $progress = OnboardingProgress::query()->find(1);

    if (! $progress?->isCompleted()) {
        return redirect()->route('onboarding');
    }

    if (! app(SessionUnlockManager::class)->isUnlocked()) {
        return redirect()->route('lock.show');
    }

    return redirect()->route('home');
});

Route::view('/onboarding', 'onboarding')->name('onboarding');
Route::get('/lock', [LockController::class, 'show'])->name('lock.show');
Route::post('/unlock', [LockController::class, 'unlock'])->name('lock.unlock');
Route::post('/lock', [LockController::class, 'lock'])->name('lock.store');

Route::middleware('ensure.unlocked')->group(function (): void {
    Route::view('/home', 'home')->name('home');
    Route::view('/chat', 'chat')->name('chat');
});
