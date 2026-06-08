<div class="space-y-16 pb-24">
    <!-- Dynamic Greeting Section -->
    <section class="mt-8 px-margin" x-data="{ hour: new Date().getHours() }">
        <div class="relative inline-block">
            <h2 class="font-headline-xl text-headline-xl text-on-background wavy-underline mb-2">
                <span x-text="hour < 12 ? '{{ __('Good morning') }}' : (hour < 18 ? '{{ __('Good afternoon') }}' : '{{ __('Good evening') }}')">{{ __('Good day') }}</span>{{ Auth::check() ? ', ' . Auth::user()->name : '' }}!
            </h2>
            <span
                class="material-symbols-outlined absolute -right-12 top-0 text-primary-container text-4xl floating-doodle"
                style="font-variation-settings: 'FILL' 1;">music_note</span>
        </div>
    </section>

    <!-- Recently Played Section -->
    @if(count($recentlyPlayed) > 0)
        <section class="mt-12 px-margin"
            x-data="{ tracks: {{ collect($recentlyPlayed->take(10))->map(fn($p) => ['id' => $p->yt_track_id, 'title' => $p->track_title, 'artist' => $p->artist_name, 'thumbnail' => $p->thumbnail_url])->toJson() }} }">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-headline-md text-headline-md text-on-surface">{{ __('Recently Played') }}</h3>
            </div>

            <div class="flex gap-6 overflow-x-auto pb-8 custom-scrollbar">
                @foreach($recentlyPlayed->take(10) as $index => $play)
                    @php
                        $rotation = ($index % 2 == 0) ? '-1.5deg' : '1.2deg';
                        if ($index % 3 == 0)
                            $rotation = '-1deg';
                        if ($index % 4 == 0)
                            $rotation = '2deg';
                    @endphp
                    <!-- Track Card -->
                    <div class="flex-none w-64 sketchy-border p-4 bg-surface block-shadow group cursor-pointer"
                        style="transform: rotate({{ $rotation }});"
                        x-on:click="$dispatch('play-queue', { queue: tracks, index: {{ $index }} })">
                        <div class="relative aspect-square mb-4 overflow-hidden rounded">
                            <img alt="{{ $play->track_title }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                src="{{ $play->thumbnail_url }}">
                            <div
                                class="absolute inset-0 bg-primary-container/20 opacity-0 group-hover:opacity-100 backdrop-blur-[2px] transition-opacity flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-6xl"
                                    style="font-variation-settings: 'FILL' 1;">play_circle</span>
                            </div>
                        </div>
                        <h4 class="font-headline-md text-[18px] truncate" title="{{ $play->track_title }}">
                            {{ $play->track_title }}</h4>
                        <p class="font-label-sm text-on-surface-variant text-[12px] truncate">{{ $play->artist_name }}</p>

                        <div class="absolute bottom-2 right-2 z-10" x-data="{ open: false }">
                            <button @click.prevent.stop="open = !open" @click.away="open = false"
                                class="material-symbols-outlined text-on-surface-variant hover:text-primary bg-surface/80 rounded-full p-1 border border-on-background shadow-[2px_2px_0px_0px_rgba(28,27,27,1)] active:scale-90 transition-all">
                                more_vert
                            </button>
                            <div x-show="open" style="display: none;"
                                class="absolute bottom-full right-0 mb-2 w-48 bg-surface border-2 border-on-background shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] rotate-[1deg] p-2">
                                <button
                                    @click.prevent.stop="@auth tracks[{{ $index }}].isSaved = !tracks[{{ $index }}].isSaved; $dispatch('saveTrackToLibrary', { track: tracks[{{ $index }}] }); @else showLoginModal = true; @endauth open = false;"
                                    class="w-full text-left p-2 hover:bg-secondary-container/20 font-body-md border-b border-dashed border-outline-variant flex items-center gap-2 text-[14px]">
                                    <span class="material-symbols-outlined text-sm"
                                        :style="`font-variation-settings: 'FILL' ${tracks[{{ $index }}].isSaved ? 1 : 0};`"
                                        :class="tracks[{{ $index }}].isSaved ? 'text-error' : ''">favorite</span>
                                    <span x-text="tracks[{{ $index }}].isSaved ? '{{ __('Remove from Favorite') }}' : '{{ __('Add to Favorite') }}'"></span>
                                </button>
                                <button
                                    @click.prevent.stop="navigator.clipboard.writeText('https://music.youtube.com/watch?v={{ $play->yt_track_id }}'); alert('{{ __('Link bagikan disalin!') }}'); open = false"
                                    class="w-full text-left p-2 hover:bg-secondary-container/20 font-body-md flex items-center gap-2 text-[14px]">
                                    <span class="material-symbols-outlined text-sm">share</span>
                                    {{ __('Share Link') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @else
        <!-- Empty State Section: Your Daily Mix -->
        <section class="mt-16 px-margin">
            <div
                class="sketchy-border p-12 bg-surface-container border-dashed flex flex-col items-center justify-center text-center rotate-[0.5deg]">
                <div class="mb-6 relative">
                    <span class="material-symbols-outlined text-8xl text-outline-variant floating-doodle"
                        style="font-variation-settings: 'FILL' 1;">music_off</span>
                    <div
                        class="absolute -top-4 -right-4 bg-primary-container p-2 rounded-full border-2 border-on-background rotate-12">
                        <span class="material-symbols-outlined text-white text-2xl"
                            style="font-variation-settings: 'FILL' 1;">bedtime</span>
                    </div>
                </div>
                <h3 class="font-headline-md text-headline-md text-on-surface mb-2">{{ __('Your Daily Mix') }}</h3>
                <p class="font-body-md text-on-surface-variant max-w-sm mb-6">{{ __('Your personalized collection is currently sleeping. Start searching and listening to fill this space!') }}</p>
            </div>
        </section>
    @endif

    <!-- Dynamic Sections from Spotify API (New Releases, Featured Playlists, etc) -->
    @foreach($sections as $sectionIndex => $section)
        @if(count($section['contents']) > 0)
            <section class="mt-16 px-margin" x-data="{ tracks: {{ collect($section['contents'])->toJson() }} }">
                <h3 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center">
                    @if(str_contains(strtolower($section['title']), 'shorts'))
                        <span class="material-symbols-outlined text-[#ff0000] text-4xl mr-2"
                            style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                    @endif
                    {{ $section['title'] }}
                </h3>

                <div class="flex overflow-x-auto gap-8 pb-8 custom-scrollbar">
                    @foreach($section['contents'] as $index => $track)
                        @php
                            $rotation = ($index % 2 == 0) ? '-1deg' : '1.5deg';
                            if ($index % 3 == 0)
                                $rotation = '-2deg';
                            if ($index % 4 == 0)
                                $rotation = '1.2deg';
                            $isShort = str_contains(strtolower($section['title']), 'shorts');
                        @endphp
                        <!-- Carousel Card -->
                        <div class="flex-none {{ $isShort ? 'w-48' : 'w-72' }} relative">
                            <div class="sketchy-border p-4 bg-surface block-shadow group cursor-pointer transition-all hover:rotate-[0deg]"
                                style="transform: rotate({{ $rotation }});" @if(isset($track['type']) && $track['type'] !== 'video')
                                x-on:click="Livewire.navigate('{{ route('playlist.show', $track['id']) }}')" @else
                                x-on:click="$dispatch('play-queue', { queue: tracks, index: {{ $index }} })" @endif>
                                <div
                                    class="relative {{ $isShort ? 'aspect-[9/16]' : 'aspect-square' }} mb-4 overflow-hidden rounded">
                                    <img alt="{{ $track['title'] }}"
                                        class="w-full h-full object-cover transition-transform group-hover:scale-110"
                                        src="{{ $track['thumbnail'] }}">
                                    <div
                                        class="absolute inset-0 opacity-0 group-hover:opacity-100 bg-background/30 backdrop-blur-sm flex items-center justify-center transition-all">
                                        <span class="material-symbols-outlined text-primary-container text-7xl"
                                            style="font-variation-settings: 'FILL' 1;">play_circle</span>
                                    </div>
                                </div>
                                <h4 class="font-headline-md text-[20px] line-clamp-2 leading-tight" title="{{ $track['title'] }}">
                                    {{ $track['title'] }}</h4>
                                <p class="font-label-sm text-on-surface-variant truncate mt-1">{{ $track['artist'] }}</p>

                                <!-- Sketchy Dropdown Menu -->
                                <div class="absolute top-2 right-2 z-10" x-data="{ open: false }">
                                    <button @click.prevent.stop="open = !open" @click.away="open = false"
                                        class="text-white bg-on-background/50 rounded-full p-1 hover:bg-on-background transition-colors shadow-sm">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <div x-show="open" style="display: none;"
                                        class="absolute top-12 right-[-20px] w-48 bg-surface border-2 border-on-background shadow-[6px_6px_0px_0px_rgba(28,27,27,1)] z-10 rotate-[-1deg] p-2">
                                        <button
                                            @click.prevent.stop="@auth tracks[{{ $index }}].isSaved = !tracks[{{ $index }}].isSaved; $dispatch('saveTrackToLibrary', { track: tracks[{{ $index }}] }); @else showLoginModal = true; @endauth open = false;"
                                            class="w-full text-left p-2 hover:bg-secondary-container/20 font-body-md border-b border-dashed border-outline-variant flex items-center gap-2 text-[14px]">
                                            <span class="material-symbols-outlined text-sm"
                                                :style="`font-variation-settings: 'FILL' ${tracks[{{ $index }}].isSaved ? 1 : 0};`"
                                                :class="tracks[{{ $index }}].isSaved ? 'text-error' : ''">favorite</span>
                                            <span x-text="tracks[{{ $index }}].isSaved ? '{{ __('Remove from Favorite') }}' : '{{ __('Add to Favorite') }}'"></span>
                                        </button>
                                        <button
                                            @click.prevent.stop="navigator.clipboard.writeText('{{ isset($track['type']) && $track['type'] !== 'video' ? url('/playlist/' . $track['id']) : 'https://music.youtube.com/watch?v=' . $track['id'] }}'); alert('{{ __('Link bagikan disalin!') }}'); open = false"
                                            class="w-full text-left p-2 hover:bg-secondary-container/20 font-body-md flex items-center gap-2 text-[14px]">
                                            <span class="material-symbols-outlined text-sm">share</span>
                                            {{ __('Share Link') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
</div>