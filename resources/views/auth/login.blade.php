@extends('layouts.guest')
@section('title', 'Iniciar sesión')

@section('content')
    <style>
        @keyframes login-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .login-spinner {
            animation: login-spin 0.9s linear infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .login-spinner {
                animation: none;
            }
        }
    </style>

    <x-session-message class="mb-4" :status="session('status')" />

    @auth
        <div class="flex flex-col overflow-y-auto md:flex-row">
            <div class="h-32 md:h-auto md:w-1/2 flex items-center justify-center">
                <img aria-hidden="true" class="hidden object-cover w-full h-full dark:block" src="{{ asset('img/store.jfif') }}"
                    alt="Office" />
            </div>
            <div class="flex flex-col items-center justify-center p-6 sm:p-12 md:w-1/2">
                <h1
                    class="mb-6 text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-gray-800 dark:text-gray-100 text-center whitespace-normal break-words">
                    INVENTARIO Y FACTURACIÓN
                </h1>
                <div class="mb-4 text-lg text-gray-700 dark:text-gray-200">Ya has iniciado sesión.</div>
                @php
                    $user = auth()->user();
                    $isCashier = $user && $user->hasRole('cashier');
                    $targetRoute = $isCashier ? route('admin.sales.index') : route('dashboard.index');
                    $targetLabel = $isCashier ? 'Ir a ventas' : 'Ir al dashboard';
                @endphp
                <div class="flex gap-3 mt-4">
                    <a x-data="{ loading: false }" :class="['flex items-center justify-center flex-1 px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple active:bg-purple-600',
                                    loading ? 'opacity-60 cursor-not-allowed' : ''
                                ]" :href="loading ? null : '{{ $targetRoute }}'" @click.prevent="
                                    if (!loading) {
                                        loading = true;
                                        requestAnimationFrame(() => {
                                            window.location = '{{ $targetRoute }}';
                                        });
                                    }
                                " :disabled="loading" :aria-disabled="loading.toString()">
                        <span class="mr-2 inline-flex items-center">
                            <template x-if="!loading">
                                <i class="fas fa-arrow-right"></i>
                            </template>
                            <template x-if="loading">
                                <svg class="login-spinner h-4 w-4 ml-2 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                    </circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </template>
                        </span>
                        <span class="align-middle font-semibold">{{ $targetLabel }}</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" data-prevent-multiple-submits>
                        @csrf
                        <button type="submit" x-data="{ loading: false }" x-bind:disabled="loading" @click.prevent="
                                        if (!loading) {
                                            loading = true;
                                            requestAnimationFrame(() => {
                                                $el.closest('form').submit();
                                            });
                                        }
                                    "
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:shadow-outline-red active:bg-red-600 disabled:opacity-60 disabled:cursor-not-allowed whitespace-nowrap">
                            <span class="mr-2 inline-flex items-center">
                                <template x-if="!loading">
                                    <i class="fas fa-sign-out-alt"></i>
                                </template>
                                <template x-if="loading">
                                    <svg class="login-spinner h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </template>
                            </span>
                            <span x-text="loading ? 'Cerrando sesión...' : 'Cerrar sesión'"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="flex flex-col overflow-y-auto md:flex-row">
            <div class="h-32 md:h-auto md:w-1/2">
                <img aria-hidden="true" class="object-cover w-full h-full dark:hidden"
                    src="{{ asset('img/login-office.jpeg') }}" alt="Office" />
                <img aria-hidden="true" class="hidden object-cover w-full h-full dark:block" src="{{ asset('img/store.jfif') }}"
                    alt="Office" />
            </div>
            <form method="POST" action="{{ route('login') }}" data-prevent-multiple-submits
                class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
                @csrf
                <div class="w-full">
                    <h1
                        class="mb-6 text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-gray-800 dark:text-gray-100 text-center whitespace-normal break-words">
                        INVENTARIO Y FACTURACIÓN
                    </h1>

                    <label class="block text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Correo electrónico</span>
                        <input
                            class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                            type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            placeholder="usuario@ejemplo.com" />
                    </label>
                    <label class="block mt-4 text-sm">
                        <span class="text-gray-700 dark:text-gray-400">Contraseña</span>
                        <input
                            class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                            placeholder="***************" type="password" name="password" required
                            autocomplete="current-password" />
                    </label>

                    <div class="mt-14 mb-6">
                        <button type="submit" x-data="{ loading: false }" x-bind:disabled="loading" @click.prevent="
                                                if (!loading) {
                                                    loading = true;
                                                    requestAnimationFrame(() => {
                                                        $el.closest('form').submit();
                                                    });
                                                }
                                            "
                            class="flex items-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple active:bg-purple-600 disabled:opacity-60 disabled:cursor-not-allowed mt-4 w-full">
                            <span class="mr-2 flex items-center">
                                <template x-if="!loading">
                                    <i class="fas fa-paper-plane"></i>
                                </template>
                                <template x-if="loading">
                                    <svg class="login-spinner h-4 w-4 ml-2 text-white" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </template>
                            </span>
                            <span x-text="loading ? 'Ingresando...' : 'Ingresar'"></span>
                        </button>
                    </div>

                    <hr class="my-8" />
                </div>
            </form>
        </div>
    @endauth
@endsection