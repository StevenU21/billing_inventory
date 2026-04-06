@extends('layouts.app')
@section('title', 'Editar Empresa')

@section('content')
    <div class="container px-6 mx-auto grid">
        <x-breadcrumb :parents="[
            ['label' => 'Módulo de Empresa', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Empresas', 'href' => route('companies.index'), 'icon' => 'fa-building'],
        ]" :current="'Editar Empresa'" />

        <x-page-header title="Editar Empresa" subtitle="Actualiza los datos de la empresa." icon="fa-building">
            <x-page-header.link :href="route('companies.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <form action="{{ route('companies.update', $company) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.companies.form')
        </form>
    </div>
@endsection