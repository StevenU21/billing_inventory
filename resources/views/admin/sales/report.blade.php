<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle ?? ('Venta #' . ($singleSale?->id ?? '')) }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <style>
        @page {
            margin: 90px 40px 70px 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 60px;
        }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 40px;
            color: #666;
            font-size: 11px;
        }

        .hf-line {
            border-top: 1px solid #ddd;
            margin-top: 6px;
        }

        .grid {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }

        .col-6 {
            width: 50%;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand .name {
            font-size: 18px;
            font-weight: 700;
            color: #222;
        }

        .brand .meta {
            font-size: 11px;
            color: #555;
            line-height: 1.3;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title .t1 {
            font-size: 16px;
            font-weight: 700;
            color: #222;
        }

        .doc-title .t2 {
            font-size: 12px;
            color: #555;
        }

        .section {
            margin-bottom: 14px;
        }

        .card {
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            padding: 10px 12px;
        }

        .card h4 {
            margin: 0 0 6px 0;
            font-size: 13px;
            color: #444;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .row {
            margin: 3px 0;
        }

        .muted {
            color: #666;
        }

        .strong {
            font-weight: 600;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
        }

        thead {
            display: table-header-group;
        }

        thead th {
            background: #f7f7f7;
            color: #444;
            font-weight: 700;
            font-size: 12px;
        }

        tbody td {
            font-size: 12px;
        }

        .striped tbody tr:nth-child(even) td {
            background: #fafafa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-top: 10px;
        }

        .totals .box {
            display: table-cell;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            padding: 10px;
        }

        .totals .box+.box {
            margin-left: 8px;
        }

        .totals .label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .totals .value {
            font-size: 14px;
            font-weight: 700;
            color: #222;
        }

        .page-num:after {
            content: counter(page) " / " counter(pages);
        }
    </style>
</head>

<body>
    @php
        /** @var \App\Models\Sale|null $sale */
        $sale = $singleSale ?? null;
        $client = $sale?->client;
        $user = $sale?->user;
        $paymentMethod = $sale?->paymentMethod;
    @endphp

    <header>
        <div class="grid">
            <div class="col col-6">
                <div class="brand">
                    <div>
                        <div class="name">{{ $company?->name ?? config('app.name') }}</div>
                        <div class="meta">
                            {{ $company?->address ?? '' }}
                            @if (!empty($company?->phone))
                                <br>Tel: {{ $company->phone }}
                            @endif
                            @if (!empty($company?->email))
                                <br>{{ $company->email }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col col-6">
                <div class="doc-title">
                    <div class="t1">{{ $reportTitle ?? 'Reporte de Venta' }}</div>
                    <div class="t2">Generado: {{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
        <div class="hf-line"></div>
    </header>

    <footer>
        <div class="hf-line"></div>
        <div class="grid" style="margin-top: 6px;">
            <div class="col col-6 muted">
                {{ config('app.name') }}
            </div>
            <div class="col col-6" style="text-align:right;">
                Página <span class="page-num"></span>
            </div>
        </div>
    </footer>

    <main>
        <div class="section">
            <div class="grid" style="gap: 10px;">
                <div class="col col-6" style="padding-right: 6px;">
                    <div class="card">
                        <h4>Venta</h4>
                        <div class="row"><span class="muted">ID:</span> <span class="strong">#{{ $sale?->id }}</span>
                        </div>
                        <div class="row"><span class="muted">Estado:</span> <span
                                class="strong">{{ $sale?->status?->value ?? ($sale?->status ?? '—') }}</span></div>
                        <div class="row"><span class="muted">Tipo:</span> <span
                                class="strong">{{ $sale?->sale_type?->label ?? ($sale?->is_credit ? 'Crédito' : 'Contado') }}</span>
                        </div>
                        <div class="row"><span class="muted">Fecha venta:</span> <span
                                class="strong">{{ $sale?->sale_date?->format('d/m/Y H:i') ?? '—' }}</span></div>
                        <div class="row"><span class="muted">Creado:</span> <span
                                class="strong">{{ $sale?->formatted_created_at ?? ($sale?->created_at?->format('d/m/Y H:i') ?? '—') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col col-6" style="padding-left: 6px;">
                    <div class="card">
                        <h4>Cliente / Pago</h4>
                        <div class="row"><span class="muted">Cliente:</span> <span
                                class="strong">{{ $client?->full_name ?? $client?->short_name ?? 'Cliente General' }}</span>
                        </div>
                        @if (!empty($client?->document_number))
                            <div class="row"><span class="muted">Documento:</span> <span
                                    class="strong">{{ $client->document_number }}</span></div>
                        @endif
                        @if (!empty($client?->phone))
                            <div class="row"><span class="muted">Tel:</span> <span
                                    class="strong">{{ $client->phone }}</span></div>
                        @endif
                        <div class="row"><span class="muted">Método de pago:</span> <span
                                class="strong">{{ $paymentMethod?->name ?? '—' }}</span></div>
                        <div class="row"><span class="muted">Usuario:</span> <span
                                class="strong">{{ $user?->name ?? '—' }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="card">
                <h4>Detalle</h4>
                <table class="striped">
                    <thead>
                        <tr>
                            <th style="width: 36px;">#</th>
                            <th>Producto</th>
                            <th class="text-center" style="width: 90px;">Cantidad</th>
                            <th class="text-right" style="width: 110px;">Precio</th>
                            <th class="text-right" style="width: 90px;">Descuento</th>
                            <th class="text-right" style="width: 110px;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($sale?->saleDetails ?? collect()) as $index => $detail)
                            @php
                                $variant = $detail->productVariant;
                                $productName = $variant?->product?->name ?? 'Ítem';
                                $variantLabel = $variant?->audit_display ? (' - ' . $variant->audit_display) : '';
                                $brand = $variant?->product?->brand?->name ?? '';
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    {{ $productName }}{!! $variantLabel ? '<span class="muted">' . e($variantLabel) . '</span>' : '' !!}
                                    @if ($brand)
                                        <br><span class="muted" style="font-size: 10px;">{{ $brand }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ rtrim(rtrim(number_format((float) ($detail->quantity ?? 0), 4, '.', ','), '0'), '.') }}
                                </td>
                                <td class="text-right">{{ $detail->formatted_unit_price ?? '' }}</td>
                                <td class="text-right">{{ $detail->formatted_discount_amount ?? '—' }}</td>
                                <td class="text-right">{{ $detail->formatted_amount ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center muted">Sin ítems</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="totals">
                    <div class="box">
                        <div class="label">Subtotal</div>
                        <div class="value">{{ $sale?->formatted_sub_total ?? '' }}</div>
                    </div>
                    <div class="box">
                        <div class="label">Descuento</div>
                        <div class="value">{{ $sale?->formatted_discount_total ?? '' }}</div>
                    </div>
                    <div class="box">
                        <div class="label">Impuesto</div>
                        <div class="value">{{ $sale?->formatted_tax_amount ?? ($sale?->formatted_tax ?? '') }}</div>
                    </div>
                    <div class="box">
                        <div class="label">Total</div>
                        <div class="value">{{ $sale?->formatted_total ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>