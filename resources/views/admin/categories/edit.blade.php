@extends('layouts.app')
@section('title', 'Editar Categoría')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Categorías', 'href' => route('categories.index')],
        ]" :current="'Editar Categoría'" />

        <x-page-header title="Editar Categoría" subtitle="Modifica los datos de la categoría." icon="fa-edit">
            <x-page-header.link :href="route('categories.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.categories.form')
            </form>
        </div>
    </div>
@endsection