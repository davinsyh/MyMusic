<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\OtpVerificationMail;

class RegisterForm extends Component
{
    public $name = '';
    public $username = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_.]+$/',
                'unique:users'
            ],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    protected function messages()
    {
        return [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik (.), dan garis bawah (_).',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.unique' => 'Username ini sudah digunakan, silakan pilih yang lain.',
        ];
    }

    public function updatedUsername($value)
    {
        // Auto-convert spaces to underscores and make lowercase (Instagram style)
        $this->username = strtolower(preg_replace('/\s+/', '_', $value));
    }

    public function register()
    {
        $this->validate();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store registration data and OTP in Cache for 10 minutes
        Cache::put('register_otp_' . $this->email, [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'otp' => $otp
        ], now()->addMinutes(10));

        // Send Email
        try {
            Mail::to($this->email)->send(new OtpVerificationMail($otp));
        } catch (\Exception $e) {
            $this->addError('email', 'Gagal mengirim email OTP: ' . $e->getMessage());
            return;
        }

        // Trigger an event to open the OTP modal
        $this->dispatch('open-otp-modal', email: $this->email);
    }

    public function render()
    {
        return view('livewire.register-form');
    }
}
