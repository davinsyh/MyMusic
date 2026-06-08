<div class="space-y-6">
    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ __('Your Library') }}</h1>
        
        <div class="flex space-x-2 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg">
            <button wire:click="setTab('saved')" 
                class="px-4 py-2 rounded-md text-sm font-medium transition {{ $tab === 'saved' ? 'bg-surface dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                Favorite
            </button>
            <button wire:click="setTab('history')" 
                class="px-4 py-2 rounded-md text-sm font-medium transition {{ $tab === 'history' ? 'bg-surface dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                {{ __('Play History') }}
            </button>
        </div>
    </div>

    @if($items->count() === 0)
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('No tracks') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $tab === 'saved' ? '{{ __('You haven\'t added any songs to Favorite yet.') }}' : '{{ __('You haven\'t played any songs yet.') }}' }}
            </p>
        </div>
    @else
        <div class="bg-surface dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700"
             x-data="{ tracks: {{ collect($items->items())->map(fn($t) => ['id' => $t->yt_track_id, 'title' => $t->track_title, 'artist' => $t->artist_name, 'thumbnail' => $t->thumbnail_url])->toJson() }} }">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Title') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Artist') }}</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ $tab === 'saved' ? '{{ __('Favorited At') }}' : '{{ __('Played At') }}' }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($items as $index => $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition cursor-pointer group"
                            x-on:click="$dispatch('play-queue', { queue: tracks, index: {{ $index }} })">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 w-12 text-center group-hover:hidden">
                                {{ $items->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 w-12 text-center hidden group-hover:table-cell">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded object-cover" src="{{ $item->thumbnail_url }}" alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->track_title }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $item->artist_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                {{ $tab === 'saved' ? $item->saved_at->diffForHumans() : $item->played_at->diffForHumans() }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $items->links() }}
        </div>
    @endif
</div>
