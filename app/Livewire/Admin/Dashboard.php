<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\PlayHistory;

class Dashboard extends Component
{
    public function render()
    {
        $users = User::latest()->get();
        $totalPlays = PlayHistory::count();
        $totalUsers = User::count();

        return view('livewire.admin.dashboard', [
            'users' => $users,
            'totalPlays' => $totalPlays,
            'totalUsers' => $totalUsers,
        ])->layout('layouts.app');
    }

    public function toggleRole($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id !== auth()->id()) {
            $user->role = $user->role === 'admin' ? 'user' : 'admin';
            $user->save();
        }
    }
}
