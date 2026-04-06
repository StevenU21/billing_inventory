@props([
    'action',
    'method' => 'POST',
    'enctype' => null,
    'class' => '',
])

@php
    $formMethod = strtoupper($method);
    $spoofMethod = in_array($formMethod, ['PUT', 'PATCH', 'DELETE']) ? $formMethod : null;
    $actualMethod = $spoofMethod ? 'POST' : $formMethod;
@endphp

<form 
    action="{{ $action }}" 
    method="{{ $actualMethod }}"
    @if($enctype) enctype="{{ $enctype }}" @endif
    {{ $attributes->merge(['class' => "space-y-6 {$class}"]) }}
>
    @csrf
    
    @if($spoofMethod)
        @method($spoofMethod)
    @endif

    {{ $slot }}
</form>
