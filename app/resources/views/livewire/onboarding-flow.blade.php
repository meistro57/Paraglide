<section class="rounded-xl border border-slate-800 bg-slate-900 p-8 shadow-xl">
    <h1 class="text-2xl font-semibold">Paraglide Onboarding</h1>
    <p class="mt-2 text-sm uppercase tracking-wide text-slate-400">Current step: {{ $step }}</p>

    @if ($step === 'welcome')
        <p class="mt-6 text-slate-200">Welcome to Paraglide. This setup prepares secure local storage and your AI backend.</p>
    @endif

    @if ($step === 'password')
        <div class="mt-6 space-y-4">
            <label class="block text-sm font-medium">App password</label>
            <input type="password" wire:model.defer="password" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2">
            <label class="block text-sm font-medium">Confirm password</label>
            <input type="password" wire:model.defer="password_confirmation" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2">
            @error('password') <p class="text-sm text-red-400">{{ $message }}</p> @enderror
        </div>
    @endif

    @if ($step === 'recovery')
        <div class="mt-6 space-y-4">
            <p class="text-slate-200">Recovery code:</p>
            <div class="rounded border border-slate-700 bg-slate-950 p-3 text-sm">{{ $recoveryCode }}</div>
            <label class="block text-sm font-medium">Re-enter recovery code</label>
            <textarea wire:model.defer="recoveryCodeConfirmation" rows="3" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2"></textarea>
            @error('recoveryCodeConfirmation') <p class="text-sm text-red-400">{{ $message }}</p> @enderror
        </div>
    @endif

    @if ($step === 'hardware')
        <div class="mt-6 space-y-4">
            <label class="block text-sm font-medium">Recommended hardware tier</label>
            <select wire:model.defer="hardwareTier" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2">
                <option value="tier_1">Tier 1 - Lightweight</option>
                <option value="tier_2">Tier 2 - Standard</option>
                <option value="tier_3">Tier 3 - Power</option>
            </select>
        </div>
    @endif

    @if ($step === 'backend')
        <div class="mt-6 space-y-4">
            <label class="block text-sm font-medium">AI backend</label>
            <select wire:model.defer="backend" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2">
                <option value="ollama">Use local AI (Ollama)</option>
                <option value="openrouter">Use OpenRouter for testing</option>
                <option value="skip">Skip for now</option>
            </select>

            @if ($backend === 'openrouter')
                <label class="block text-sm font-medium">OpenRouter API Key</label>
                <input type="password" wire:model.defer="openrouterApiKey" class="w-full rounded border border-slate-700 bg-slate-950 px-3 py-2">
            @endif
        </div>
    @endif

    @if ($step === 'done')
        <p class="mt-6 text-slate-200">Onboarding complete. Continue to the home screen to chat with Lyra.</p>
    @endif

    <div class="mt-8 flex gap-3">
        @if ($step !== 'done')
            <button wire:click="nextStep" class="rounded bg-indigo-500 px-4 py-2 text-sm font-medium text-white">Continue</button>
        @else
            <button wire:click="completeOnboarding" class="rounded bg-emerald-500 px-4 py-2 text-sm font-medium text-white">Go to home</button>
        @endif
    </div>
</section>
