<div class="w-full flex flex-col items-center" @open-otp-modal.window="showOtpModal = true; showRegisterModal = false;">
    <!-- Close Button -->
    <button @click="showOtpModal = false" type="button" class="absolute -top-4 -right-4 w-10 h-10 bg-surface border-2 border-on-background rounded-full flex items-center justify-center shadow-[2px_2px_0px_0px_rgba(28,27,27,1)] hover:scale-110 active:scale-90 transition-transform z-50">
        <span class="material-symbols-outlined text-on-background">close</span>
    </button>
    
    <!-- Logo -->
    <div class="w-24 h-24 mb-6 sketchy-border block-shadow bg-surface p-2 rotate-[2deg] flex items-center justify-center overflow-hidden">
        <img src="{{ asset('images/logoTanpaTulisan.png') }}" alt="MyMusic Logo" class="w-full h-full object-contain scale-[1.2]">
    </div>
    
    <h2 class="font-headline-lg text-headline-lg text-on-background mb-2 text-center">Enter OTP</h2>
    <p class="font-body-md text-on-surface-variant text-center mb-6">We've sent a 6-digit code to <span class="font-bold text-primary">{{ $email }}</span></p>
    
    <!-- Form -->
    <form wire:submit.prevent="verify" class="w-full flex flex-col gap-4">
        
        <div class="flex flex-col gap-1 items-center">
            <input wire:model="otp" class="w-full max-w-[200px] text-center text-2xl tracking-[0.5em] bg-surface border-2 border-on-background p-4 rounded-lg font-body-md focus:ring-0 focus:rotate-[-1deg] transition-transform placeholder:text-outline-variant" type="text" maxlength="6" placeholder="------" required autofocus>
            @error('otp')<span class="text-error text-[12px] mt-2 text-center">{{ $message }}</span>@enderror
        </div>
        
        <button class="mt-4 w-full bg-primary text-white font-headline-md py-3 block-shadow hover:scale-[1.02] active:scale-95 transition-all rotate-[1deg] flex items-center justify-center gap-2" type="submit">
            <span wire:loading.remove wire:target="verify">Verify & Login</span>
            <span wire:loading wire:target="verify">Verifying...</span>
        </button>
    </form>
</div>
