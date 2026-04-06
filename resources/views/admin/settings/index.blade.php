@extends('layouts.app')
@section('title', 'Configuración')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 mx-auto">
        <x-breadcrumb :parents="[
            ['label' => 'Módulo de Administración', 'href' => route('dashboard.index'), 'icon' => 'fa-cogs'],
        ]" :current="'Configuración del sistema'" />

        <x-page-header title="Configuración" subtitle="Gestiona la configuración general del sistema." icon="fa-sliders-h">
        </x-page-header>

        <!-- Success Messages -->
        <div class="mt-4">
            <x-session-message />
        </div>
        <!-- End Success Messages -->

        <div class="mt-6 space-y-6 pb-6">
            <!-- General Settings Card -->
            @include('admin.settings.partials.general')

            <!-- System Info Card -->
            @include('admin.settings.partials.system')

            <!-- Appearance Card -->
            @include('admin.settings.partials.appearance')

            <!-- Quotation Settings Card -->
            @include('admin.settings.partials.quotations')

            <!-- Backup Settings Card -->
            @include('admin.settings.partials.backup')

            <!-- Notifications Settings Card -->
            @include('admin.settings.partials.notifications')

            <!-- Application Behavior Card -->
            @include('admin.settings.partials.behavior')
        </div>
    </div>
@endsection