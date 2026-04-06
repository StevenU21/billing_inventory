@extends('layouts.app')
@section('title', 'Crear Empresa')

@section('content')
    <div class="container px-6 mx-auto grid">
        <x-breadcrumb :parents="[
            ['label' => 'Módulo de Empresa', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Empresas', 'href' => route('companies.index'), 'icon' => 'fa-building'],
        ]" :current="'Crear Empresa'" />

        <x-page-header title="Crear Empresa" subtitle="Registra los datos de la empresa." icon="fa-building">
            <x-page-header.link :href="route('companies.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <form action="{{ route('companies.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.companies.form')
        </form>
    </div>
@endsection