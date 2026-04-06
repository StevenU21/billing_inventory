@extends('layouts.app')
@section('title', 'Crear Método de Pago')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Métodos de Pago', 'href' => route('payment_methods.index')],
        ]" :current="'Crear Método de Pago'" />

        <x-page-header title="Crear Método de Pago" subtitle="Agrega un nuevo método de pago al sistema." icon="fa-plus">
            <x-page-header.link :href="route('payment_methods.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('payment_methods.store') }}" method="POST">
                @csrf
                @include('admin.payment_methods.form')
            </form>
        </div>
    </div>
@endsection