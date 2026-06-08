<div class="space-y-8 animate-pulse">
    <div>
        <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-1/4 mb-4"></div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @for ($i = 0; $i < 6; $i++)
                <div class="bg-gray-200 dark:bg-gray-800 rounded-lg aspect-square"></div>
            @endfor
        </div>
    </div>
    
    <div>
        <div class="h-8 bg-gray-300 dark:bg-gray-700 rounded w-1/4 mb-4"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @for ($i = 0; $i < 6; $i++)
                <div class="h-16 bg-gray-200 dark:bg-gray-800 rounded-lg"></div>
            @endfor
        </div>
    </div>
</div>
