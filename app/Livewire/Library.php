<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SavedLibrary;
use App\Models\PlayHistory;
use Illuminate\Support\Facades\Auth;

class Library extends Component
{
    use WithPagination;

    public $tab = 'saved'; // 'saved' or 'history'

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $userId = Auth::id();

        if ($this->tab === 'saved') {
            $items = SavedLibrary::where('user_id', $userId)
                        ->orderBy('saved_at', 'desc')
                        ->paginate(20);
        } else {
            $items = PlayHistory::where('user_id', $userId)
                        ->orderBy('played_at', 'desc')
                        ->paginate(20);
        }

        return view('livewire.library', [
            'items' => $items
        ])->layout('layouts.app');
    }
}
