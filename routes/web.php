<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->name('login');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Note: Livewire component routes will be added here
Route::get('/', \App\Livewire\Home::class)->name('home');
Route::get('/library', \App\Livewire\Library::class)->name('library')->middleware('auth');
Route::get('/account', \App\Livewire\Account::class)->name('account');
Route::get('/playlist/{id}', \App\Livewire\Playlist::class)->name('playlist.show');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
});

// Track API Endpoint
Route::get('/api/track/{id}', function ($id, \App\Services\MusicService $musicService) {
    return response()->json($musicService->getTrack($id));
});
