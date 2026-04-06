@extends('layouts.app')
@section('title', 'Crear Unidad de Medida')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Unidades de Medida', 'href' => route('unit_measures.index')],
        ]" :current="'Crear Unidad de Medida'" />

        <x-page-header title="Crear Unidad de Medida" subtitle="Agrega una nueva unidad de medida al sistema."
            icon="fa-plus">
            <x-page-header.link :href="route('unit_measures.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('unit_measures.store') }}" method="POST">
                @csrf
                @include('admin.unit_measures.form')
            </form>
        </div>
    </div>
@endsection