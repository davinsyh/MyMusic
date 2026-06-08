<div class="w-full flex flex-col items-center">
    <!-- Close Button -->
    <button @click="showLoginModal = false" type="button" class="absolute -top-4 -right-4 w-10 h-10 bg-surface border-2 border-on-background rounded-full flex items-center justify-center shadow-[2px_2px_0px_0px_rgba(28,27,27,1)] hover:scale-110 active:scale-90 transition-transform z-50">
        <span class="material-symbols-outlined text-on-background">close</span>
    </button>
    
    <!-- Logo -->
    <div class="w-24 h-24 mb-6 sketchy-border block-shadow bg-surface p-2 rotate-[2deg] flex items-center justify-center overflow-hidden">
        <img src="{{ asset('images/logoTanpaTulisan.png') }}" alt="MyMusic Logo" class="w-full h-full object-contain scale-[1.2]">
    </div>
    
    <h2 class="font-headline-lg text-headline-lg text-on-background mb-6 text-center">Welcome Back!</h2>
    
    <!-- Form -->
    <form wire:submit.prevent="authenticate" class="w-full flex flex-col gap-4">
        <div class="flex flex-col gap-1">
            <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Email or Username</label>
            <input wire:model="login" class="w-full bg-surface border-2 border-on-background p-3 rounded-lg font-body-md focus:ring-0 focus:rotate-[1deg] transition-transform placeholder:text-outline-variant" type="text" required autofocus>
            @error('login')<span class="text-error text-[12px] ml-1">{{ $message }}</span>@enderror
        </div>
        
        <div class="flex flex-col gap-1">
            <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Password</label>
            <div class="relative w-full" x-data="{ showPass: false }">
                <input wire:model="password" class="w-full bg-surface border-2 border-on-background p-3 rounded-lg font-body-md focus:ring-0 focus:rotate-[-1deg] transition-transform placeholder:text-outline-variant" :type="showPass ? 'text' : 'password'" required>
                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors outline-none" @click="showPass = !showPass">
                    <span class="material-symbols-outlined text-[20px]" x-text="showPass ? 'visibility' : 'visibility_off'">visibility_off</span>
                </button>
            </div>
            @error('password')<span class="text-error text-[12px] ml-1">{{ $message }}</span>@enderror
        </div>
        
        <div class="flex items-center gap-2 ml-1 mb-2">
            <input type="checkbox" id="remember-me" wire:model="remember" class="w-5 h-5 bg-surface border-2 border-on-background rounded-sm text-primary focus:ring-0 focus:ring-offset-0 cursor-pointer transition-transform active:scale-90">
            <label for="remember-me" class="font-body-md text-[14px] text-on-surface-variant cursor-pointer select-none">Remember Me</label>
        </div>
        
        <button class="mt-4 w-full bg-primary text-white font-headline-md py-3 block-shadow hover:scale-[1.02] active:scale-95 transition-all rotate-[1deg] flex items-center justify-center gap-2" type="submit">
            <span wire:loading.remove wire:target="authenticate">Sign In</span>
            <span wire:loading wire:target="authenticate">Signing in...</span>
        </button>
    </form>
    
    <!-- Footer Links -->
    <div class="mt-6 flex flex-col items-center gap-2">
        <p class="font-body-md text-[14px] text-on-surface-variant">
            Don't have an account? 
            <button type="button" @click="showLoginModal = false; showRegisterModal = true" class="text-primary font-bold hover:underline">{{ __('Sign Up') }}</button>
        </p>
    </div>
</div>
