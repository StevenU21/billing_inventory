@extends('layouts.app')

@section('title', 'Notificaciones')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Módulo de Administración', 'href' => route('dashboard.index'), 'icon' => 'fa-cogs'],
        ]" :current="'Notificaciones'" />

        <x-page-header title="Notificaciones"
            subtitle="Gestiona las notificaciones del sistema: alertas de inventario, respaldos, y más." icon="fa-bell">
            <x-page-header.form-button :action="route('notifications.markAll')" can="markAll"
                :canModel="\Illuminate\Notifications\DatabaseNotification::class" icon="fas fa-check-double"
                :disabled="auth()->user()->unreadNotifications->isEmpty()">
                Marcar todo leído
            </x-page-header.form-button>

            <x-page-header.form-button :action="route('notifications.destroyAll')" method="DELETE"
                can="destroy notifications" icon="fas fa-trash"
                confirm="¿Eliminar todas las notificaciones? Esta acción no se puede deshacer.">
                Eliminar todas
            </x-page-header.form-button>
        </x-page-header>

        <div class="mt-4">
            <x-session-message />
        </div>

        @php
            $showReset =
                (request()->input('filter.status') && request()->input('filter.status') !== 'all') ||
                (request()->input('filter.category') && request()->input('filter.category') !== 'all') ||
                (request()->has('per_page') && (int) request('per_page') !== 15);
        @endphp

        <x-filter-card action="{{ route('notifications.index') }}">

            <x-filter-card.select name="per_page" label="Mostrar">
                @foreach ([5, 10, 15, 25, 50, 100] as $size)
                    <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>
                        {{ $size }} por página
                    </option>
                @endforeach
            </x-filter-card.select>

            <x-filter-card.select name="filter[status]" label="Estado" :value="$filter">
                <option value="unread" {{ $filter === 'unread' ? 'selected' : '' }}>No leídas</option>
                <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>Todas</option>
                <option value="read" {{ $filter === 'read' ? 'selected' : '' }}>Leídas</option>
            </x-filter-card.select>

            <x-filter-card.select name="filter[category]" label="Categoría" :value="$category">
                <option value="all" {{ $category === 'all' ? 'selected' : '' }}>Todas las categorías</option>
                <option value="inventory" {{ $category === 'inventory' ? 'selected' : '' }}>Inventario</option>
                <option value="system" {{ $category === 'system' ? 'selected' : '' }}>Sistema</option>
                <option value="sales" {{ $category === 'sales' ? 'selected' : '' }}>Ventas</option>
                <option value="purchases" {{ $category === 'purchases' ? 'selected' : '' }}>Compras</option>
                <option value="general" {{ $category === 'general' ? 'selected' : '' }}>General</option>
            </x-filter-card.select>

            <div class="flex flex-row gap-2">
                @if ($showReset)
                    <x-link :href="route('notifications.index')" variant="secondary" icon="fas fa-undo">
                        Limpiar
                    </x-link>
                @endif
            </div>
        </x-filter-card>

        <x-table :resource="$notifications">
            <x-slot name="thead">
                <x-table.th icon="fa-bell">Notificación</x-table.th>
                <x-table.th icon="fa-tag" class="hidden lg:table-cell">Categoría</x-table.th>
                <x-table.th icon="fa-clock">Generado</x-table.th>
                <x-table.th icon="fa-tools">Acciones</x-table.th>
            </x-slot>
            <x-slot name="tbody">
                @forelse ($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $isUnread = $notification->read_at === null;
                        $type = $data['type'] ?? 'unknown';
                        $category = $data['category'] ?? 'general';
                        $icon = $data['icon'] ?? 'fa-bell';
                        $title = $data['title'] ?? 'Notificación';
                        $message = $data['message'] ?? 'Sin descripción disponible.';
                        $url = $data['url'] ?? null;

                        $categoryLabels = [
                            'inventory' => 'Inventario',
                            'system' => 'Sistema',
                            'sales' => 'Ventas',
                            'purchases' => 'Compras',
                            'general' => 'General',
                        ];
                        $categoryLabel = $categoryLabels[$category] ?? ucfirst($category);

                        $categoryColors = [
                            'inventory' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-200',
                            'system' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200',
                            'sales' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200',
                            'purchases' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-200',
                            'general' => 'bg-gray-100 text-gray-700 dark:bg-gray-900/40 dark:text-gray-200',
                        ];
                        $categoryColor = $categoryColors[$category] ?? $categoryColors['general'];
                    @endphp
                    <x-table.tr @class(['bg-purple-50/60 dark:bg-purple-900/20' => $isUnread])>
                        <x-table.td>
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                    <i class="fas {{ $icon }} text-purple-600 dark:text-purple-400"></i>
                                </div>
                                <div class="flex flex-col flex-1">
                                    <span class="font-semibold flex items-center gap-2">
                                        <span class="inline-flex h-2 w-2 rounded-full" @class([
                                            'bg-red-500 animate-pulse' => $isUnread,
                                            'bg-gray-400' => !$isUnread,
                                        ])></span>
                                        {{ $title }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $message }}
                                    </span>
                                </div>
                            </div>
                        </x-table.td>
                        <x-table.td class="hidden lg:table-cell">
                            <span
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold {{ $categoryColor }}">
                                <i class="fas {{ $icon }}"></i>
                                {{ $categoryLabel }}
                            </span>
                        </x-table.td>
                        <x-table.td class="text-sm text-gray-500">
                            {{ optional($notification->created_at)->format('d/m/Y H:i') }}
                        </x-table.td>
                        <x-table.td>
                            <div class="flex items-center gap-2">
                                @if($url)
                                    <x-link :href="$url" variant="action" icon="fas fa-eye" title="Ver detalle">
                                    </x-link>
                                @endif

                                <x-table.form-button :action="route('notifications.markAsRead', $notification->id)"
                                    method="PATCH" can="mark" :canModel="$notification" icon="fas fa-envelope-open-text"
                                    title="Marcar como leído" :disabled="!$isUnread">
                                </x-table.form-button>

                                <x-table.form-button :action="route('notifications.destroy', $notification->id)" method="DELETE"
                                    can="delete" :canModel="$notification" icon="fas fa-trash" title="Eliminar"
                                    confirm="¿Eliminar esta notificación?">
                                </x-table.form-button>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500 text-sm">
                            No se encontraron notificaciones con los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection