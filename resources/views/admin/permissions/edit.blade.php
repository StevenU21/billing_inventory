@extends('layouts.app')
@section('title', 'Asignar Permisos a Usuario')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Administración', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Usuarios', 'href' => route('users.index'), 'icon' => 'fa-users'],
        ]" :current="'Asignar Permisos'" />

        <x-page-header
            :title="'Asignar Permisos a: ' . $user->first_name . ' ' . $user->last_name"
            subtitle="Gestiona permisos heredados y directos del usuario."
            icon="fa-user-shield">
            <x-page-header.link :href="route('users.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <x-session-message />

        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            {{-- Permisos heredados por roles --}}
            <div class="mb-6">
                <span class="text-gray-700 dark:text-gray-400 font-semibold">Permisos heredados por roles</span>
                
                @if (count($rolePermissionGroups))
                    <div class="mt-4 space-y-6">
                        @foreach ($rolePermissionGroups as $group)
                            <fieldset class="border border-gray-200 dark:border-gray-700 rounded-md p-4 bg-gray-50 dark:bg-gray-900/30">
                                <legend class="px-2 text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    {{ $group['title'] }}
                                </legend>
                                <div class="flex flex-wrap -mx-2">
                                    @foreach ($group['permissions'] as $perm)
                                        <div class="w-1/2 md:w-1/3 lg:w-1/4 px-2 py-1">
                                            <div class="flex items-center space-x-2 text-xs md:text-sm font-semibold text-gray-400 dark:text-gray-500 opacity-80">
                                                <input type="checkbox" checked disabled class="form-checkbox h-4 w-4 text-purple-600 cursor-not-allowed">
                                                <span class="uppercase leading-snug">{{ $perm['label'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                @else
                    <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">Sin permisos heredados</p>
                @endif
            </div>

            {{-- Permisos especiales (directos) --}}
            <form action="{{ route('users.permissions.assign', $user) }}" method="POST" class="w-full">
                @csrf
                
                <div class="mt-6">
                    <span class="text-gray-700 dark:text-gray-400 font-semibold">Permisos especiales (directos)</span>

                    @if (count($permissionGroups))
                        {{-- Buscador --}}
                        <div class="mt-2 mb-4 relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-search"></i>
                            </span>
                            <input id="permission-filter" type="text" placeholder="Filtrar permisos..."
                                class="w-full pl-10 pr-9 py-2 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-purple-600">
                            <button type="button" id="permission-filter-clear" aria-label="Limpiar filtro"
                                class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none">
                                <i class="fas fa-times-circle text-sm"></i>
                            </button>
                        </div>

                        {{-- Grupos de permisos --}}
                        <div class="space-y-6" id="permission-groups-wrapper">
                            @foreach ($permissionGroups as $group)
                                <fieldset data-permission-group class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                                    <legend class="px-2 text-sm font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wide">
                                        {{ $group['title'] }}
                                    </legend>
                                    <div class="flex flex-wrap -mx-2">
                                        @foreach ($group['permissions'] as $perm)
                                            <div class="w-1/2 md:w-1/3 lg:w-1/4 px-2 py-1">
                                                <x-inputs.checkbox 
                                                    name="permission_ids[]" 
                                                    :value="$perm['id']"
                                                    :checked="$perm['checked']"
                                                    :id="'permission_' . $perm['id']"
                                                    data-permission-label="{{ $perm['labelSearch'] }}"
                                                    class="space-x-2"
                                                >
                                                    <span class="uppercase leading-snug text-xs md:text-sm font-semibold">{{ $perm['label'] }}</span>
                                                </x-inputs.checkbox>
                                            </div>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>

                        {{-- Botón de guardar --}}
                        <div class="mt-6">
                            <x-inputs.button type="submit" variant="primary" icon="fas fa-paper-plane">
                                Guardar
                            </x-inputs.button>
                        </div>

                        @push('scripts')
                            <script>
                                (function () {
                                    const input = document.getElementById('permission-filter');
                                    const clearBtn = document.getElementById('permission-filter-clear');
                                    if (!input) return;
                                    
                                    function applyFilter() {
                                        const term = input.value.trim().toLowerCase();
                                        if (clearBtn) {
                                            if (term.length) clearBtn.classList.remove('hidden');
                                            else clearBtn.classList.add('hidden');
                                        }
                                        const groups = document.querySelectorAll('#permission-groups-wrapper [data-permission-group]');
                                        groups.forEach(groupEl => {
                                            let anyVisibleInGroup = false;
                                            const items = groupEl.querySelectorAll('[data-permission-label]');
                                            items.forEach(checkboxDiv => {
                                                const text = checkboxDiv.getAttribute('data-permission-label');
                                                const container = checkboxDiv.closest('.w-1\\/2, .md\\:w-1\\/3, .lg\\:w-1\\/4') || checkboxDiv.parentElement;
                                                if (!term || text.includes(term)) {
                                                    container.classList.remove('hidden');
                                                    anyVisibleInGroup = true;
                                                } else {
                                                    container.classList.add('hidden');
                                                }
                                            });
                                            groupEl.classList.toggle('hidden', !anyVisibleInGroup);
                                        });
                                    }
                                    
                                    input.addEventListener('input', applyFilter);
                                    if (clearBtn) {
                                        clearBtn.addEventListener('click', () => { input.value = ''; applyFilter(); input.focus(); });
                                    }
                                })();
                            </script>
                        @endpush
                    @else
                        <p class="mt-2 text-sm text-gray-400 dark:text-gray-500 italic">
                            No hay permisos especiales disponibles para asignar.
                        </p>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
