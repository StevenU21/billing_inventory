@extends('layouts.app')
@section('title', 'Categorías')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
        ]" :current="'Categorías'" />

        <x-page-header title="Categorías" subtitle="Gestiona, busca y organiza las categorías de productos." icon="fa-tags"
            :action-href="route('categories.create')" action-label="Crear categoría" action-permission="create categories">
        </x-page-header>

        <!-- Mensajes de éxito -->
        <div class="mt-4">
            <x-session-message />
        </div>
        <!-- Fin mensajes de éxito -->

        <x-filter-card :action="route('categories.index')">
            <div class="col-span-full">
                <x-filter-card.search />
            </div>
        </x-filter-card>

        <x-table :resource="$categories">
            <x-slot name="thead">
                <x-table.th>Folio</x-table.th>
                <x-table.th>Nombre</x-table.th>
                <x-table.th>Descripción</x-table.th>
                <x-table.th>Fecha de creación</x-table.th>
                <x-table.th>Fecha de actualización</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>
            <x-slot name="tbody">
                @forelse($categories as $category)
                    <x-table.tr>
                        <x-table.td-folio :id="$category->id" />

                        <x-table.td-text variant="highlight">
                            {{ $category->name }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $category->description ?? '-' }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $category->formatted_created_at }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $category->formatted_updated_at }}
                        </x-table.td-text>

                        <x-table.dropdown-actions>
                            @can('read categories')
                                <x-table.dropdown-action-item :href="route('categories.show', $category)" icon="fa-eye">
                                    Ver
                                </x-table.dropdown-action-item>
                            @endcan
                            @can('update categories')
                                <x-table.dropdown-action-item :href="route('categories.edit', $category)" icon="fa-edit">
                                    Editar
                                </x-table.dropdown-action-item>
                            @endcan

                            <x-table.dropdown-action-delete :action="route('categories.destroy', $category)"
                                message="¿Estás seguro de eliminar esta categoría?" />
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="6" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-tags fa-3x mb-4 text-gray-600"></i>
                                <p class="text-lg font-medium">No se encontraron categorías</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection