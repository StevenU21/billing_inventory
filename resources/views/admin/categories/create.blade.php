@extends('layouts.app')
@section('title', 'Crear Categoría')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Categorías', 'href' => route('categories.index')],
        ]" :current="'Crear Categoría'" />

        <x-page-header title="Crear Categoría" subtitle="Agrega una nueva categoría al sistema." icon="fa-plus">
            <x-page-header.link :href="route('categories.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                @include('admin.categories.form')
            </form>
        </div>
    </div>
@endsection