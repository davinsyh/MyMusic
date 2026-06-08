<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LoginForm extends Component
{
    public $login = '';
    public $password = '';
    public $remember = false;

    protected function rules()
    {
        return [
            'login' => 'required|string',
            'password' => 'required|string',
        ];
    }

    public function authenticate()
    {
        $this->validate();

        $login_type = filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $login_type => $this->login,
            'password' => $this->password
        ];

        if (Auth::attempt($credentials, $this->remember)) {
            session()->regenerate();
            return redirect()->intended('/');
        }

        $this->addError('login', 'The provided credentials do not match our records.');
    }

    public function render()
    {
        return view('livewire.login-form');
    }
}
