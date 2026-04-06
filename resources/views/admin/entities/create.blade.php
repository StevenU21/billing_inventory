@extends('layouts.app')
@section('title', 'Crear Entidad')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Compras', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Clientes y Proveedores', 'href' => route('entities.index')],
            ['label' => 'Crear Entidad'],
        ]" />

        <x-page-header title="Crear Cliente & Proveedor" subtitle="Agrega una nueva entidad al sistema." icon="fa-plus">
            <x-page-header.link :href="route('entities.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('entities.store') }}" method="POST">
                @csrf
                @include('admin.entities.form')
            </form>
        </div>
    </div>
@endsection
