<div x-data="{ show: false }"
     @toast-opened.window="show = true; setTimeout(() => show = false, 3000)"
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
    <p class="font-headline-md text-headline-md text-on-surface">{{ $message }}</p>
    <button class="material-symbols-outlined text-on-surface-variant hover:text-primary ml-2" @click="show = false">close</button>
</div>
