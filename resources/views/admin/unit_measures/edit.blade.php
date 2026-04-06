@extends('layouts.app')
@section('title', 'Editar Unidad de Medida')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Unidades de Medida', 'href' => route('unit_measures.index')],
        ]" :current="'Editar Unidad de Medida'" />

        <x-page-header title="Editar Unidad de Medida" subtitle="Modifica los datos de la unidad de medida." icon="fa-edit">
            <x-page-header.link :href="route('unit_measures.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('unit_measures.update', $unitMeasure) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.unit_measures.form')
            </form>
        </div>
    </div>
@endsection