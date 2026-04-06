@extends('layouts.app')
@section('title', 'Backups')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 mx-auto">
        <x-breadcrumb :parents="[
            ['label' => 'Módulo de Administración', 'href' => route('dashboard.index'), 'icon' => 'fa-cogs'],
        ]" :current="'Respaldos de la base de datos'" />

        <x-page-header title="Respaldos de la base de datos" subtitle="Gestiona respaldos de la base de datos."
            icon="fa-database" :action-href="route('backups.store')" action-label="Crear respaldo manual"
            action-permission="create backups">
        </x-page-header>

        <!-- Success Messages -->
        <div class="mt-4">
            <x-session-message />
        </div>
        <!-- End Success Messages -->

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Archivo activo</p>
                <p class="text-sm text-gray-700 dark:text-gray-200 mt-2 break-words">{{ $databasePath }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Asegúrate de cerrar la aplicación antes de restaurar para evitar errores de bloqueo.
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Carpeta de respaldos</p>
                <p class="text-sm text-gray-700 dark:text-gray-200 mt-2 break-words">{{ $backupsPath }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Cada respaldo se guarda con fecha y hora. Al restaurar se copiará como <strong>database.sqlite</strong>.
                </p>
            </div>
        </div>

        <br>

        <x-table :resource="$files">
            <x-slot name="thead">
                <x-table.th>Nombre</x-table.th>
                <x-table.th class="hidden md:table-cell">Tamaño</x-table.th>
                <x-table.th>Fecha</x-table.th>
                <x-table.th class="hidden lg:table-cell">Ruta</x-table.th>
                <x-table.th class="text-center">Acciones</x-table.th>
            </x-slot>
            <x-slot name="tbody">
                @forelse ($files as $file)
                    <x-table.tr>
                        <x-table.td class="font-semibold">{{ $file->name }}</x-table.td>
                        <x-table.td class="hidden md:table-cell">{{ $file->formatted_size }}</x-table.td>
                        <x-table.td>{{ $file->created_at }}</x-table.td>
                        <x-table.td class="hidden lg:table-cell text-xs text-gray-500 truncate max-w-xs" title="{{ $file->backup_path }}">
                            {{ $file->backup_path }}
                        </x-table.td>
                        <x-table.dropdown-actions>
                            <x-table.dropdown-action-item 
                                :href="route('backups.download', ['filename' => $file->name])" 
                                icon="fa-download"
                                :can="['download', \App\Models\Backup::class]">
                                Descargar
                            </x-table.dropdown-action-item>

                            <x-table.dropdown-action-form 
                                :action="route('backups.restore')" 
                                method="POST"
                                message="¿Restaurar este respaldo? Se sobrescribirá la base de datos actual y se perderán los cambios no respaldados desde entonces."
                                title="Restaurar"
                                icon="fa-history"
                                color="emerald"
                                :can="['restore', \App\Models\Backup::class]">
                                <input type="hidden" name="filename" value="{{ $file->name }}">
                            </x-table.dropdown-action-form>

                            <x-table.dropdown-action-delete 
                                :action="route('backups.destroy')"
                                message="¿Eliminar este respaldo?"
                                :can="['delete', \App\Models\Backup::class]">
                                <input type="hidden" name="filename" value="{{ $file->name }}">
                            </x-table.dropdown-action-delete>
                        </x-table.dropdown-actions>
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="5" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500 text-sm">
                            No hay respaldos disponibles.
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-slot>
        </x-table>
    </div>
@endsection