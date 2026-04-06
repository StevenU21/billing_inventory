@props(['maxWidth' => 'xl', 'title' => null, 'description' => null, 'showVar' => 'isModalOpen', 'onClose' => 'closeModal'])
@php
    // Responsive max-width presets that grow with screen size
    $sizes = [
        'sm' => 'sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl 2xl:max-w-2xl',
        'md' => 'sm:max-w-md md:max-w-lg lg:max-w-xl xl:max-w-2xl 2xl:max-w-3xl',
        'lg' => 'sm:max-w-lg md:max-w-xl lg:max-w-2xl xl:max-w-3xl 2xl:max-w-4xl',
        'xl' => 'sm:max-w-xl md:max-w-2xl lg:max-w-3xl xl:max-w-4xl 2xl:max-w-5xl',
        '2xl' => 'sm:max-w-2xl md:max-w-3xl lg:max-w-4xl xl:max-w-5xl 2xl:max-w-6xl',
        '3xl' => 'sm:max-w-3xl md:max-w-4xl lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl',
        'full' => 'sm:max-w-full',
    ];
    $maxWidthClass = $sizes[$maxWidth] ?? $sizes['xl'];
@endphp
<div x-cloak x-show="{{ $showVar }}" x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" {{ $attributes->merge(['class' => 'fixed inset-0 z-[70] flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center']) }}>
    <!-- Modal -->
    <div x-show="{{ $showVar }}" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 transform translate-y-1/2" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0  transform translate-y-1/2" @click.away="{{ $onClose }}"
        @keydown.escape="{{ $onClose }}"
        class="inline-block w-auto px-6 py-4 bg-white rounded-t-lg dark:bg-gray-800 sm:rounded-lg sm:m-4 {{ $maxWidthClass }} max-w-[95vw] min-w-[20rem] max-h-[90vh] overflow-y-auto overflow-x-auto"
        role="dialog" id="modal">
        <!-- Remove header if you don't want a close icon. Use modal body to place modal tile. -->
        <header class="flex justify-end">
            <button type="button"
                class="inline-flex items-center justify-center w-6 h-6 text-gray-400 transition-colors duration-150 rounded dark:hover:text-gray-200 hover: hover:text-gray-700"
                aria-label="close" @click="{{ $onClose }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" role="img" aria-hidden="true">
                    <path
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" fill-rule="evenodd"></path>
                </svg>
            </button>
        </header>
        <!-- Modal body -->
        <div class="mt-4 mb-6">
            <!-- Modal title -->
            <p class="mb-2 text-lg font-semibold text-gray-700 dark:text-gray-300">
                {{ $title ?? '' }}
            </p>
            <!-- Modal description -->
            <p class="text-sm text-gray-700 dark:text-gray-400">
                {{ $description ?? '' }}
            </p>
            <!-- Slot for custom content -->
            <div>
                {{ $slot }}
            </div>
        </div>
        <footer
            class="flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-6 sm:flex-row bg-gray-50 dark:bg-gray-800">
        </footer>
    </div>
</div>