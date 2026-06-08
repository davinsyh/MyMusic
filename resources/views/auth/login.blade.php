<x-layouts.app>
    <div class="max-w-md mx-auto mt-12 bg-surface dark:bg-gray-800 p-8 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-900 dark:text-white">Sign in to MusicStream</h2>
        
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            
            <div>
                <label for="login" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email or Username</label>
                <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
                @error('login')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                <input id="password" type="password" name="password" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" type="checkbox" name="remember" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700">
                    <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">{{ __('Remember me') }}</label>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    Sign in
                </button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
            Don't have an account? 
            <a href="{{ route('register') }}" class="font-medium text-green-600 hover:text-green-500 dark:text-green-400">Sign up</a>
        </p>
    </div>
</x-layouts.app>
