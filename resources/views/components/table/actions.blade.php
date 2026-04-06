@props(['showUrl' => null, 'editUrl' => null, 'deleteUrl' => null, 'deleteMessage' => '¿Estás seguro?', 'deleteIcon' => 'fa-trash', 'deleteTitle' => 'Eliminar'])

<td class="px-4 py-3 whitespace-nowrap text-center">
    <div class="flex items-center justify-center gap-3">
        {{ $slot }}

        @if($showUrl)
            <a href="{{ $showUrl }}" title="Ver" class="p-1 text-gray-300 hover:text-purple-400 transition-colors"
                aria-label="Ver">
                <i class="fas fa-eye"></i>
            </a>
        @endif

        @if($editUrl)
            <a href="{{ $editUrl }}" title="Editar" class="p-1 text-gray-300 hover:text-purple-400 transition-colors"
                aria-label="Editar">
                <i class="fas fa-edit"></i>
            </a>
        @endif

        @if($deleteUrl)
            <form action="{{ $deleteUrl }}" method="POST" class="inline" onsubmit="return confirm('{{ $deleteMessage }}');">
                @csrf
                @method('DELETE')
                <button type="submit" title="{{ $deleteTitle }}"
                    class="p-1 text-gray-500 hover:text-red-400 transition-colors" aria-label="{{ $deleteTitle }}">
                    <i class="fas {{ $deleteIcon }}"></i>
                </button>
            </form>
        @endif
    </div>
</td>