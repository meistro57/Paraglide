<?php

use App\Models\OnboardingProgress;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    if (! Schema::hasTable('onboarding_progress')) {
        return redirect()->route('onboarding');
    }

    $progress = OnboardingProgress::query()->find(1);

    if ($progress?->isCompleted()) {
        return redirect()->route('home');
    }

    return redirect()->route('onboarding');
});

Route::view('/onboarding', 'onboarding')->name('onboarding');
Route::view('/home', 'home')->name('home');
