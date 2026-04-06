@props(['name', 'label' => null])
<button
    type="button"
    class="py-2 border-b-2"
    :class="tab==='{{ $name }}' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-gray-500'"
    @click="tab='{{ $name }}'"
>
    {{ $label ?? $slot }}

</button>
