@extends('layouts.app')
@section('title', 'Actualizar Usuario')

@section('content')
    <div class="container px-6 mx-auto grid">
        <!-- Breadcrumbs -->
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Administración', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Usuarios', 'href' => route('users.index')],
        ]" :current="'Editar'" />

        <div class="mb-6">
            <x-page-header title="Actualizar Usuario" subtitle="Modifica los datos del usuario seleccionado."
                icon="fa-user-edit">
                <x-page-header.link :href="route('users.index')" icon="fas fa-arrow-left">
                    Volver
                </x-page-header.link>
            </x-page-header>
        </div>

        <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.users.form')
        </form>
    </div>
@endsection