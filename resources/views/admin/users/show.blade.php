@extends('layouts.app')
@section('title', 'Detalles de Usuario')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Administración', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Usuarios', 'href' => route('users.index')],
        ]" :current="'Detalles'" />

        <div class="mb-6">
            <x-page-header title="Detalles de Usuario" subtitle="Consulta los datos del usuario seleccionado."
                icon="fa-user-circle">
                <x-page-header.link :href="route('users.index')" icon="fas fa-arrow-left">
                    Volver
                </x-page-header.link>
            </x-page-header>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: User Profile Card -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                    <div class="p-6 flex flex-col items-center text-center">
                        <!-- Avatar -->
                        @if ($user->profile && $user->profile->avatar)
                            <img src="{{ $user->profile->getAvatarUrlAttribute() }}" alt="Avatar"
                                class="w-32 h-32 rounded-full object-cover border-4 border-purple-100 dark:border-purple-900 shadow-lg mb-4">
                        @else
                            <div class="w-32 h-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-5xl text-gray-400 border-4 border-gray-100 dark:border-gray-600 shadow-lg mb-4">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif

                        <!-- Name & Role -->
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-1">
                            {{ $user->first_name }} {{ $user->last_name }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $user->email }}
                        </p>

                        <!-- Status & Role Badges -->
                        <div class="flex flex-wrap justify-center gap-2 mb-6">
                             @if ($user->roles->count())
                                <span class="px-3 py-1 text-xs font-semibold leading-tight text-white bg-blue-600 rounded-full dark:bg-blue-700">
                                    {{ $user->formatted_role_name }}
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold leading-tight text-gray-600 bg-gray-200 rounded-full dark:bg-gray-700 dark:text-gray-300">
                                    Sin rol
                                </span>
                            @endif

                            @if ($user->is_active)
                                <span class="px-3 py-1 text-xs font-semibold leading-tight text-white bg-green-600 rounded-full dark:bg-green-700">
                                    Activo
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold leading-tight text-white bg-red-600 rounded-full dark:bg-red-700">
                                    Inactivo
                                </span>
                            @endif
                        </div>

                        <!-- Quick Actions -->
                        <div class="w-full grid grid-cols-2 gap-2">
                             <a href="{{ route('users.edit', $user) }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition text-sm font-medium">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                             <a href="{{ route('users.permissions.edit', $user) }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg transition text-sm font-medium">
                                <i class="fas fa-key"></i> Permisos
                            </a>
                        </div>
                    </div>
                    
                    <!-- Divider -->
                    <div class="border-t border-gray-100 dark:border-gray-700"></div>

                    <!-- Basic Info List -->
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                            Información Básica
                        </h3>
                        <ul class="space-y-3 text-sm">
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">ID Usuario:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">#{{ $user->id }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Registrado:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $user->created_at->format('d/m/Y') }}</span>
                            </li>
                             <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Última act.:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $user->updated_at->format('d/m/Y') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Column: Detailed Info & Permissions -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Personal Information Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="fas fa-id-card text-purple-600 dark:text-purple-400"></i>
                            Información Personal
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Nombre Completo</label>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $user->first_name }} {{ $user->last_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Cédula / Identificación</label>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $user->profile->formatted_identity_card ?? 'No registrado' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Correo Electrónico</label>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Teléfono</label>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $user->profile->formatted_phone ?? 'No registrado' }}</p>
                        </div>
                         <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Género</label>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $user->profile->gender_label ?? 'No especificado' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1">Dirección</label>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $user->profile->address ?? 'No registrada' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Permissions Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="fas fa-shield-alt text-purple-600 dark:text-purple-400"></i>
                            Permisos y Accesos
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Direct Permissions -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                                <i class="fas fa-key text-gray-400"></i> Permisos Directos
                            </h4>
                            @php $directPermissions = $user->getDirectPermissions()->pluck('name'); @endphp
                            @if ($directPermissions->count())
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($directPermissions as $perm)
                                        <span class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 rounded-full text-xs font-semibold border border-green-200 dark:border-green-800 uppercase tracking-wide">
                                            {{ $perm }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 italic">Este usuario no tiene permisos asignados directamente.</p>
                            @endif
                        </div>

                        <!-- Inherited Permissions -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                                <i class="fas fa-users-cog text-gray-400"></i> Permisos Heredados (Rol: {{ $user->formatted_role_name ?? 'N/A' }})
                            </h4>
                            @php $rolePermissions = $user->getPermissionsViaRoles()->pluck('name')->unique(); @endphp
                            @if ($rolePermissions->count())
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($rolePermissions as $perm)
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-full text-xs font-semibold border border-gray-200 dark:border-gray-600 uppercase tracking-wide">
                                            {{ $perm }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 italic">No hay permisos heredados por el rol actual.</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection