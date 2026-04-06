@extends('layouts.app')
@section('title', 'Crear Impuesto')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Impuestos', 'href' => route('taxes.index')],
        ]" :current="'Crear Impuesto'" />

        <x-page-header title="Crear Impuesto" subtitle="Agrega un nuevo impuesto al sistema." icon="fa-plus">
            <x-page-header.link :href="route('taxes.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('taxes.store') }}" method="POST">
                @csrf
                @include('admin.taxes.form')
            </form>
        </div>
    </div>
@endsection
