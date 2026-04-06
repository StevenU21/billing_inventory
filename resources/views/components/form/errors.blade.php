@props([
    'title' => 'Por favor corrige los siguientes errores:',
])

@if($errors->any())
    <div {{ $attributes->merge(['class' => 'p-3 bg-red-100 dark:bg-red-900/30 rounded-lg border border-red-300 dark:border-red-700']) }}>
        <p class="text-sm text-red-600 dark:text-red-400 font-medium">{{ $title }}</p>
        <ul class="mt-1 text-sm text-red-500 dark:text-red-400 list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
