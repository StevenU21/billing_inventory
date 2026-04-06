@extends('layouts.app')
@section('title', 'Detalles de la Empresa')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Módulo de Administración', 'href' => route('companies.index'), 'icon' => 'fa-cogs'],
        ]" :current="$company->name" />

        <x-page-header title="Datos de la Empresa" subtitle="Revisa la información general y de contacto."
            icon="fa-building">
            <x-page-header.link :href="route('companies.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
            <x-page-header.link :href="route('companies.edit', $company)" icon="fas fa-edit">
                Editar
            </x-page-header.link>
        </x-page-header>

        <!-- Content -->
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left column: profile card -->
            <section class="rounded-xl bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow p-6">
                <div class="flex flex-col items-center text-center">
                    <div class="relative">
                        @if ($company->logo)
                            <img src="{{ $company->avatar_url }}" alt="Logo de {{ $company->name }}"
                                class="w-40 h-40 md:w-48 md:h-48 object-cover rounded-xl ring-4 ring-purple-100 dark:ring-purple-900/40 shadow"
                                loading="lazy">
                        @else
                            <div
                                class="w-40 h-40 md:w-48 md:h-48 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center ring-4 ring-purple-100 dark:ring-purple-900/40 shadow text-5xl text-gray-400">
                                <i class="fas fa-building"></i>
                            </div>
                        @endif
                    </div>
                    <h2 class="mt-4 text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight uppercase">
                        {{ $company->name }}
                    </h2>

                    <div class="mt-4 w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <div class="py-3 text-left">
                            <span class="block text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">RUC</span>
                            <div class="mt-1 flex items-center justify-between gap-2">
                                <span id="company-ruc"
                                    class="font-medium text-gray-800 dark:text-gray-100">{{ $company->ruc ?? '—' }}</span>
                                @if (!empty($company->ruc))
                                    <button type="button" onclick="copyField('company-ruc')"
                                        class="px-2 py-1 text-xs rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                                        Copiar
                                    </button>
                                @endif
                            </div>
                            <span id="company-ruc-copied" class="hidden mt-1 text-xs text-green-600">Copiado</span>
                        </div>
                        <div class="py-3 text-left">
                            <span
                                class="block text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Correo</span>
                            <div class="mt-1 flex items-center justify-between gap-2">
                                <span id="company-email"
                                    class="font-medium text-gray-800 dark:text-gray-100">{{ $company->email ?? '—' }}</span>
                                @if (!empty($company->email))
                                    <div class="flex items-center gap-2">
                                        <a href="mailto:{{ $company->email }}"
                                            class="px-2 py-1 text-xs rounded-md bg-purple-50 text-purple-700 hover:bg-purple-100 dark:bg-purple-900/40 dark:text-purple-300 dark:hover:bg-purple-700">Enviar</a>
                                        <button type="button" onclick="copyField('company-email')"
                                            class="px-2 py-1 text-xs rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">Copiar</button>
                                    </div>
                                @endif
                            </div>
                            <span id="company-email-copied" class="hidden mt-1 text-xs text-green-600">Copiado</span>
                        </div>
                        <div class="py-3 text-left">
                            <span
                                class="block text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Teléfono</span>
                            <div class="mt-1 flex items-center justify-between gap-2">
                                <span id="company-phone"
                                    class="font-medium text-gray-800 dark:text-gray-100">{{ $company->phone ?? '—' }}</span>
                                @if (!empty($company->phone))
                                    <div class="flex items-center gap-2">
                                        <a href="tel:{{ $company->phone }}"
                                            class="px-2 py-1 text-xs rounded-md bg-purple-50 text-purple-700 hover:bg-purple-100 dark:bg-purple-900/40 dark:text-purple-300 dark:hover:bg-purple-700">Llamar</a>
                                        <button type="button" onclick="copyField('company-phone')"
                                            class="px-2 py-1 text-xs rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">Copiar</button>
                                    </div>
                                @endif
                            </div>
                            <span id="company-phone-copied" class="hidden mt-1 text-xs text-green-600">Copiado</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Right column: details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Información General -->
                <section class="rounded-xl bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-id-card text-purple-600 dark:text-purple-400"></i>
                            Información de la empresa
                        </h3>
                    </div>
                    <div class="px-6 py-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Dirección</dt>
                                <dd id="company-address" class="mt-1 text-gray-800 dark:text-gray-100">
                                    {{ $company->address ?? '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Descripción</dt>
                                <dd class="mt-1 text-gray-800 dark:text-gray-100">{{ $company->description ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <!-- Fechas -->
                <section class="rounded-xl bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-shadow">
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-purple-600 dark:text-purple-400"></i>
                            Fechas
                        </h3>
                    </div>
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="flex items-start gap-3">
                                <span
                                    class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                    <i class="fas fa-calendar-plus"></i>
                                </span>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Fecha de
                                        registro</p>
                                    <p class="mt-1 font-medium text-gray-800 dark:text-gray-100">
                                        {{ $company->formatted_created_at ?? '—' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span
                                    class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                    <i class="fas fa-history"></i>
                                </span>
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Última
                                        actualización</p>
                                    <p class="mt-1 font-medium text-gray-800 dark:text-gray-100">
                                        {{ $company->formatted_updated_at ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Helpers -->
    <script>
        function copyField(id) {
            const el = document.getElementById(id);
            if (!el || !navigator.clipboard) return;
            const text = (el.innerText || el.textContent || '').trim();
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                const toast = document.getElementById(id + '-copied');
                if (toast) {
                    toast.classList.remove('hidden');
                    setTimeout(() => toast.classList.add('hidden'), 1500);
                }
            }).catch(() => { });
        }
    </script>
@endsection