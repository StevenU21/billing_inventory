@props([
    'action' => null,
    'method' => 'POST',
])

@php
    $method = strtoupper($method);
    $spoofMethod = in_array($method, ['PUT', 'PATCH', 'DELETE']) ? $method : null;
    $actualMethod = $spoofMethod ? 'POST' : $method;
    
    // Extract action from attributes if not provided as prop (for x-bind:action support)
    $formAction = $action;
    $remainingAttributes = $attributes->except(['action']);
@endphp

<form @if($formAction) action="{{ $formAction }}" @endif method="{{ $actualMethod }}" {{ $remainingAttributes }}>
    @csrf
    @if($spoofMethod)
        @method($spoofMethod)
    @endif

    <div>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="mt-6 flex flex-col items-center justify-end px-6 py-3 -mx-6 -mb-4 space-y-4 sm:space-y-0 sm:space-x-3 sm:flex-row bg-gray-50 dark:bg-gray-800 rounded-b-lg">
            {{ $footer }}
        </div>
    @endisset
</form>