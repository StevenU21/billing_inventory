@extends('layouts.app')
@section('title', 'Crear Marca')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Marcas', 'href' => route('brands.index')],
        ]" :current="'Crear Marca'" />

        <x-page-header title="Crear Marca" subtitle="Agrega una nueva marca al sistema." icon="fa-plus">
            <x-page-header.link :href="route('brands.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('brands.store') }}" method="POST">
                @csrf
                @include('admin.brands.form')
            </form>
        </div>
    </div>
@endsection