@props([
    'action', 
    'message' => '¿Estás seguro de eliminar este registro?', 
    'title' => 'Eliminar',
    'icon' => 'fa-trash',
    'can' => null
])

@if(is_null($can))
    @php $show = true; @endphp
@elseif(is_array($can))
    @php $show = auth()->user()?->can($can[0], $can[1] ?? []); @endphp
@else
    @php $show = auth()->user()?->can($can); @endphp
@endif

@if($show)
    <form action="{{ $action }}" method="POST" onsubmit="return confirm('{{ $message }}');" class="block w-full">
        @csrf
        @method('DELETE')
        <button type="submit"
            class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 transition-colors flex items-center gap-2 rounded-md"
            role="menuitem">
            <i class="fas {{ $icon }} w-4"></i>
            <span>{{ $title }}</span>
        </button>
        {{ $slot }}
    </form>
@endif
