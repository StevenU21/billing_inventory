@extends('layouts.app')
@section('title', 'Nueva Cotización')

@section('content')
    <div class="container px-6 mx-auto grid">
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Ventas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Cotizaciones', 'href' => route('admin.quotations.index')],
            ['label' => 'Crear'],
        ]" />

        <x-page-header title="Crear Cotización" subtitle="Registra una nueva cotización para un cliente."
            icon="fa-file-invoice-dollar">
            <x-page-header.link href="{{ route('admin.quotations.index') }}" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>
        <x-session-message />
        
        <x-form method="POST" action="{{ route('admin.quotations.store') }}">
            @include('admin.quotations.form')
        </x-form>
    </div>
@endsection