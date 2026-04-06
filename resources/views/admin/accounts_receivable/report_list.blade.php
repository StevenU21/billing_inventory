<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Cuentas por Cobrar</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2933;
            margin: 0;
            padding: 24px;
        }

        h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .company-header {
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
        }

        .company-meta {
            margin-top: 4px;
            font-size: 11px;
            color: #4b5563;
            line-height: 1.4;
        }

        .meta {
            font-size: 12px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            font-size: 10.5px;
            vertical-align: top;
        }

        thead th {
            background: #f3f4f6;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .summary-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            background: #fff;
        }

        .summary-card .label {
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
        }

        .summary-card .value {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        .filters {
            margin-bottom: 16px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #f9fafb;
            font-size: 11px;
        }

        .filters strong {
            text-transform: uppercase;
            font-size: 11px;
        }

        .filters ul {
            margin: 6px 0 0 0;
            padding-left: 16px;
        }
    </style>
</head>

<body>
    <div class="company-header">
        <h1>{{ strtoupper($company?->name ?? 'Empresa') }}</h1>
        <div class="company-meta">
            {{ $company?->address ?? 'Dirección no disponible' }}<br>
            Tel: {{ $company?->phone ?? 'N/D' }} · Correo: {{ $company?->email ?? 'N/D' }} · RUC:
            {{ $company?->tax_id ?? 'N/D' }}
        </div>
    </div>
    <div class="meta">
        <strong>Reporte de Cuentas por Cobrar</strong><br>
        Generado: {{ now()->format('d/m/Y H:i:s') }}
    </div>

    @if (!empty($filters))
        <div class="filters">
            <strong>Filtros aplicados</strong>
            <ul>
                @foreach ($filters as $key => $value)
                    <li>{{ \Illuminate\Support\Str::of($key)->replace('_', ' ')->title() }}:
                        {{ is_array($value) ? implode(', ', $value) : $value }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Venta</th>
                <th>Estado</th>
                <th class="text-right">Total</th>
                <th class="text-right">Pagado</th>
                <th class="text-right">Saldo</th>
                <th>Fecha venta</th>
                <th>Creado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $ar)
                @php
                    $client =
                        $ar->entity?->short_name ?:
                        trim(($ar->entity->first_name ?? '') . ' ' . ($ar->entity->last_name ?? '')) ?:
                        '-';
                    $saleId = $ar->sale?->id ? '#' . $ar->sale->id : '-';
                    $saleDate = $ar->sale?->sale_date
                        ? \Carbon\Carbon::parse($ar->sale->sale_date)->format('d/m/Y')
                        : '-';
                    $amountDue = (float) ($ar->amount_due ?? 0);
                    $amountPaid = (float) ($ar->amount_paid ?? 0);
                    $balance = max(0, round($amountDue - $amountPaid, 2));
                @endphp
                <tr>
                    <td>{{ $ar->id }}</td>
                    <td>{{ $client }}</td>
                    <td>{{ $saleId }}</td>
                    <td>{{ $ar->translated_status ?? $ar->status }}</td>
                    <td class="text-right">C$ {{ number_format($amountDue, 2) }}</td>
                    <td class="text-right">C$ {{ number_format($amountPaid, 2) }}</td>
                    <td class="text-right">C$ {{ number_format($balance, 2) }}</td>
                    <td>{{ $saleDate }}</td>
                    <td>{{ optional($ar->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>