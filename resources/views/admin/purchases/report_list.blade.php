<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle ?? 'Reporte de Compras' }}</title>
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
        <strong>{{ $reportTitle ?? 'Reporte de Compras' }}</strong><br>
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

    @php
        $records = (int) ($totals['records'] ?? ($purchases?->count() ?? 0));
        $items = (int) ($totals['items'] ?? 0);
        $subtotal = (float) ($totals['subtotal'] ?? 0);
        $total = (float) ($totals['total'] ?? 0);
        $currencySymbol = 'C$';
    @endphp

    <div class="summary-grid">
        <div class="summary-card">
            <div class="label">Registros</div>
            <div class="value">{{ number_format($records) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Ítems</div>
            <div class="value">{{ number_format($items) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Subtotal</div>
            <div class="value">{{ $currencySymbol }} {{ number_format($subtotal, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total</div>
            <div class="value">{{ $currencySymbol }} {{ number_format($total, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 54px;">#</th>
                <th>Proveedor</th>
                <th>Referencia</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Impuesto</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($purchases ?? collect()) as $purchase)
                @php
                    $supplier = $purchase->entity;
                @endphp
                <tr>
                    <td class="text-center">{{ $purchase->id }}</td>
                    <td>{{ $supplier?->full_name ?? $supplier?->short_name ?? '—' }}</td>
                    <td>{{ $purchase->reference ?: '—' }}</td>
                    <td>{{ $purchase->status?->value ?? ($purchase->status ?? '—') }}</td>
                    <td>{{ $purchase->purchase_date?->format('d/m/Y') ?? ($purchase->created_at?->format('d/m/Y') ?? '—') }}</td>
                    <td class="text-right">{{ $purchase->formatted_sub_total ?? '' }}</td>
                    <td class="text-right">{{ $purchase->formatted_tax_amount ?? ($purchase->formatted_tax ?? '') }}</td>
                    <td class="text-right">{{ $purchase->formatted_total ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
