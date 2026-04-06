@extends('layouts.app')
@section('title', 'Detalles de la Categoría')

@section('content')
    @if (isset($alpine) && $alpine)
        <div x-ref="showContent">
            <p><strong>ID:</strong> <span x-text="showCategory.id"></span></p>
            <p><strong>Nombre:</strong> <span x-text="showCategory.name"></span></p>
            <p><strong>Descripción:</strong> <span x-text="showCategory.description"></span></p>
            <p><strong>Fecha de Registro:</strong> <span x-text="showCategory.formatted_created_at"></span></p>
            <p><strong>Fecha de Actualización:</strong> <span x-text="showCategory.formatted_updated_at"></span></p>
        </div>
    @else
        <div class="container px-6 mx-auto grid">
            <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
                Detalles de la Categoría
            </h2>

            <div class="mb-4 flex justify-end">
                <a href="{{ route('categories.index') }}"
                    class="flex items-center px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>

            <div
                class="w-full overflow-hidden rounded-lg shadow-md bg-white dark:bg-gray-800 hover:shadow-lg transition-shadow duration-150">
                <div class="p-4">
                    <h3
                        class="text-lg font-semibold text-gray-700 dark:text-gray-200 hover:text-purple-600 dark:hover:text-purple-400 transition-colors duration-150">
                        Información de la Categoría
                    </h3>
                    <div class="mt-4">
                        <p
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors duration-150 flex items-center">
                            <i class="fas fa-tag text-purple-600 dark:text-purple-400 mr-2"></i>
                            <strong
                                class="text-gray-700 dark:text-gray-200 hover:text-purple-600 dark:hover:text-purple-400 transition-colors duration-150">Nombre:</strong>
                            {{ $category->name }}
                        </p>
                        <p
                            class="text-sm text-gray-600 dark:text-gray-400 mt-2 hover:text-gray-900 dark:hover:text-gray-200 transition-colors duration-150 flex items-center">
                            <i class="fas fa-align-left text-purple-600 dark:text-purple-400 mr-2"></i>
                            <strong
                                class="text-gray-700 dark:text-gray-200 hover:text-purple-600 dark:hover:text-purple-400 transition-colors duration-150">Descripción:</strong>
                            {{ $category->description }}
                        </p>
                        <p
                            class="text-sm text-gray-600 dark:text-gray-400 mt-2 hover:text-gray-900 dark:hover:text-gray-200 transition-colors duration-150 flex items-center">
                            <i class="fas fa-calendar-alt text-purple-600 dark:text-purple-400 mr-2"></i>
                            <strong
                                class="text-gray-700 dark:text-gray-200 hover:text-purple-600 dark:hover:text-purple-400 transition-colors duration-150">Fecha
                                de creación:</strong>
                            {{ $category->created_at ? $category->created_at->format('Y-m-d') : '-' }}
                        </p>
                        <p
                            class="text-sm text-gray-600 dark:text-gray-400 mt-2 hover:text-gray-900 dark:hover:text-gray-200 transition-colors duration-150 flex items-center">
                            <i class="fas fa-clock text-purple-600 dark:text-purple-400 mr-2"></i>
                            <strong
                                class="text-gray-700 dark:text-gray-200 hover:text-purple-600 dark:hover:text-purple-400 transition-colors duration-150">Última
                                actualización:</strong>
                            {{ $category->updated_at ? $category->updated_at->format('Y-m-d') : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
