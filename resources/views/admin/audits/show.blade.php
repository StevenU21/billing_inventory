@extends('layouts.app')
@section('title', 'Auditoría - Historia del registro')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Administración', 'href' => '#', 'icon' => 'fa-cogs'],
            ['label' => 'Bitácora', 'href' => route('audits.index'), 'icon' => 'fa-clipboard-list'],
        ]" :current="'Historia del registro'" />

        <x-page-header title="Historia del registro" :subtitle="$subjectLabel . ' · ID ' . $subjectId" icon="fa-history">

            <x-page-header.link :href="route('audits.index')" icon="fas fa-arrow-left">
                {{ __('Volver') }}
            </x-page-header.link>
        </x-page-header>

        <div class="mt-4 w-full overflow-hidden rounded-2xl shadow-lg bg-white dark:bg-gray-800">
            <div
                class="border-b border-gray-100 dark:border-gray-700 px-6 py-4 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('Actividades registradas') }}</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $activities->count() }}</p>
                </div>

                <form action="{{ url()->current() }}" method="GET" class="flex flex-wrap items-end gap-2">
                    <div>
                        <label for="event"
                            class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Evento</label>
                        <select name="filter[event]" id="event" onchange="this.form.submit()"
                            class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Todos</option>
                            <option value="created" {{ request('filter.event') == 'created' ? 'selected' : '' }}>
                                {{ __('activity-presenter::logs.events.created') }}
                            </option>
                            <option value="updated" {{ request('filter.event') == 'updated' ? 'selected' : '' }}>
                                {{ __('activity-presenter::logs.events.updated') }}
                            </option>
                            <option value="deleted" {{ request('filter.event') == 'deleted' ? 'selected' : '' }}>
                                {{ __('activity-presenter::logs.events.deleted') }}
                            </option>
                            <option value="restored" {{ request('filter.event') == 'restored' ? 'selected' : '' }}>
                                {{ __('activity-presenter::logs.events.restored') }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="from"
                            class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Desde</label>
                        <input type="date" name="filter[start_date]" id="from" value="{{ request('filter.start_date') }}"
                            onchange="this.form.submit()"
                            class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label for="to"
                            class="block text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Hasta</label>
                        <input type="date" name="filter[end_date]" id="to" value="{{ request('filter.end_date') }}"
                            onchange="this.form.submit()"
                            class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    @if (request()->hasAny(['filter.event', 'filter.start_date', 'filter.end_date']))
                        <a href="{{ url()->current() }}"
                            class="px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>

            <div class="p-4 sm:p-6">
                @if ($activities->isEmpty())
                    <div class="text-center text-gray-500 dark:text-gray-400 py-10">
                        {{ __('No hay cambios registrados para este elemento.') }}
                    </div>
                @else
                    <ul class="space-y-4">
                        @foreach ($activities as $activity)
                            <li class="rounded-xl border border-gray-100 dark:border-gray-700 p-4 bg-white dark:bg-gray-900">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div
                                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-200">
                                            <i class="fas fa-bolt"></i>
                                            {{ strtoupper($activity->event) }}
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('Usuario: :user', ['user' => $activity->userName ?: __('Sistema')]) }}
                                        </p>
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ $activity->date }}
                                    </div>
                                </div>

                                <div class="mt-4 overflow-x-auto rounded-lg border border-gray-100 dark:border-gray-700">
                                    <table class="w-full text-sm text-left">
                                        <thead
                                            class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                                            <tr>
                                                <th class="px-4 py-2 font-medium">{{ __('Campo') }}</th>
                                                <th class="px-4 py-2 font-medium">{{ __('Antes') }}</th>
                                                <th class="px-4 py-2 font-medium">{{ __('Después') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                            @forelse($activity->changes as $change)
                                                <x-table.tr>
                                                    <td class="px-4 py-2 font-medium text-gray-700 dark:text-gray-300">
                                                        {{ $change->label }}
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-red-600 dark:text-red-400 bg-red-50/30 dark:bg-red-900/10">
                                                        {{ $change->old }}
                                                    </td>
                                                    <td
                                                        class="px-4 py-2 text-green-600 dark:text-green-400 bg-green-50/30 dark:bg-green-900/10">
                                                        {{ $change->new }}
                                                    </td>
                                                </x-table.tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                                        {{ __('Sin detalles de cambios') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection