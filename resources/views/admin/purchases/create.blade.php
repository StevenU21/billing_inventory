@extends('layouts.app')
@section('title', 'Nueva Compra')

@section('content')
    <div class="container px-6 mx-auto grid">
        <x-breadcrumb :items="[
            ['label' => 'Modulo de Compras', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Compras', 'href' => route('purchases.index')],
            ['label' => 'Crear'],
        ]" />

        <x-page-header title="Crear Compra" subtitle="Registra una nueva compra en el sistema." icon="fa-shopping-cart">
            <x-page-header.link href="{{ route('purchases.index') }}" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <x-form method="POST" action="{{ route('purchases.store') }}">
            @include('admin.purchases.form')
        </x-form>
    </div>
@endsection