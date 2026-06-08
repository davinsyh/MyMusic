<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Account extends Component
{
    public $name;
    public $username;
    public $email;

    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public $delete_password;

    public function mount()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
        }
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->name = $this->name;
        $user->save();

        session()->flash('profile_message', __('Profile updated successfully.'));
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('The provided password does not match your current password.'),
            ]);
        }

        $user->password = Hash::make($this->new_password);
        $user->save();

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('password_message', __('Password updated successfully.'));
    }

    public function deleteAccount()
    {
        $this->validate([
            'delete_password' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->delete_password, $user->password)) {
            throw ValidationException::withMessages([
                'delete_password' => __('The provided password does not match your current password.'),
            ]);
        }

        Auth::logout();
        $user->delete();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }

    public function render()
    {
        return view('livewire.account')->layout('layouts.app');
    }
}
