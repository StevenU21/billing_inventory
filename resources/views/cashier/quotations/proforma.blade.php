<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proforma</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            color: #222;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .invoice-box {
            max-width: 800px;
            margin: 18px auto;
            background: #fff;
            border: 1.5px solid #222;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            padding: 16px 18px 12px 18px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            border-bottom: 1.5px solid #222;
            padding-bottom: 4px;
        }

        .company {
            font-size: 22px;
            font-weight: bold;
            color: #222;
        }

        .company img {
            margin-bottom: 0;
            filter: grayscale(100%);
            height: 100px !important;
            display: block;
        }

        .meta {
            text-align: right;
            font-size: 14px;
            color: #222;
        }

        .client {
            margin-bottom: 18px;
            font-size: 15px;
            color: #222;
        }

        .fiscal {
            font-size: 13px;
            color: #444;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #222;
            padding: 8px 6px;
        }

        th {
            background: #e9ecef;
            color: #222;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .totals {
            margin-top: 18px;
            width: 100%;
        }

        .totals td {
            padding: 6px 8px;
            font-size: 14px;
        }

        .right {
            text-align: right;
        }

        .footer {
            margin-top: 32px;
            text-align: left;
            color: #222;
            font-size: 13px;
            border-top: 1px solid #222;
            padding-top: 12px;
        }

        .legal {
            font-size: 12px;
            color: #555;
            margin-top: 18px;
        }
    </style>
    @php
        $currency = '$';
    @endphp
</head>

<body>
    <div class="invoice-box">
        <div class="header">
            <div class="company">
                <img src="{{ public_path('img/logo_factura.png') }}" alt="logo" height="100">
                <div>{{ $company->name ?? 'Empresa' }}</div>
                <div class="fiscal">Correo: {{ $company->email ?? 'N/A' }}</div>
                <div style="font-size:13px; font-weight:normal; color:#222;">{{ $company->address ?? '' }}</div>
                <div style="font-size:13px; font-weight:normal; color:#222;">Tel: {{ $company->phone ?? '' }}</div>
            </div>
            <div class="meta">
                <div><strong>Proforma No.:</strong> {{ $quotation?->id ?? '' }}</div>
                <div><strong>Fecha:</strong> {{ optional($quotation?->created_at)->format('d/m/Y H:i:s') }}</div>
                {{-- Válida hasta: usa valid_until o created_at +7 días --}}
                <div><strong>Válida hasta:</strong>
                    @if ($quotation && $quotation->valid_until)
                        {{ $quotation->valid_until->format('d/m/Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($quotation_date)->addDays($validityDays)->format('d/m/Y') }}
                    @endif
                </div>
                <div><strong>Vendedor:</strong> {{ $user?->short_name ?? 'N/D' }}</div>
            </div>
        </div>
        <div class="client">
            <strong>Cliente:</strong> {{ $entity?->short_name ?? 'N/D' }}<br>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Color</th>
                    <th>Talla / Presentación</th>
                    <th class="right">Cant</th>
                    <th class="right">P. Unit</th>
                    <th class="right">Desc</th>
                    <th class="right">Imp.</th>
                    <th class="right">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @foreach ($details as $d)
                    @php
                        $prod = $d['variant']->product ?? null;
                        $color = $d['variant']->color->name ?? '';
                        $size = $d['variant']->size->name ?? '';
                    @endphp
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $prod?->name ?? 'Producto' }}</td>
                        <td>{{ $color ? $color : 'N/A' }}</td>
                        <td>{{ $size ? $size : 'N/A' }}</td>
                        <td class="right">{{ $d['quantity'] }}</td>
                        <td class="right">{{ $currency }}
                            {{ number_format($d['unit_price'], 2) }}
                        </td>
                        <td class="right">{{ $currency }} {{ number_format($d['discount_amount'] ?? 0, 2) }}</td>
                        <td class="right">{{ $currency }}
                            {{ number_format($d['line_tax'] ?? $d['unit_tax_amount'] * $d['quantity'], 2) }}
                        </td>
                        <td class="right">{{ $currency }}
                            {{ number_format($d['sub_total'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table class="totals">
            <tr>
                <td class="right"><strong>Subtotal (neto):</strong></td>
                <td class="right" style="width:120px;">{{ $currency }}
                    {{ number_format($totals['sub_total'] ?? 0, 2) }}
                </td>
            </tr>
            <tr>
                <td class="right"><strong>Impuesto total:</strong></td>
                <td class="right">{{ $currency }} {{ number_format($totals['totalTax'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="right"><strong>Total:</strong></td>
                <td class="right">{{ $currency }} {{ number_format($totals['total'] ?? 0, 2) }}</td>
            </tr>
        </table>
        <div class="footer">
            <p style="color:#b91c1c;"><strong>Esta proforma es válida por {{ $validityDays }} días y únicamente con los precios y
                    descuentos aquí detallados.</strong></p>
        </div>
        <!-- Condiciones Generales -->
        <div class="legal" style="margin-top:20px; font-size:13px; color:#555;">
            <h4 style="margin-bottom:8px;">Condiciones Generales</h4>
            <ul style="list-style:disc; padding-left:20px;">
                <li>Vigencia de {{ $validityDays }} días.</li>
                <li>Garantía para cada producto según especificaciones del fabricante.</li>
                <li>Oferta a nombre de:
                    {{ trim(($entity?->first_name ?? '') . ' ' . ($entity?->last_name ?? '')) ?: $entity?->short_name ?? '' }}.
                </li>
            </ul>
        </div>
        <!-- Firmas -->
        <div class="signature" style="margin-top:30px; display:flex; justify-content:space-between;">
            <div style="text-align:center;">
                <p>______________________________</p>
                <p>Firma del Vendedor<br>{{ $user?->name ?? ($user?->short_name ?? '') }}</p>
            </div>
        </div>
    </div>
</body>

</html>