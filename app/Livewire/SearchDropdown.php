<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\MusicService;

class SearchDropdown extends Component
{
    public $query = '';
    public $results = [];

    public function updatedQuery()
    {
        if (strlen($this->query) > 0) {
            $musicService = app(MusicService::class);
            $response = $musicService->search($this->query);
            $this->results = $response;
        } else {
            $this->results = [];
        }
    }

    public function render()
    {
        return view('livewire.search-dropdown');
    }
}
