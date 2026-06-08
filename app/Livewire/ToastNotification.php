<?php

namespace App\Livewire;

use Livewire\Component;

class ToastNotification extends Component
{
    public $message = '';

    #[\Livewire\Attributes\On('show-toast')]
    public function showToast($message = 'Berhasil disimpan ke favorit!')
    {
        $this->message = $message;
        $this->dispatch('toast-opened');
    }

    public function render()
    {
        return view('livewire.toast-notification');
    }
}
