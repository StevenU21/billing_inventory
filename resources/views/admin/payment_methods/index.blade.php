@extends('layouts.app')
@section('title', 'Métodos de Pago')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
        ]" :current="'Métodos de Pago'" />

        <x-page-header title="Métodos de Pago" subtitle="Administra las formas de pago disponibles." icon="fa-credit-card">
            @can('create payment_methods')
                <x-page-header.link :href="route('payment_methods.create')" icon="fas fa-plus">
                    Crear Método de Pago
                </x-page-header.link>
            @endcan
        </x-page-header>

        <div class="mt-4">
            <x-session-message />
        </div>

        <x-filter-card :action="route('payment_methods.index')">
            <div class="col-span-full">
                <x-filter-card.search />
            </div>
            <div class="col-span-1 sm:col-span-1 lg:col-span-1">
                <x-filter-card.select name="per_page" label="Mostrar" :options="[
            '5' => '5',
            '10' => '10',
            '25' => '25',
            '50' => '50',
            '100' => '100',
        ]" :selected="request('per_page', 10)" :auto-submit="true" />
            </div>
        </x-filter-card>

        <x-table :resource="$paymentMethods">
            <x-slot name="thead">
                <x-table.th>Folio</x-table.th>
                <x-table.th>Nombre</x-table.th>
                <x-table.th>Descripción</x-table.th>
                <x-table.th>Es efectivo</x-table.th>
                <x-table.th>Está activo</x-table.th>
                <x-table.th>Fecha de Registro</x-table.th>
                <x-table.th>Fecha de Actualización</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>
            <x-slot name="tbody">
                @forelse($paymentMethods as $paymentMethod)
                    <x-table.tr>
                        <x-table.td-folio :id="$paymentMethod->id" />

                        <x-table.td-text variant="highlight">
                            {{ $paymentMethod->name }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $paymentMethod->description ?? '-' }}
                        </x-table.td-text>
                        
                        <x-table.td>
                            @if($paymentMethod->is_cash)
                                <span class="px-2 py-1 text-xs font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                    <i class="fas fa-money-bill-wave mr-1"></i> Sí
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold leading-tight text-gray-700 bg-gray-100 rounded-full dark:text-gray-100 dark:bg-gray-700">
                                    <i class="fas fa-credit-card mr-1"></i> No
                                </span>
                            @endif
                        </x-table.td>

                        <x-table.td>
                            @if($paymentMethod->is_active)
                                <span class="px-2 py-1 text-xs font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                    <i class="fas fa-check-circle mr-1"></i> Activo
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold leading-tight text-red-700 bg-red-100 rounded-full dark:bg-red-700 dark:text-red-100">
                                    <i class="fas fa-times-circle mr-1"></i> Inactivo
                                </span>
                            @endif
                        </x-table.td>
                        
                        <x-table.td-text variant="muted" size="sm">
                            {{ $paymentMethod->formatted_created_at }}
                        </x-table.td-text>

                        <x-table.td-text variant="muted" size="sm">
                            {{ $paymentMethod->formatted_updated_at }}
                        </x-table.td-text>
                        
                        <x-table.dropdown-actions :delete-url="route('payment_methods.destroy', $paymentMethod)"
                                delete-message="¿Estás seguro de eliminar este método de pago?">
                                @can('read payment_methods')
                                    <x-table.dropdown-action-item :href="route('payment_methods.show', $paymentMethod)"
                                        icon="fa-eye">
                                        Ver
                                    </x-table.dropdown-action-item>
                                @endcan
                                @can('update payment_methods')
                                    <x-table.dropdown-action-item :href="route('payment_methods.edit', $paymentMethod)"
                                        icon="fa-edit">
                                        Editar
                                    </x-table.dropdown-action-item>
                                @endcan
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <td colspan="8" class="px-4 py-3 text-center text-gray-400 dark:text-gray-500">No se encontraron
                            métodos de pago.</td>
                    </x-table.tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection