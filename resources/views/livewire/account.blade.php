<div class="max-w-4xl mx-auto space-y-8">
    <div class="border-b-2 border-dashed border-outline-variant pb-4">
        <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ __('Account Settings') }}</h1>
    </div>

    @guest
        <div class="bg-surface rounded-xl shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] border-2 border-on-background p-8 text-center rotate-[-1deg]">
            <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">account_circle</span>
            <h2 class="text-2xl font-headline-md text-on-surface mb-2">{{ __('You are not signed in') }}</h2>
            <p class="text-on-surface-variant font-body-md mb-6">{{ __('Sign in to view your account settings.') }}</p>
            <div class="flex justify-center gap-4">
                <button type="button" @click="showLoginModal = true" class="px-6 py-2 bg-primary text-on-primary border-2 border-on-background shadow-[2px_2px_0px_0px_rgba(28,27,27,1)] rounded-md hover:translate-y-[1px] hover:translate-x-[1px] hover:shadow-[1px_1px_0px_0px_rgba(28,27,27,1)] active:shadow-none active:translate-y-[2px] active:translate-x-[2px] transition-all font-label-lg">
                    {{ __('Login') }}
                </button>
            </div>
        </div>
    @endguest

    @auth
        <!-- Profile Header -->
        <div class="bg-surface rounded-xl shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] border-2 border-on-background overflow-hidden">
            <div class="p-8 flex flex-col gap-1 border-b border-dashed border-outline-variant">
                <h2 class="font-headline-md text-2xl text-on-surface">{{ auth()->user()->name }}</h2>
                <p class="font-body-md text-on-surface-variant">{{ '@' . auth()->user()->username }}</p>
            </div>

            <!-- Profile Info Form -->
            <div class="p-8 space-y-6">
                <h3 class="font-headline-sm text-xl text-on-surface">{{ __('Profile Information') }}</h3>
                
                @if (session()->has('profile_message'))
                    <div class="p-4 bg-primary-container/20 border-2 border-primary text-primary rounded-md font-body-md">
                        {{ session('profile_message') }}
                    </div>
                @endif

                <form wire:submit.prevent="updateProfile" class="space-y-4 max-w-xl">
                    <div>
                        <label class="block font-label-md text-on-surface-variant mb-1">{{ __('Username') }}</label>
                        <input type="text" wire:model="username" readonly class="w-full bg-surface-container border-2 border-outline-variant rounded-md px-4 py-2 text-on-surface-variant cursor-not-allowed font-body-md" title="{{ __('Username cannot be changed') }}">
                    </div>
                    <div>
                        <label class="block font-label-md text-on-surface-variant mb-1">{{ __('Email') }}</label>
                        <input type="email" wire:model="email" readonly class="w-full bg-surface-container border-2 border-outline-variant rounded-md px-4 py-2 text-on-surface-variant cursor-not-allowed font-body-md" title="{{ __('Email cannot be changed') }}">
                    </div>
                    <div>
                        <label class="block font-label-md text-on-surface mb-1">{{ __('Full Name') }}</label>
                        <input type="text" wire:model="name" class="w-full bg-surface border-2 border-on-background rounded-md px-4 py-2 text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('name') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <button type="submit" class="px-6 py-2 bg-primary text-on-primary border-2 border-on-background shadow-[2px_2px_0px_0px_rgba(28,27,27,1)] rounded-md hover:translate-y-[1px] hover:translate-x-[1px] hover:shadow-[1px_1px_0px_0px_rgba(28,27,27,1)] active:shadow-none active:translate-y-[2px] active:translate-x-[2px] transition-all font-label-lg">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Form -->
        <div class="bg-surface rounded-xl shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] border-2 border-on-background overflow-hidden mt-8">
            <div class="p-8 space-y-6">
                <h3 class="font-headline-sm text-xl text-on-surface">{{ __('Update Password') }}</h3>
                <p class="font-body-md text-on-surface-variant">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>

                @if (session()->has('password_message'))
                    <div class="p-4 bg-primary-container/20 border-2 border-primary text-primary rounded-md font-body-md">
                        {{ session('password_message') }}
                    </div>
                @endif

                <form wire:submit.prevent="updatePassword" class="space-y-4 max-w-xl">
                    <div>
                        <label class="block font-label-md text-on-surface mb-1">{{ __('Current Password') }}</label>
                        <input type="password" wire:model="current_password" class="w-full bg-surface border-2 border-on-background rounded-md px-4 py-2 text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('current_password') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-label-md text-on-surface mb-1">{{ __('New Password') }}</label>
                        <input type="password" wire:model="new_password" class="w-full bg-surface border-2 border-on-background rounded-md px-4 py-2 text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('new_password') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-label-md text-on-surface mb-1">{{ __('Confirm New Password') }}</label>
                        <input type="password" wire:model="new_password_confirmation" class="w-full bg-surface border-2 border-on-background rounded-md px-4 py-2 text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <button type="submit" class="px-6 py-2 bg-primary text-on-primary border-2 border-on-background shadow-[2px_2px_0px_0px_rgba(28,27,27,1)] rounded-md hover:translate-y-[1px] hover:translate-x-[1px] hover:shadow-[1px_1px_0px_0px_rgba(28,27,27,1)] active:shadow-none active:translate-y-[2px] active:translate-x-[2px] transition-all font-label-lg">
                            {{ __('Update Password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="bg-error-container/10 rounded-xl shadow-[4px_4px_0px_0px_rgba(28,27,27,1)] border-2 border-error mt-8 overflow-hidden">
            <div class="p-8 space-y-6">
                <h3 class="font-headline-sm text-xl text-error">{{ __('Danger Zone') }}</h3>
                <p class="font-body-md text-on-surface-variant">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>

                <div x-data="{ showDeleteModal: false }">
                    <button type="button" @click="showDeleteModal = true" class="px-6 py-2 bg-error text-on-error border-2 border-on-background shadow-[2px_2px_0px_0px_rgba(28,27,27,1)] rounded-md hover:translate-y-[1px] hover:translate-x-[1px] hover:shadow-[1px_1px_0px_0px_rgba(28,27,27,1)] active:shadow-none active:translate-y-[2px] active:translate-x-[2px] transition-all font-label-lg">
                        {{ __('Delete Account') }}
                    </button>

                    <!-- Delete Account Modal -->
                    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
                        <div @click.away="showDeleteModal = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-surface rounded-xl border-2 border-on-background shadow-[8px_8px_0px_0px_rgba(28,27,27,1)] p-8 max-w-md w-full rotate-[-1deg]">
                            <h3 class="font-headline-md text-2xl text-on-surface mb-4">{{ __('Are you sure you want to delete your account?') }}</h3>
                            <p class="font-body-md text-on-surface-variant mb-6">{{ __('Please enter your password to confirm you would like to permanently delete your account.') }}</p>
                            
                            <form wire:submit.prevent="deleteAccount" class="space-y-4">
                                <div>
                                    <input type="password" wire:model="delete_password" placeholder="{{ __('Password') }}" class="w-full bg-surface border-2 border-on-background rounded-md px-4 py-2 text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary">
                                    @error('delete_password') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex items-center gap-4 justify-end mt-6">
                                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2 text-on-surface-variant hover:text-on-surface transition-colors font-label-lg">{{ __('Cancel') }}</button>
                                    <button type="submit" class="px-6 py-2 bg-error text-on-error border-2 border-on-background shadow-[2px_2px_0px_0px_rgba(28,27,27,1)] rounded-md hover:translate-y-[1px] hover:translate-x-[1px] hover:shadow-[1px_1px_0px_0px_rgba(28,27,27,1)] active:shadow-none active:translate-y-[2px] active:translate-x-[2px] transition-all font-label-lg">
                                        {{ __('Delete Account') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endauth
</div>
