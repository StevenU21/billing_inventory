@props(['resource' => null])

<div
    class="w-full overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700">

    {{-- Optional Header Slot --}}
    @if(isset($header))
        {{ $header }}
    @endif

    {{-- Optional Info Panel Slot --}}
    @if(isset($info))
        {{ $info }}
    @endif

    <div class="w-full overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700">
                <tr class="text-xs font-medium text-gray-400 uppercase tracking-wider">
                    {{ $thead }}
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-transparent">
                {{ $tbody }}
            </tbody>
        </table>
    </div>

    @if($resource && method_exists($resource, 'links'))
        <div class="mt-4 px-4 pb-4">
            {{ $resource->links() }}
        </div>
    @endif
</div>