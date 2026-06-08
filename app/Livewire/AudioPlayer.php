<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PlayHistory;
use Illuminate\Support\Facades\Auth;

class AudioPlayer extends Component
{
    public function render()
    {
        return view('livewire.audio-player');
    }

    public function recordHistory($trackId, $title, $artist, $thumbnail)
    {
        if (Auth::check()) {
            $lastHistory = \App\Models\PlayHistory::where('user_id', Auth::id())
                ->orderBy('played_at', 'desc')
                ->first();

            if ($lastHistory && $lastHistory->yt_track_id === $trackId) {
                $lastHistory->played_at = now();
                $lastHistory->save();
            } else {
                \App\Models\PlayHistory::create([
                    'user_id' => Auth::id(),
                    'yt_track_id' => $trackId,
                    'track_title' => $title,
                    'artist_name' => $artist,
                    'thumbnail_url' => $thumbnail,
                ]);
            }
        }
    }

    public function checkIsSaved($trackId)
    {
        if (Auth::check()) {
            return \App\Models\SavedLibrary::where('user_id', Auth::id())
                ->where('yt_track_id', $trackId)
                ->exists();
        }
        return false;
    }

    #[\Livewire\Attributes\On('saveTrackToLibrary')]
    public function saveTrackToLibrary($track)
    {
        $trackId = $track['id'] ?? $track['videoId'] ?? null;
        $type = $track['type'] ?? 'video';
        
        if (Auth::check() && $trackId) {
            if ($type === 'video' || $type === 'song') {
                $saved = \App\Models\SavedLibrary::where('user_id', Auth::id())
                            ->where('yt_track_id', $trackId)
                            ->first();
                
                if ($saved) {
                    $saved->delete();
                } else {
                    \App\Models\SavedLibrary::create([
                        'user_id' => Auth::id(),
                        'yt_track_id' => $trackId,
                        'track_title' => $track['title'] ?? 'Unknown',
                        'artist_name' => $track['artist'] ?? 'Unknown',
                        'thumbnail_url' => $track['thumbnail'] ?? '',
                    ]);
                    $this->dispatch('show-toast', 'Berhasil disimpan ke favorit!');
                }
                $this->dispatch('favorites-updated');
            } else {
                $saved = \App\Models\FavoriteCollection::where('user_id', Auth::id())
                            ->where('yt_id', $trackId)
                            ->first();
                
                if ($saved) {
                    $saved->delete();
                } else {
                    \App\Models\FavoriteCollection::create([
                        'user_id' => Auth::id(),
                        'yt_id' => $trackId,
                        'title' => $track['title'] ?? 'Unknown',
                        'type' => ucfirst($type),
                        'author' => $track['artist'] ?? 'Unknown',
                        'thumbnail_url' => $track['thumbnail'] ?? '',
                    ]);
                    $this->dispatch('show-toast', 'Berhasil disimpan ke favorit!');
                }
                $this->dispatch('favorites-updated');
            }
        }
    }
}
