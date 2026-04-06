@props(['name'])
<div class="p-6" x-show="tab==='{{ $name }}'">
    {{ $slot }}
</div>
