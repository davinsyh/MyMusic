<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MyMusic') }}</title>
    <link rel="icon" href="{{ asset('images/logoTanpaTulisan.png') }}" type="image/png">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Delius+Swash+Caps&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script>
        // Anti-FOUC Dark Mode script
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <!-- YouTube IFrame API (dimuat di sini agar stabil, tidak terganggu Livewire) -->
    <script src="https://www.youtube.com/iframe_api"></script>
</head>
<body class="font-body-md text-on-surface bg-background" x-data="{ showLoginModal: {{ $errors->has('login') || $errors->has('password') || session('showLoginModal') ? 'true' : 'false' }}, showRegisterModal: false, showOtpModal: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', darkMode: localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }" x-effect="localStorage.setItem('sidebarCollapsed', sidebarCollapsed); localStorage.setItem('darkMode', darkMode); document.documentElement.classList.toggle('dark', darkMode)">
    <!-- Sidebar Navigation -->
    <aside id="sidebar" :class="sidebarCollapsed ? 'collapsed' : ''" class="fixed left-0 top-16 h-[calc(100vh-4rem)] flex flex-col px-margin pb-margin gap-unit bg-background w-64 border-r-2 border-on-background shadow-[4px_0px_0px_0px_rgba(28,27,27,1)] z-40 transition-all duration-300">
        <nav class="flex flex-col gap-4 shrink-0 pt-2">
            <a href="/" wire:navigate class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary-container/30 transition-colors {{ request()->is('/') ? 'bg-secondary-container/50 font-bold text-on-secondary-container' : 'text-on-surface-variant' }}" title="{{ __('Home') }}">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->is('/') ? 1 : 0 }};">home</span>
                <span class="font-headline-md text-[18px] tracking-wide" x-show="!sidebarCollapsed">{{ __('Home') }}</span>
            </a>
        </nav>
        
        <div class="mt-8 flex-1 overflow-y-auto custom-scrollbar pb-24 pr-2"
             x-data="{ isScrolling: false, scrollTimeout: null }"
             @scroll="isScrolling = true; clearTimeout(scrollTimeout); scrollTimeout = setTimeout(() => isScrolling = false, 1000)"
             :class="isScrolling ? 'is-scrolling' : ''">
            <h3 class="fav-header font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest mb-4 px-2">{{ __('Your Favorites') }}</h3>
            <div class="flex flex-col gap-2">
                <!-- Liked Songs -->
                <a href="{{ url('/playlist/favorites') }}" wire:navigate class="playlist-item flex items-center gap-3 p-2 rounded-lg transition-all group {{ request()->is('playlist/favorites') ? 'bg-secondary-container/30 border-2 border-on-background shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] rotate-[1deg] font-bold' : 'hover:bg-surface-container hover:rotate-[-1deg]' }}">
                    <div class="playlist-icon w-12 h-12 sketchy-border bg-primary-container flex items-center justify-center shrink-0 rotate-[1deg]">
                        <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">favorite</span>
                    </div>
                    <div class="playlist-details overflow-hidden">
                        <p class="font-headline-md text-[16px] truncate">{{ __('Favorite') }}</p>
                        <p class="font-label-sm text-[10px] text-on-surface-variant flex items-center gap-1">
                            {{ __('Playlist') }} • {{ __('You') }}
                        </p>
                    </div>
                </a>
                
                <!-- Recently Played -->
                <a href="{{ url('/playlist/history') }}" wire:navigate class="playlist-item flex items-center gap-3 p-2 rounded-lg transition-all group {{ request()->is('playlist/history') ? 'bg-secondary-container/30 border-2 border-on-background shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] rotate-[1deg] font-bold' : 'hover:bg-surface-container hover:rotate-[-1deg]' }}">
                    <div class="playlist-icon w-12 h-12 sketchy-border bg-tertiary-container flex items-center justify-center shrink-0 rotate-[-1deg]">
                        <span class="material-symbols-outlined text-on-tertiary-container">history</span>
                    </div>
                    <div class="playlist-details overflow-hidden">
                        <p class="font-headline-md text-[16px] truncate">{{ __('Recently Played') }}</p>
                        <p class="font-label-sm text-[10px] text-on-surface-variant flex items-center gap-1">
                            {{ __('Playlist') }} • {{ __('You') }}
                        </p>
                    </div>
                </a>
                
                @auth
                @livewire('sidebar-favorites')
                @endauth
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main id="main-content" :class="sidebarCollapsed ? 'sidebar-collapsed' : ''" class="ml-64 pb-32 min-h-screen transition-all duration-300">
        <!-- Top App Bar (Search & Notifications) -->
        <header id="top-bar" class="fixed top-0 right-0 left-0 z-50 h-16 bg-background/90 backdrop-blur-md flex justify-between items-center px-4 md:px-gutter border-b-2 border-solid border-on-background shadow-sm transition-all duration-300 gap-4">
            
            <!-- Left Side: Hamburger & Logo -->
            <div class="flex items-center gap-2 md:gap-4 w-auto md:w-64 shrink-0">
                <button @click="sidebarCollapsed = !sidebarCollapsed" class="material-symbols-outlined p-2 hover:bg-secondary-container/50 rounded-full transition-colors border-2 border-transparent active:border-on-background" title="{{ __('Toggle Sidebar') }}">menu</button>
                <a href="/" wire:navigate class="flex items-center gap-2 group outline-none">
                    <img src="{{ asset('images/logoTanpaTulisan.png') }}" alt="MyMusic Icon" class="h-8 md:h-10 object-contain transition-transform group-active:scale-95">
                    <img src="{{ asset('images/tulisanTanpaLogo.png') }}" alt="MyMusic Text" class="h-6 md:h-8 object-contain transition-transform group-active:scale-95 hidden md:block" x-show="!darkMode">
                    <img src="{{ asset('images/tulisanTanpaLogoWarnaPutih.png') }}" alt="MyMusic Text" class="h-6 md:h-8 object-contain transition-transform group-active:scale-95 hidden md:block" x-show="darkMode" x-cloak>
                </a>
            </div>
            
            <div class="flex-1 max-w-2xl px-4">
                @livewire('search-dropdown')
            </div>
            
            <div class="flex items-center gap-6">
                @guest
                <button type="button" @click="showLoginModal = true" class="font-headline-sm text-primary hover:underline cursor-pointer">{{ __('Login') }}</button>
                @endguest
                
                <div class="relative" x-data="{ showSettings: false }">
                    <button @click="showSettings = !showSettings" @click.away="showSettings = false" class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors hover:scale-110" id="settings-menu-btn">settings</button>
                    
                    <div x-show="showSettings" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 top-full mt-2 w-64 bg-surface border-2 border-on-background shadow-[6px_6px_0px_0px_rgba(28,27,27,1)] z-50 rotate-[-1deg] p-2 origin-top-right">
                        <div class="flex flex-col gap-1">
                            <button @click="darkMode = !darkMode" class="w-full text-left p-3 hover:bg-secondary-container/20 hover:rotate-[1deg] transition-all font-headline-md text-[16px] border-b border-dashed border-outline-variant flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px]" x-text="darkMode ? 'light_mode' : 'dark_mode'">palette</span>
                                <span x-text="darkMode ? '{{ __('Light Mode') }}' : '{{ __('Dark Mode') }}'">{{ __('Theme Appearance') }}</span>
                            </button>
                            
                            <a href="{{ route('lang.switch', app()->getLocale() === 'en' ? 'id' : 'en') }}" class="w-full text-left p-3 hover:bg-secondary-container/20 hover:rotate-[-1deg] transition-all font-headline-md text-[16px] @auth border-b border-dashed border-outline-variant @endauth flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-[20px]">language</span>
                                    <span>{{ __('Language Appearance') }}</span>
                                </div>
                                <span class="font-bold text-primary text-sm bg-primary-container/30 px-2 py-0.5 rounded">{{ strtoupper(app()->getLocale()) }}</span>
                            </a>

                            @auth
                            <a href="{{ route('account') }}" wire:navigate class="w-full text-left p-3 hover:bg-secondary-container/20 hover:rotate-[1deg] transition-all font-headline-md text-[16px] border-b border-dashed border-outline-variant flex items-center gap-3">
                                <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                                {{ __('Account Settings') }}
                            </a>
                            
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <button type="submit" class="w-full text-left p-3 hover:bg-error-container/30 hover:rotate-[-1.5deg] transition-all font-headline-md text-[16px] text-error flex items-center gap-3">
                                    <span class="material-symbols-outlined text-[20px]">logout</span>
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Content -->
        <div class="pt-16">
            {{ $slot }}
        </div>
    </main>

    <!-- Global Persistent Audio Player -->
    @persist('player')
    <footer class="fixed bottom-0 left-0 w-full h-24 z-[100] glass-player border-t-2 border-on-background shadow-[0px_-4px_0px_0px_rgba(28,27,27,1)] flex flex-row items-center justify-between px-gutter py-2">
        @livewire('audio-player')
    </footer>
    @endpersist

    <!-- Login Modal -->
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-on-background/50 backdrop-blur-sm p-4" id="login-modal-overlay" x-show="showLoginModal" x-cloak x-transition.opacity>
        <div class="relative w-full max-w-sm bg-surface p-8 sketchy-border block-shadow rotate-[-1deg] flex flex-col items-center" style="border-radius: 8px 16px 4px 12px;" @click.away="showLoginModal = false" x-show="showLoginModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
            @livewire('login-form')
        </div>
    </div>

    <!-- Register Modal -->
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-on-background/50 backdrop-blur-sm p-4 overflow-y-auto" id="register-modal-overlay" x-show="showRegisterModal" x-cloak x-transition.opacity>
        <div class="relative w-full max-w-sm bg-surface p-8 sketchy-border block-shadow rotate-[1deg] flex flex-col items-center my-auto" style="border-radius: 12px 6px 16px 8px;" @click.away="showRegisterModal = false" x-show="showRegisterModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
            @livewire('register-form')
        </div>
    </div>

    <!-- OTP Modal -->
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-on-background/50 backdrop-blur-sm p-4" id="otp-modal-overlay" x-show="showOtpModal" x-cloak x-transition.opacity>
        <div class="relative w-full max-w-sm bg-surface p-8 sketchy-border block-shadow rotate-[0deg] flex flex-col items-center" style="border-radius: 8px 12px 16px 10px;" @click.away="showOtpModal = false" x-show="showOtpModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
            @livewire('otp-verification')
        </div>
    </div>

    <!-- Global Toast Notification -->
    <div x-data="{ show: false, message: '' }"
         @show-toast.window="message = $event.detail; show = true; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-32 right-8 z-[100] bg-surface sketchy-border p-4 block-shadow rotate-[-1deg] flex items-center gap-3">
        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
        <p class="font-headline-md text-headline-md text-on-surface" x-text="message"></p>
        <button class="material-symbols-outlined text-on-surface-variant hover:text-primary ml-2" @click="show = false">close</button>
    </div>

    @livewireScripts
</body>
</html>
