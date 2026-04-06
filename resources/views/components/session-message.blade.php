<div>
    @if ($errors->any())
        <div x-data="{ show: true }" x-show="show"
            class="relative mb-4 text-sm font-medium text-red-700 bg-red-100 rounded-lg dark:bg-red-700 dark:text-red-100 px-4 py-3 flex items-center justify-between">
            <div>
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button @click="show = false"
                class="text-red-700 dark:text-red-100 hover:text-red-900 dark:hover:text-red-300 focus:outline-none transition-colors duration-150">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show"
            class="relative mb-4 text-sm font-medium text-green-700 bg-green-100 rounded-lg dark:bg-green-700 dark:text-green-100 px-4 py-3 flex items-center justify-between">
            <div>
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
            <button @click="show = false"
                class="text-green-700 dark:text-green-100 hover:text-green-900 dark:hover:text-green-300 focus:outline-none transition-colors duration-150">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('updated'))
        <div x-data="{ show: true }" x-show="show"
            class="relative mb-4 text-sm font-medium text-green-700 bg-green-100 rounded-lg dark:bg-green-700 dark:text-green-100 px-4 py-3 flex items-center justify-between">
            <div>
                <i class="fas fa-info-circle mr-2"></i> {{ session('updated') }}
            </div>
            <button @click="show = false"
                class="text-green-700 dark:text-green-100 hover:text-green-900 dark:hover:text-green-300 focus:outline-none transition-colors duration-150">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('deleted'))
        <div x-data="{ show: true }" x-show="show"
            class="relative mb-4 text-sm font-medium text-red-700 bg-red-100 rounded-lg dark:bg-red-700 dark:text-red-100 px-4 py-3 flex items-center justify-between">
            <div>
                <i class="fas fa-times-circle mr-2"></i> {{ session('deleted') }}
            </div>
            <button @click="show = false"
                class="text-red-700 dark:text-red-100 hover:text-red-900 dark:hover:text-red-300 focus:outline-none transition-colors duration-150">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show"
            class="relative mb-4 text-sm font-medium text-red-700 bg-red-100 rounded-lg dark:bg-red-700 dark:text-red-100 px-4 py-3 flex items-center justify-between">
            <div>
                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            </div>
            <button @click="show = false"
                class="text-red-700 dark:text-red-100 hover:text-red-900 dark:hover:text-red-300 focus:outline-none transition-colors duration-150">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
</div>
