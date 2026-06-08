<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use App\Services\MusicService;
use App\Models\PlayHistory;
use Illuminate\Support\Facades\Auth;

#[Lazy]
class Home extends Component
{
    public function render(MusicService $musicService)
    {
        $sections = $musicService->getHomeSections();

        $recentlyPlayed = [];
        if (Auth::check()) {
            $recentlyPlayed = PlayHistory::where('user_id', Auth::id())
                ->orderBy('played_at', 'desc')
                ->take(10)
                ->get();
        }

        return view('livewire.home', [
            'sections' => $sections,
            'recentlyPlayed' => $recentlyPlayed
        ])->layout('layouts.app');
    }

    public function placeholder()
    {
        return view('livewire.skeletons.home-skeleton')->layout('layouts.app');
    }
}
