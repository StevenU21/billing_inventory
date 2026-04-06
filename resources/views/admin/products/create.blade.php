@extends('layouts.app')
@section('title', 'Nuevo Producto')

@section('content')
    <div class="container px-6 mx-auto grid">
        <!-- Breadcrumbs -->
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Inventario', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Productos', 'href' => route('products.index')],
        ]" :current="'Crear Producto'" />

        <!-- Page header card -->
        <x-page-header title="Crear Producto" subtitle="Registra un nuevo producto en el sistema." icon="fa-box">
            <x-page-header.link href="{{ route('products.index') }}" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.products.form')
        </form>
    </div>
@endsection