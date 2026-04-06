@extends('layouts.app')
@section('title', 'Editar Marca')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Marcas', 'href' => route('brands.index')],
        ]" :current="'Editar Marca'" />

        <x-page-header title="Editar Marca" subtitle="Modifica los datos de la marca." icon="fa-edit">
            <x-page-header.link :href="route('brands.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('brands.update', $brand) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.brands.form')
            </form>
        </div>
    </div>
@endsection