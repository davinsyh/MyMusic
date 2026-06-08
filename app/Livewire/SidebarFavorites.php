<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FavoriteCollection;
use Illuminate\Support\Facades\Auth;

class SidebarFavorites extends Component
{
    #[\Livewire\Attributes\On('favorites-updated')]
    public function render()
    {
        $favorites = [];
        if (Auth::check()) {
            $favorites = FavoriteCollection::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('livewire.sidebar-favorites', [
            'favorites' => $favorites
        ]);
    }
}
