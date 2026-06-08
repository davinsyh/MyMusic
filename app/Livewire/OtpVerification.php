<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class OtpVerification extends Component
{
    public $email = '';
    public $otp = '';

    #[On('open-otp-modal')]
    public function setPendingEmail($email)
    {
        $this->email = $email;
        $this->otp = '';
    }

    public function verify()
    {
        $this->validate([
            'otp' => 'required|string|size:6'
        ]);

        if (empty($this->email)) {
            $this->addError('otp', 'Terjadi kesalahan sistem. Silakan ulangi pendaftaran.');
            return;
        }

        $cacheKey = 'register_otp_' . $this->email;
        $pendingData = Cache::get($cacheKey);

        if (!$pendingData) {
            $this->addError('otp', 'Kode OTP telah kadaluarsa atau tidak valid. Silakan daftar ulang.');
            return;
        }

        if ($pendingData['otp'] !== $this->otp) {
            $this->addError('otp', 'Kode OTP salah. Silakan coba lagi.');
            return;
        }

        // OTP is correct! Create user
        $user = User::create([
            'name' => $pendingData['name'],
            'username' => $pendingData['username'],
            'email' => $pendingData['email'],
            'password' => $pendingData['password'],
            'email_verified_at' => now(),
            'role' => 'user'
        ]);

        // Clear cache
        Cache::forget($cacheKey);

        // Auto login
        Auth::login($user);

        // Redirect to home
        return redirect()->intended('/');
    }

    public function render()
    {
        return view('livewire.otp-verification');
    }
}
