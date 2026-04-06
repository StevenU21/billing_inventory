@extends('layouts.app')
@section('title', 'Actualizar Compra')

@section('content')
    <div class="container px-6 mx-auto grid">
        <x-breadcrumb :items="[
            ['label' => 'Modulo de Compras', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Compras', 'href' => route('purchases.index')],
            ['label' => 'Editar'],
        ]" />

        <x-page-header title="Actualizar Compra" subtitle="Edita los datos de la compra seleccionada."
            icon="fa-shopping-cart">
            <x-page-header.link href="{{ route('purchases.index') }}" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <form method="POST" action="{{ route('purchases.update', $purchase) }}">
            @csrf
            @method('PUT')
            @include('admin.purchases.form')
        </form>
    </div>
@endsection