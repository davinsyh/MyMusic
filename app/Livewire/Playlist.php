<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\MusicService;
use App\Models\FavoriteCollection;
use App\Models\SavedLibrary;
use App\Models\PlayHistory;
use Illuminate\Support\Facades\Auth;

class Playlist extends Component
{
    public $playlistId;
    public $playlistDetails;
    public $type = 'Playlist';
    public $isFavorited = false;

    public function mount($id, MusicService $musicService)
    {
        $this->playlistId = $id;

        // Pengecekan Autentikasi untuk playlist khusus
        if (in_array($id, ['favorites', 'history']) && !Auth::check()) {
            session()->flash('showLoginModal', true);
            return $this->redirect('/', navigate: true);
        }
        
        if ($id === 'favorites') {
            $this->type = 'Track';
            $savedTracks = SavedLibrary::where('user_id', Auth::id())->orderBy('saved_at', 'desc')->get();
            $tracks = $savedTracks->map(function ($track) {
                return [
                    'videoId' => $track->yt_track_id,
                    'title' => $track->track_title,
                    'artist' => $track->artist_name,
                    'duration' => '--:--',
                    'thumbnail' => $track->thumbnail_url,
                    'artists' => [['name' => $track->artist_name, 'id' => null]],
                    'thumbnails' => [['url' => $track->thumbnail_url]],
                ];
            })->toArray();

            $this->playlistDetails = [
                'title' => 'Favorited Song',
                'description' => 'Lagu-lagu yang Anda sukai.',
                'author' => Auth::user()->name,
                'thumbnails' => count($tracks) > 0 ? $tracks[0]['thumbnail'] : null,
                'trackCount' => count($tracks),
                'tracks' => $tracks
            ];
        } elseif ($id === 'history') {
            $this->type = 'Track';
            $historyTracks = PlayHistory::where('user_id', Auth::id())->orderBy('played_at', 'desc')->get();
            $tracks = $historyTracks->map(function ($track) {
                return [
                    'videoId' => $track->yt_track_id,
                    'title' => $track->track_title,
                    'artist' => $track->artist_name,
                    'duration' => '--:--',
                    'thumbnail' => $track->thumbnail_url,
                    'artists' => [['name' => $track->artist_name, 'id' => null]],
                    'thumbnails' => [['url' => $track->thumbnail_url]],
                ];
            })->toArray();

            $this->playlistDetails = [
                'title' => 'Recently Played',
                'description' => 'Riwayat lagu yang baru saja Anda putar.',
                'author' => Auth::user()->name,
                'thumbnails' => count($tracks) > 0 ? $tracks[0]['thumbnail'] : null,
                'trackCount' => count($tracks),
                'tracks' => $tracks
            ];
        } else {
            if (str_starts_with($id, 'MPREb_') || str_starts_with($id, 'browseId')) {
                $this->type = 'Album';
            } elseif (str_starts_with($id, 'UC')) {
                $this->type = 'Artist';
            } elseif (str_starts_with($id, 'MPSP')) {
                $this->type = 'Podcast';
            } else {
                $this->type = 'Playlist';
            }

            $this->playlistDetails = $musicService->getPlaylistDetails($id);

            if (!$this->playlistDetails) {
                abort(404, 'Playlist not found or unavailable');
            }

            if (Auth::check()) {
                $this->isFavorited = FavoriteCollection::where('user_id', Auth::id())
                    ->where('yt_id', $this->playlistId)
                    ->exists();
            }
        }

        // Tentukan status isSaved untuk setiap lagu
        if (Auth::check() && isset($this->playlistDetails['tracks'])) {
            $savedTrackIds = SavedLibrary::where('user_id', Auth::id())
                ->pluck('yt_track_id')
                ->toArray();
                
            foreach ($this->playlistDetails['tracks'] as &$track) {
                $vId = $track['videoId'] ?? $track['id'] ?? null;
                $track['isSaved'] = $vId ? in_array($vId, $savedTrackIds) : false;
            }
        }
    }

    public function toggleFavorite()
    {
        if (!Auth::check()) {
            session()->flash('showLoginModal', true);
            return $this->redirect(request()->header('Referer') ?? '/', navigate: true);
        }

        if ($this->isFavorited) {
            FavoriteCollection::where('user_id', Auth::id())
                ->where('yt_id', $this->playlistId)
                ->delete();
            $this->isFavorited = false;
        } else {
            FavoriteCollection::create([
                'user_id' => Auth::id(),
                'yt_id' => $this->playlistId,
                'title' => $this->playlistDetails['title'] ?? 'Unknown',
                'author' => $this->playlistDetails['author'] ?? 'Unknown',
                'type' => $this->type,
                'thumbnail_url' => $this->playlistDetails['thumbnails'] ?? null
            ]);
            $this->isFavorited = true;
            $this->dispatch('show-toast', 'Berhasil disimpan ke favorit!');
        }
        
        $this->dispatch('favorites-updated');
    }

    public function render()
    {
        return view('livewire.playlist')->layout('layouts.app');
    }
}
