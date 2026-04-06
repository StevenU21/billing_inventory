@extends('layouts.app')
@section('title', 'Editar Entidad')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :items="[
            ['label' => 'Módulo de Compras', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Clientes y Proveedores', 'href' => route('entities.index')],
            ['label' => 'Editar Entidad'],
        ]" />

        <x-page-header title="Editar Entidad" subtitle="Modifica los datos de la entidad seleccionada." icon="fa-edit">
            <x-page-header.link :href="route('entities.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('entities.update', $entity) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.entities.form')
            </form>
        </div>
    </div>
@endsection
