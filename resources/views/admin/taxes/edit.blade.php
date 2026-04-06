@extends('layouts.app')
@section('title', 'Editar Impuesto')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Modulo de Catálogos', 'href' => '#', 'icon' => 'fa-home'],
            ['label' => 'Impuestos', 'href' => route('taxes.index')],
        ]" :current="'Editar Impuesto'" />

        <x-page-header title="Editar Impuesto" subtitle="Modifica los datos del impuesto." icon="fa-edit">
            <x-page-header.link :href="route('taxes.index')" icon="fas fa-arrow-left">
                Volver
            </x-page-header.link>
        </x-page-header>

        <div class="mt-6">
            <form action="{{ route('taxes.update', $tax) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.taxes.form')
            </form>
        </div>
    </div>
@endsection
