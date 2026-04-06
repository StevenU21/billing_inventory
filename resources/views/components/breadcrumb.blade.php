@props(['items' => null, 'parents' => [], 'current' => null])

@php
    // Build items list: prefer explicit `items` prop, otherwise combine `parents` + `current`.
    $itemsToRender = [];
    if (!is_null($items) && is_array($items) && count($items)) {
        $itemsToRender = $items;
    } else {
        if (is_array($parents) && count($parents)) {
            foreach ($parents as $p) {
                $itemsToRender[] = $p;
            }
        }
        if (!empty($current)) {
            $itemsToRender[] = ['label' => $current];
        }
    }
@endphp

<nav class="mt-4 mb-2 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
    <ol class="flex items-center gap-2">
        @foreach($itemsToRender as $index => $item)
            <li class="flex items-center">
                @if(isset($item['href']))
                    <a href="{{ $item['href'] }}"
                        class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors inline-flex items-center">
                        @if(!empty($item['icon']))
                            <i class="fas {{ $item['icon'] }} mr-1 text-gray-400 dark:text-gray-500"></i>
                        @endif
                        {{ $item['label'] }}
                    </a>
                @else
                    <span
                        class="{{ $loop->last ? 'text-gray-700 dark:text-gray-200 inline-flex items-center' : 'text-gray-500 dark:text-gray-400 inline-flex items-center' }}">
                        @if(!empty($item['icon']))
                            <i class="fas {{ $item['icon'] }} mr-1 text-gray-400 dark:text-gray-500"></i>
                        @endif
                        {{ $item['label'] }}</span>
                @endif
            </li>
            @if(!$loop->last)
                <li class="text-gray-400">/</li>
            @endif
        @endforeach
    </ol>
</nav>