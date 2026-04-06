@extends('layouts.app')
@section('title', 'Nueva Venta')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Ventas', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Ventas', 'href' => route('admin.sales.index')],
            ['label' => 'Registrar'],
        ]" />

        <x-page-header title="Crear Venta" subtitle="Registra una nueva venta." icon="fa-shopping-basket">
            <x-page-header.link href="{{ route('admin.sales.index') }}" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <x-form action="{{ route('admin.sales.store') }}" method="POST">
            @include('admin.sales.form')
        </x-form>
    </div>
@endsection