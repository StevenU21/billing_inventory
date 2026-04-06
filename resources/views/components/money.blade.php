@props([
    'amount',
    'variant' => 'default', // default, positive, negative, balance
    'size' => 'base', // xs, sm, base, lg, xl, 2xl, 3xl
    'showZero' => false,
])
@php
    use Brick\Money\Money;

    // Si es null, mostrar guión
    if ($amount === null) {
        echo '<span class="text-gray-400 dark:text-gray-500">-</span>';
        return;
    }

    // Si no es una instancia de Money, intentar convertir
    if (!($amount instanceof Money)) {
        echo '<span class="text-gray-400 dark:text-gray-500">-</span>';
        return;
    }

    // Si es cero y no queremos mostrarlo
    if ($amount->isZero() && !$showZero) {
        echo '<span class="text-gray-400 dark:text-gray-500">-</span>';
        return;
    }

    // Formatear el monto
    $formatted = $amount->formatTo('es_NI');

    // Determinar clase de color según variante
    $colorClass = match ($variant) {
        'positive' => 'text-green-600 dark:text-green-400',
        'negative' => 'text-red-600 dark:text-red-400',
        'balance' => $amount->isPositive()
        ? 'text-red-600 dark:text-red-400'  // Deuda (debe)
        : ($amount->isNegative()
            ? 'text-green-600 dark:text-green-400'  // A favor (haber)
            : 'text-gray-600 dark:text-gray-400'),  // Cero
        'auto' => $amount->isPositive()
        ? 'text-green-600 dark:text-green-400'
        : ($amount->isNegative()
            ? 'text-red-600 dark:text-red-400'
            : 'text-gray-600 dark:text-gray-400'),
        default => 'text-gray-800 dark:text-gray-100',
    };

    // Determinar tamaño de fuente
    $sizeClass = match ($size) {
        'xs' => 'text-xs',
        'sm' => 'text-sm',
        'base' => 'text-base',
        'lg' => 'text-lg',
        'xl' => 'text-xl',
        '2xl' => 'text-2xl',
        '3xl' => 'text-3xl',
        default => 'text-base',
    };
@endphp

<span {{ $attributes->merge(['class' => "$colorClass $sizeClass font-mono tabular-nums whitespace-nowrap"]) }}>
    {{ $formatted }}
</span>
