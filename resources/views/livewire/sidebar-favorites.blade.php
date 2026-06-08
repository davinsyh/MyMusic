<div>
    @foreach($favorites as $fav)
    <a href="{{ url('/playlist/' . $fav->yt_id) }}" wire:navigate class="playlist-item flex items-center gap-3 p-2 rounded-lg transition-all group {{ request()->is('playlist/' . $fav->yt_id) ? 'bg-secondary-container/30 border-2 border-on-background shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] rotate-[1deg] font-bold' : 'hover:bg-surface-container hover:rotate-[-1deg]' }}">
        @if($fav->thumbnail_url)
        <img alt="{{ $fav->title }}" class="playlist-icon w-12 h-12 sketchy-border object-cover shrink-0" style="transform: rotate({{ rand(-2, 2) }}deg);" src="{{ $fav->thumbnail_url }}">
        @else
        <div class="playlist-icon w-12 h-12 sketchy-border bg-secondary-container flex items-center justify-center shrink-0" style="transform: rotate({{ rand(-2, 2) }}deg);">
            <span class="material-symbols-outlined">library_music</span>
        </div>
        @endif
        <div class="playlist-details overflow-hidden">
            <p class="font-headline-md text-[16px] truncate">{{ $fav->title }}</p>
            <p class="font-label-sm text-[10px] text-on-surface-variant truncate">{{ $fav->type }} • {{ $fav->author }}</p>
        </div>
    </a>
    @endforeach
</div>
