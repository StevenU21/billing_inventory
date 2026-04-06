@extends('layouts.app')
@section('title', 'Editar Método de Pago')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Métodos de Pago', 'href' => route('payment_methods.index')],
        ]" :current="'Editar Método de Pago'" />

        <x-page-header title="Editar Método de Pago" subtitle="Modifica los datos del método de pago." icon="fa-edit">
            <x-page-header.link :href="route('payment_methods.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('payment_methods.update', $paymentMethod) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.payment_methods.form')
            </form>
        </div>
    </div>
@endsection