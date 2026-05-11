<section class="space-y-6">
    <header class="flex items-center justify-between gap-3">
        <h1 class="text-3xl font-semibold">Conversation with Lyra</h1>
        <span class="rounded-full bg-slate-800 px-3 py-1 text-xs font-medium text-slate-200">{{ $backendBadge }}</span>
    </header>

    <div class="h-[28rem] space-y-3 overflow-y-auto rounded-xl border border-slate-800 bg-slate-900 p-4">
        @forelse ($messages as $item)
            <article @class([
                'max-w-[85%] rounded-lg px-4 py-3 text-sm leading-relaxed',
                'ml-auto bg-indigo-600 text-indigo-50' => $item['role'] === 'user',
                'mr-auto bg-slate-800 text-slate-100' => $item['role'] !== 'user',
            ])>
                <p>{{ $item['content'] }}</p>
            </article>
        @empty
            <p class="text-sm text-slate-400">Say hi to Lyra to begin your first conversation.</p>
        @endforelse

        <article class="mr-auto max-w-[85%] rounded-lg bg-slate-800 px-4 py-3 text-sm text-slate-100" wire:stream="assistant_stream"></article>
    </div>

    <form wire:submit="sendMessage" class="space-y-3">
        <label for="message" class="block text-sm font-medium text-slate-200">Message</label>
        <textarea
            id="message"
            wire:model="message"
            rows="4"
            class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 outline-none ring-indigo-500/40 placeholder:text-slate-500 focus:ring"
            placeholder="Ask Lyra what to work on next..."
            x-on:keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); $wire.sendMessage(); }"
        ></textarea>
        @error('message')
            <p class="text-sm text-rose-400">{{ $message }}</p>
        @enderror
        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400">Press Enter to send, Shift+Enter for a new line.</p>
            <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-400">Send</button>
        </div>
    </form>
</section>
