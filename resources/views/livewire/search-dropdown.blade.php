<div class="relative w-full max-w-xl mx-auto" x-data="{ open: false }">
    <div class="relative flex items-center">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-text-main/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <input 
            wire:model.live.debounce.300ms="query" 
            @focus="open = true"
            @click.away="open = false"
            @keydown.escape="open = false"
            type="text" 
            placeholder="Search songs, artists..." 
            class="block w-full pl-12 pr-4 py-3 bg-surface border-[2px] border-text-main rounded-xl shadow-[4px_4px_0px_#111827] focus:outline-none focus:ring-0 focus:border-primary focus:shadow-[6px_6px_0px_#49B6E5] text-text-main font-bold placeholder-text-main/50 transition-all"
        >
        <div wire:loading wire:target="query" class="absolute inset-y-0 right-0 pr-4 flex items-center">
            <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    @if(strlen($query) > 0)
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         class="fixed md:absolute z-50 left-4 right-4 md:left-auto md:right-auto md:w-full mt-2 bg-surface border-[2px] border-text-main rounded-xl shadow-[6px_6px_0px_#111827] overflow-hidden max-h-96 overflow-y-auto top-[70px] md:top-auto"
         x-data="{ tracks: {{ collect($results)->filter(fn($t) => isset($t['type']) && $t['type'] === 'video')->map(fn($t) => ['id' => $t['id'], 'title' => $t['title'], 'artist' => $t['artist'], 'thumbnail' => $t['thumbnail']])->values()->toJson() }} }"
         style="display: none;">
        
        @if(count($results) === 0)
            <div class="p-4 text-center text-text-main/70 font-mono text-sm" wire:loading.remove wire:target="query">
                No results found for "{{ $query }}"
            </div>
        @else
            <div class="p-2 space-y-1" wire:loading.remove wire:target="query">
                @foreach($results as $index => $track)
                    <div class="flex items-center p-2 hover:bg-primary/10 rounded-lg cursor-pointer transition-colors group relative"
                         @if(isset($track['type']) && $track['type'] !== 'video')
                            wire:navigate href="{{ route('playlist.show', $track['id']) }}"
                         @else
                            x-on:click="$dispatch('play-queue', { queue: tracks, index: {{ collect($results)->where('type', 'video')->keys()->search($index) !== false ? collect($results)->where('type', 'video')->keys()->search($index) : 0 }} }); open = false; $wire.set('query', '')"
                         @endif>
                         
                        <!-- Label moved to end of flex container -->

                        <div class="relative h-12 w-12 shrink-0 overflow-hidden bg-surface border border-text-main mr-3 {{ isset($track['type']) && in_array($track['type'], ['artist', 'profile']) ? 'rounded-full' : 'rounded-md' }}">
                            <img src="{{ $track['thumbnail'] }}" alt="{{ $track['title'] }}" class="object-cover w-full h-full">
                            <div class="absolute inset-0 bg-primary/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                @if(isset($track['type']) && $track['type'] !== 'video')
                                <svg class="w-6 h-6 text-surface" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                @else
                                <svg class="w-6 h-6 text-surface" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-sm text-text-main truncate">{{ is_array($track['title'] ?? null) ? implode(', ', $track['title']) : ($track['title'] ?? 'Unknown Title') }}</h4>
                            @if(!isset($track['type']) || $track['type'] !== 'artist')
                                <p class="text-xs font-mono text-text-main/70 truncate">{{ is_array($track['artist'] ?? null) ? (isset($track['artist'][0]['name']) ? collect($track['artist'])->pluck('name')->implode(', ') : implode(', ', $track['artist'])) : ($track['artist'] ?? 'Unknown Artist') }}</p>
                            @endif
                        </div>
                        
                        @if(isset($track['type']) && $track['type'] !== 'video')
                        <div class="shrink-0 ml-2 px-2 py-0.5 bg-primary text-surface font-black text-[10px] uppercase tracking-wider rounded border border-text-main shadow-[1px_1px_0_#111827]">
                            {{ $track['type'] }}
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif
</div>
