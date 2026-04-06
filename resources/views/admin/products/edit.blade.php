@extends('layouts.app')
@section('title', 'Editar Producto')

@section('content')
    <div class="container px-6 mx-auto grid">
        <!-- Breadcrumbs -->
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Inventario', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Productos', 'href' => route('products.index')],
        ]" :current="'Editar Producto'" />

        <!-- Page header card -->
        <x-page-header title="Editar Producto #{{ $product->id }}" subtitle="Modifica los datos del producto." icon="fa-box">
            <x-page-header.link href="{{ route('products.index') }}" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.products.form')
        </form>
    </div>
@endsection
