@extends('layouts.app')
@section('title', 'Crear Usuario')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Administración', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Usuarios', 'href' => route('users.index')],
        ]" :current="'Crear'" />

        <div class="mb-6">
            <x-page-header title="Crear Usuario" subtitle="Agrega un nuevo usuario al sistema." icon="fa-user-plus">
                <x-page-header.link :href="route('users.index')" icon="fas fa-arrow-left">
                    Volver
                </x-page-header.link>
            </x-page-header>
        </div>

        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.users.form')
        </form>
    </div>
@endsection