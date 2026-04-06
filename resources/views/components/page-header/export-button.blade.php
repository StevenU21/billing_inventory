@props(['action', 'method' => 'GET', 'target' => null, 'icon' => null, 'params' => []])

<form method="{{ $method }}" action="{{ $action }}" class="flex">
    @if(strtoupper($method) !== 'GET')
        @csrf
        @if(strtoupper($method) !== 'POST')
            @method($method)
        @endif
    @endif

    @php
        $flatten = function ($array, $prefix = '') use (&$flatten) {
            $result = [];
            foreach ($array as $key => $value) {
                $new_key = $prefix ? "{$prefix}[{$key}]" : $key;
                if (is_array($value)) {
                    $result = array_merge($result, $flatten($value, $new_key));
                } else {
                    $result[$new_key] = $value;
                }
            }
            return $result;
        };
        $flatParams = $flatten($params);
    @endphp

    @foreach($flatParams as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <button type="submit" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 text-sm font-medium transition']) }}>
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </button>
</form>