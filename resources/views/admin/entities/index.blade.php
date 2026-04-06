@extends('layouts.app')
@section('title', 'Clientes y Proveedores')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Compras', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Clientes y Proveedores'],
        ]" />
        
        <x-page-header title="Clientes y Proveedores" subtitle="Gestiona, busca y organiza tus entidades." icon="fa-users"
            :action-href="route('entities.create')" action-label="Nuevo Registro">
        </x-page-header>

        <div class="mt-4">
            <x-session-message />
        </div>

        <x-filter-card action="{{ route('entities.index') }}">
            @if (request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <div class="col-span-12 lg:col-span-3">
                <label for="search" class="block text-xs font-medium text-gray-400 mb-1">Buscar</label>
                <x-autocomplete name="filter[search]" :value="request('filter.search')" url="{{ route('entities.autocomplete') }}"
                    placeholder="Nombre del registro..." id="search" />
            </div>

            @if (auth()->user()->can('read suppliers') && auth()->user()->can('read clients'))
                <x-filter-card.select name="filter[entity_type]" label="Tipo de entidad" :options="[
                    '' => 'Todos',
                    'clients' => 'Solo Clientes',
                    'suppliers' => 'Solo Proveedores',
                    'both' => 'Ambos',
                ]" :selected="request('filter.entity_type')"
                    placeholder="Tipo de entidad" class="col-span-12 lg:col-span-2" />
            @endif

            <x-filter-card.select name="filter[department_id]" label="Departamento" :options="$departments"
                :selected="request('filter.department_id')" placeholder="Departamento" class="col-span-12 lg:col-span-2" />

            <x-filter-card.select name="filter[municipality_id]" label="Municipio" :options="$municipalities"
                :selected="request('filter.municipality_id')" placeholder="Municipio" class="col-span-12 lg:col-span-2" />

            <x-filter-card.select name="filter[is_active]" label="Activo" :options="['1' => 'Sí', '0' => 'No']" :selected="request('filter.is_active', 1)"
                placeholder="Activo?" class="col-span-6 lg:col-span-1" />

            <div class="col-span-12 lg:col-span-1">
                <x-inputs.button type="submit" variant="secondary" icon="fas fa-search" icon-only
                    class="w-full h-[38px] mt-1" title="Filtrar Resultados" />
            </div>

            @if (request()->filled('filter') || request()->filled('per_page'))
                <div class="col-span-6 lg:col-span-1">
                    <a href="{{ route('entities.index') }}"
                        class="w-full h-[38px] mt-1 inline-flex items-center justify-center text-sm font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200"
                        title="Limpiar filtros">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            @endif
        </x-filter-card>

        <div class="mt-4">
            <x-table :resource="$entities">
                <x-slot name="thead">
                    <x-table.th>Folio</x-table.th>
                    <x-table.th>Entidad / Razón Social</x-table.th>
                    <x-table.th>Identificación</x-table.th>
                    <x-table.th>Datos de Contacto</x-table.th>
                    <x-table.th>Ubicación</x-table.th>
                    <x-table.th>Roles</x-table.th>
                    <x-table.th>Estado</x-table.th>
                    <x-table.th>Acciones</x-table.th>
                </x-slot>
                <x-slot name="tbody">
                    @foreach ($entities as $entity)
                        <x-table.tr>
                            <x-table.td-folio :id="$entity->id" />

                            <x-table.td-text variant="highlight" size="base">
                                {{ $entity->full_name }}
                            </x-table.td-text>

                            <x-table.td-stacked
                                :top="$entity->formatted_identification['primary'] ?: null"
                                :middle="$entity->formatted_identification['secondary'] ?: null"
                                top-class="font-mono text-gray-200"
                                middle-class="font-mono"
                            >
                                @if (!$entity->formatted_identification['primary'] && !$entity->formatted_identification['secondary'])
                                    <span class="text-sm text-gray-500 dark:text-gray-400 font-sans">-</span>
                                @endif
                            </x-table.td-stacked>

                            <x-table.td-stacked>
                                <x-slot:top>
                                    @if ($entity->formatted_phone)
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-phone text-xs text-gray-400 dark:text-gray-500"></i>
                                            <span>{{ $entity->formatted_phone }}</span>
                                        </div>
                                    @endif
                                </x-slot:top>
                                
                                <x-slot:middle>
                                    @if ($entity->email)
                                        <div class="flex items-center gap-2 break-all">
                                            <i class="fas fa-envelope text-xs text-gray-400 dark:text-gray-500"></i>
                                            <span>{{ $entity->email }}</span>
                                        </div>
                                    @endif
                                </x-slot:middle>

                                @if (!$entity->formatted_phone && !$entity->email)
                                    <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                @endif
                            </x-table.td-stacked>

                            <x-table.td-stacked
                                :top="$entity->address ?: ($entity->formatted_location['municipality'] ?: '-')"
                                :middle="$entity->address && $entity->formatted_location['municipality'] 
                                    ? $entity->formatted_location['municipality'] . ($entity->formatted_location['show_department'] ? ', ' . $entity->formatted_location['department'] : '')
                                    : ($entity->formatted_location['show_department'] ? $entity->formatted_location['department'] : null)"
                                top-class="{{ $entity->address ? 'line-clamp-2 leading-snug' : '' }}"
                            />

                            <x-table.td>
                                @php
                                    $isBoth = $entity->is_client && $entity->is_supplier;
                                @endphp
                                
                                @if ($isBoth)
                                    <x-badge color="indigo" text="Mixto" />
                                @elseif ($entity->is_client)
                                    <x-badge color="blue" text="Cliente" />
                                @elseif ($entity->is_supplier)
                                    <x-badge color="purple" text="Proveedor" />
                                @else
                                    <span class="text-xs text-gray-500 dark:text-gray-400">-</span>
                                @endif
                            </x-table.td>

                            <x-table.td>
                                @if ($entity->is_active)
                                    <span class="inline-flex items-center justify-center text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-check text-sm"></i>
                                    </span>
                                @else
                                    <x-badge color="red" text="Inactivo" />
                                @endif
                            </x-table.td>

                            <x-table.dropdown-actions>
                                <x-table.dropdown-action-item :href="route('entities.show', $entity)" icon="fa-eye">
                                    Ver
                                </x-table.dropdown-action-item>

                                <x-table.dropdown-action-item :href="route('entities.edit', $entity)" icon="fa-edit">
                                    Editar
                                </x-table.dropdown-action-item>

                                <x-table.dropdown-action-delete 
                                    :action="route('entities.destroy', $entity)" 
                                    :message="$entity->is_active ? '¿Estás seguro de desactivar esta entidad?' : '¿Estás seguro de activar esta entidad?'" 
                                    :title="$entity->is_active ? 'Desactivar' : 'Activar'"
                                    :icon="$entity->is_active ? 'fa-user-slash' : 'fa-user-check'"
                                />
                            </x-table.dropdown-actions>
                        </x-table.tr>
                    @endforeach
                </x-slot>
            </x-table>
        </div>
    </div>
@endsection
