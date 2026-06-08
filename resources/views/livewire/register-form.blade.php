<div class="w-full flex flex-col items-center">
    <!-- Close Button -->
    <button @click="showRegisterModal = false" type="button" class="absolute -top-4 -right-4 w-10 h-10 bg-surface border-2 border-on-background rounded-full flex items-center justify-center shadow-[2px_2px_0px_0px_rgba(28,27,27,1)] hover:scale-110 active:scale-90 transition-transform z-50">
        <span class="material-symbols-outlined text-on-background">close</span>
    </button>
    
    <!-- Logo -->
    <div class="w-20 h-20 mb-4 sketchy-border block-shadow bg-surface p-2 rotate-[-2deg] flex items-center justify-center overflow-hidden">
        <img src="{{ asset('images/logoTanpaTulisan.png') }}" alt="MyMusic Logo" class="w-full h-full object-contain scale-[1.2]">
    </div>
    
    <h2 class="font-headline-lg text-headline-lg text-on-background mb-4 text-center">Create Account</h2>
    
    <!-- Form -->
    <form wire:submit.prevent="register" class="w-full flex flex-col gap-3">
        
        <div class="flex flex-col gap-1">
            <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Full Name</label>
            <input wire:model="name" class="w-full bg-surface border-2 border-on-background px-3 py-2 rounded-lg font-body-md focus:ring-0 focus:rotate-[1deg] transition-transform placeholder:text-outline-variant" type="text" required autofocus>
            @error('name')<span class="text-error text-[12px] ml-1">{{ $message }}</span>@enderror
        </div>

        <div class="flex flex-col gap-1">
            <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Username</label>
            <input wire:model="username" class="w-full bg-surface border-2 border-on-background px-3 py-2 rounded-lg font-body-md focus:ring-0 focus:rotate-[-1deg] transition-transform placeholder:text-outline-variant" type="text" required>
            @error('username')<span class="text-error text-[12px] ml-1">{{ $message }}</span>@enderror
        </div>

        <div class="flex flex-col gap-1">
            <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Email</label>
            <input wire:model="email" class="w-full bg-surface border-2 border-on-background px-3 py-2 rounded-lg font-body-md focus:ring-0 focus:rotate-[1deg] transition-transform placeholder:text-outline-variant" type="email" required>
            @error('email')<span class="text-error text-[12px] ml-1">{{ $message }}</span>@enderror
        </div>
        
        <div class="flex flex-col gap-1">
            <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Password</label>
            <div class="relative w-full" x-data="{ showPass: false }">
                <input wire:model="password" class="w-full bg-surface border-2 border-on-background px-3 py-2 rounded-lg font-body-md focus:ring-0 focus:rotate-[-1deg] transition-transform placeholder:text-outline-variant" :type="showPass ? 'text' : 'password'" required>
                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors outline-none" @click="showPass = !showPass">
                    <span class="material-symbols-outlined text-[20px]" x-text="showPass ? 'visibility' : 'visibility_off'">visibility_off</span>
                </button>
            </div>
            @error('password')<span class="text-error text-[12px] ml-1">{{ $message }}</span>@enderror
        </div>

        <div class="flex flex-col gap-1">
            <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">Confirm Password</label>
            <div class="relative w-full" x-data="{ showPass: false }">
                <input wire:model="password_confirmation" class="w-full bg-surface border-2 border-on-background px-3 py-2 rounded-lg font-body-md focus:ring-0 focus:rotate-[1deg] transition-transform placeholder:text-outline-variant" :type="showPass ? 'text' : 'password'" required>
                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors outline-none" @click="showPass = !showPass">
                    <span class="material-symbols-outlined text-[20px]" x-text="showPass ? 'visibility' : 'visibility_off'">visibility_off</span>
                </button>
            </div>
            @error('password_confirmation')<span class="text-error text-[12px] ml-1">{{ $message }}</span>@enderror
        </div>
        
        <button class="mt-4 w-full bg-primary text-white font-headline-md py-3 block-shadow hover:scale-[1.02] active:scale-95 transition-all rotate-[1deg] flex items-center justify-center gap-2" type="submit">
            <span wire:loading.remove wire:target="register">{{ __('Sign Up') }}</span>
            <span wire:loading wire:target="register">Sending OTP...</span>
        </button>
    </form>
    
    <!-- Footer Links -->
    <div class="mt-6 flex flex-col items-center gap-2">
        <p class="font-body-md text-[14px] text-on-surface-variant">
            Already have an account? 
            <button type="button" @click="showRegisterModal = false; showLoginModal = true" class="text-primary font-bold hover:underline">Sign In</button>
        </p>
    </div>
</div>
