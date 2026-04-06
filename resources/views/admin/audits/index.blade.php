@extends('layouts.app')
@section('title', 'Auditoría')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Administración', 'href' => '#', 'icon' => 'fa-cogs'],
        ]" :current="'Bitácora'" />

        <x-page-header title="Bitácora de Registros" subtitle="Historial de actividades y cambios en el sistema."
            icon="fa-clipboard-list">
        </x-page-header>

        <!-- Success Messages -->
        <div class="mt-4">
            <x-session-message />
        </div>

        <!-- Filtros, búsqueda -->
        <x-filter-card action="{{ route('audits.index') }}">

            <x-filter-card.select name="filter[causer_id]" label="Usuario" placeholder="Todos los usuarios" colspan="2">
                @foreach ($allCausers as $causer)
                    <option value="{{ $causer->id }}" {{ request('filter.causer_id') == $causer->id ? 'selected' : '' }}>
                        {{ $causer->first_name }} {{ $causer->last_name }}
                    </option>
                @endforeach
            </x-filter-card.select>

            <x-filter-card.select name="filter[event]" label="Evento" placeholder="Todos los eventos">
                <option value="created" {{ request('filter.event') == 'created' ? 'selected' : '' }}>{{ __('activity-presenter::logs.events.created') }}</option>
                <option value="updated" {{ request('filter.event') == 'updated' ? 'selected' : '' }}>{{ __('activity-presenter::logs.events.updated') }}</option>
                <option value="deleted" {{ request('filter.event') == 'deleted' ? 'selected' : '' }}>{{ __('activity-presenter::logs.events.deleted') }}</option>
            </x-filter-card.select>

            <x-filter-card.select name="filter[subject_type]" label="Registro" placeholder="Todos los registros">
                @foreach ($modelOptions as $option)
                    <option value="{{ $option['value'] }}" {{ request('filter.subject_type') == $option['value'] ? 'selected' : '' }}>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </x-filter-card.select>

            <x-filter-card.select name="filter[range]" label="Rango" placeholder="Rango de tiempo">
                <option value="hoy" {{ request('filter.range') == 'hoy' ? 'selected' : '' }}>Hoy</option>
                <option value="semana" {{ request('filter.range') == 'semana' ? 'selected' : '' }}>Esta semana</option>
                <option value="mes" {{ request('filter.range') == 'mes' ? 'selected' : '' }}>Este mes</option>
                <option value="historico" {{ request('filter.range') == 'historico' ? 'selected' : '' }}>Histórico</option>
            </x-filter-card.select>
        </x-filter-card>

        <x-table :resource="$activities">
            <x-slot name="thead">
                <x-table.th icon="fa-hashtag">Folio</x-table.th>
                <x-table.th icon="fa-user">Usuario</x-table.th>
                <x-table.th icon="fa-bolt">Evento</x-table.th>
                <x-table.th icon="fa-cube">Registro</x-table.th>
                <x-table.th icon="fa-id-card">Nombre Registro</x-table.th>
                <x-table.th icon="fa-calendar-alt">Fecha de Actualización</x-table.th>
                <x-table.th icon="fa-tools">Acciones</x-table.th>
            </x-slot>
            <x-slot name="tbody">
                @foreach ($activities as $activity)
                    <x-table.tr>
                        <x-table.td-folio :id="$activity->id" />

                        <x-table.td-text variant="highlight">
                            {{ $activity->userName }}
                        </x-table.td-text>

                        <x-table.td-text>
                             {{-- Podríamos usar badges aquí si tuviéramos lógica de color --}}
                            {{ $activity->event }}
                        </x-table.td-text>

                        <x-table.td-text font="mono" size="xs">
                            {{ $activity->subjectType }}
                        </x-table.td-text>

                        <x-table.td-stacked
                            :top="$activity->subjectName"
                            :middle="'Cambios: ' . $activity->count"
                            :route="$activity->showUrl"
                        />

                        <x-table.td-text variant="muted" size="sm">
                            {{ $activity->date }}
                        </x-table.td-text>

                        <x-table.td>
                            @if ($activity->showUrl)
                                <x-link :href="$activity->showUrl" variant="action" icon="fas fa-eye" title="Ver detalles">
                                </x-link>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </x-table.td>
                    </x-table.tr>
                @endforeach
            </x-slot>
        </x-table>
    </div>
@endsection
