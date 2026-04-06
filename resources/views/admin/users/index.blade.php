@extends('layouts.app')
@section('title', 'Usuarios')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Administración', 'href' => '#', 'icon' => 'fa-home'],
        ]" :current="'Usuarios'" />

        <!-- Page header card -->
        <x-page-header title="Usuarios" subtitle="Administra cuentas, estados y permisos." icon="fa-users"
            :action-href="route('users.create')" action-label="Crear usuario" action-permission="create users">
        </x-page-header>
        <!-- End Page Header -->

        <!-- Success Messages -->
        <div class="mt-4">
            <x-session-message />
        </div>
        <!-- End Success Messages -->

        {{-- Filtros, búsqueda --}}
        <x-filter-card action="{{ route('users.index') }}">
            {{-- Buscar Cliente --}}
            <div class="col-span-12 lg:col-span-3">
                <label for="search" class="block text-xs font-medium text-gray-400 mb-1">
                    Buscar Cliente
                </label>
                <x-autocomplete name="filter[search]" :value="request('filter.search')"
                    url="{{ route('users.autocomplete') }}" placeholder="Nombre, cédula..." id="search" />
            </div>

            {{-- Rol --}}
            <x-filter-card.select name="filter[role]" label="Rol" placeholder="Todos" :selected="request('filter.role')"
                class="col-span-6 lg:col-span-2">
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" {{ request('filter.role') == $role->name ? 'selected' : '' }}>
                        {{ mb_strtoupper($role->name) }}
                    </option>
                @endforeach
            </x-filter-card.select>

            {{-- Estado --}}
            <x-filter-card.select name="filter[status]" label="Estado" placeholder="Todos"
                :selected="request('filter.status')" class="col-span-6 lg:col-span-2">
                <option value="activo" {{ request('filter.status') == 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ request('filter.status') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </x-filter-card.select>

            {{-- Género --}}
            <x-filter-card.select name="filter[gender]" label="Género" placeholder="Todos"
                :selected="request('filter.gender')" class="col-span-6 lg:col-span-2">
                <option value="male" {{ request('filter.gender') == 'male' ? 'selected' : '' }}>Masculino</option>
                <option value="female" {{ request('filter.gender') == 'female' ? 'selected' : '' }}>Femenino</option>
            </x-filter-card.select>

            <div class="col-span-12 lg:col-span-1">
                <x-inputs.button type="submit" variant="secondary" icon="fas fa-search" icon-only
                    class="w-full h-[38px] mt-1" title="Filtrar Resultados" />
            </div>
        </x-filter-card>

        <x-table :resource="$users">
            <x-slot name="thead">
                <x-table.th>Folio</x-table.th>
                <x-table.th>Nombre</x-table.th>
                <x-table.th>Cédula</x-table.th>
                <x-table.th>Teléfono</x-table.th>
                <x-table.th>Correo</x-table.th>
                <x-table.th>Rol</x-table.th>
                <x-table.th>Fecha</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>
            <x-slot name="tbody">
                @forelse($users as $user)
                    <x-table.tr>
                        <x-table.td-folio :id="$user->id" />

                        <x-table.td-text variant="highlight">
                            {{ $user->full_name }}
                        </x-table.td-text>

                        <x-table.td-text variant="highlight" font="mono">
                            {{ $user->profile->formatted_identity_card ?? '-' }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm" font="mono">
                            {{ $user->profile->formatted_phone ?? '-' }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $user->email }}
                        </x-table.td-text>

                        <x-table.td-badge :color="$user->roles->count() ? 'blue' : 'gray'" :text="$user->formatted_role_name ?? 'Sin rol'" />

                        <x-table.td-text variant="muted" size="sm">
                            {{ $user->formatted_created_at }}
                        </x-table.td-text>

                        <x-table.dropdown-actions>
                            <x-table.dropdown-action-item :href="route('users.show', $user)" icon="fa-eye">
                                Ver
                            </x-table.dropdown-action-item>

                            <x-table.dropdown-action-item :href="route('users.edit', $user)" icon="fa-edit">
                                Editar
                            </x-table.dropdown-action-item>

                            @if ($user->is_active)
                                <x-table.dropdown-action-item :href="route('users.permissions.edit', $user)" icon="fa-user-shield">
                                    Permisos
                                </x-table.dropdown-action-item>
                            @endif

                            <x-table.dropdown-action-delete :action="route('users.destroy', $user)" :message="$user->is_active ? '¿Estás seguro de desactivar este usuario?' : '¿Estás seguro de reactivar este usuario?'"
                                :title="$user->is_active ? 'Desactivar' : 'Reactivar'" :icon="$user->is_active ? 'fa-user-slash' : 'fa-user-check'" />
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="9" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-users fa-3x mb-4 text-gray-600"></i>
                                <p class="text-lg font-medium">No se encontraron usuarios</p>
                                <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection