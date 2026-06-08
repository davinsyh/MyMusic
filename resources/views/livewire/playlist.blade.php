<div class="max-w-6xl mx-auto px-4" x-data="{ tracks: {{ collect($playlistDetails['tracks'])->toJson() }} }">
    <!-- Playlist Header Section -->
    <div class="flex flex-col md:flex-row gap-8 md:items-end mb-12">
        <div class="w-64 h-64 mx-auto md:mx-0 sketchy-border bg-surface shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] rotate-[-2deg] shrink-0 overflow-hidden">
            @if($playlistId === 'favorites')
                <div class="w-full h-full bg-primary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-[120px]" style="font-variation-settings: 'FILL' 1;">favorite</span>
                </div>
            @elseif($playlistId === 'history')
                <div class="w-full h-full bg-tertiary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-tertiary-container text-[120px]">history</span>
                </div>
            @elseif($playlistDetails['thumbnails'])
                <img alt="Cover" class="w-full h-full object-cover" src="{{ $playlistDetails['thumbnails'] }}">
            @else
                <div class="w-full h-full flex items-center justify-center bg-primary/20 text-primary">
                    <span class="material-symbols-outlined text-[120px]">library_music</span>
                </div>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-label-sm text-primary uppercase tracking-widest mb-2">{{ $type }}</p>
            <h2 class="font-headline-xl text-headline-xl text-on-background mb-4 truncate">{{ $playlistDetails['title'] }}</h2>
            @if($playlistDetails['description'])
                <p class="font-body-md text-on-surface-variant mb-4 line-clamp-2 max-w-3xl">{{ $playlistDetails['description'] }}</p>
            @endif
            <div class="flex flex-wrap items-center gap-4 font-label-sm text-on-surface mb-6">
                <span class="font-bold">{{ $playlistDetails['author'] }}</span>
                <span class="w-1 h-1 bg-outline-variant rounded-full"></span>
                <span x-text="tracks.length + ' tracks'">{{ $playlistDetails['trackCount'] }} tracks</span>
            </div>
            <div class="flex flex-wrap gap-4 mt-6">
                <button x-on:click="$dispatch('play-queue', { queue: tracks, index: 0, context: { title: {{ \Illuminate\Support\Js::from($playlistDetails['title']) }}, type: {{ \Illuminate\Support\Js::from($type) }}, author: {{ \Illuminate\Support\Js::from($playlistDetails['author']) }} } })" class="bg-primary text-white font-headline-md px-8 py-3 block-shadow rotate-[-1deg] hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                    Play All
                </button>
                @if(!in_array($playlistId, ['favorites', 'history']))
                <button wire:click="toggleFavorite" class="bg-transparent text-on-background border-2 border-on-background font-headline-md px-8 py-3 shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] rotate-[-2deg] hover:scale-105 active:scale-95 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined {{ $isFavorited ? 'text-primary' : '' }}" style="font-variation-settings: 'FILL' {{ $isFavorited ? '1' : '0' }};">favorite</span>
                    Favorite
                </button>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Track List Section -->
    <div class="sketchy-border bg-surface shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] overflow-hidden mb-12">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="border-b-2 border-on-background bg-surface-container-low">
                        <th class="p-4 font-label-sm w-16 text-center">#</th>
                        <th class="p-4 font-label-sm">Title</th>
                        <th class="p-4 font-label-sm hidden md:table-cell w-1/3">Artist</th>
                        <th class="p-4 font-label-sm w-24 text-right">Time</th>
                        <th class="p-4 w-24"></th>
                    </tr>
                </thead>
                <tbody class="font-body-md">
                    <template x-for="(track, index) in tracks" :key="track.videoId || track.id">
                    <tr class="group hover:bg-secondary-container/20 transition-colors border-b border-dashed border-outline-variant cursor-pointer last:border-b-0"
                        x-on:click="$dispatch('play-queue', { queue: tracks, index: index, context: { title: {{ \Illuminate\Support\Js::from($playlistDetails['title']) }}, type: {{ \Illuminate\Support\Js::from($type) }}, author: {{ \Illuminate\Support\Js::from($playlistDetails['author']) }} } })">
                        <td class="p-4 text-on-surface-variant text-center relative w-16">
                            <span class="group-hover:opacity-0 transition-opacity" x-text="index + 1"></span>
                            <span class="material-symbols-outlined absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 text-primary transition-opacity" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 sketchy-border overflow-hidden shrink-0">
                                    <img class="w-full h-full object-cover" :src="track.thumbnail">
                                </div>
                                <div class="min-w-0">
                                    <p class="font-headline-md text-[16px] truncate group-hover:text-primary transition-colors max-w-[200px] sm:max-w-xs md:max-w-[300px]" x-text="track.title"></p>
                                    <p class="text-[12px] text-on-surface-variant md:hidden truncate max-w-[200px] sm:max-w-xs" x-text="track.artist"></p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 text-on-surface-variant text-[14px] hidden md:table-cell truncate max-w-[200px] lg:max-w-[300px]" x-text="track.artist"></td>
                        <td class="p-4 text-right text-on-surface-variant text-[14px] font-mono whitespace-nowrap" x-text="track.duration"></td>
                        <td class="p-4">
                            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity justify-end">
                                <button title="Favorite" 
                                    class="material-symbols-outlined transition-colors focus:outline-none" 
                                    :class="track.isSaved ? 'text-primary' : 'text-on-surface-variant hover:text-primary'"
                                    :style="`font-variation-settings: 'FILL' ${track.isSaved ? 1 : 0};`"
                                    @click.stop="
                                        @auth 
                                            track.isSaved = !track.isSaved; 
                                            $dispatch('show-toast', 'Berhasil disimpan ke favorit!');
                                            $dispatch('saveTrackToLibrary', { track: track });
                                            if ('{{ $playlistId }}' === 'favorites' && !track.isSaved) {
                                                tracks.splice(index, 1);
                                            }
                                        @else 
                                            showLoginModal = true; 
                                        @endauth
                                    ">
                                    favorite
                                </button>
                            </div>
                        </td>
                    </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
