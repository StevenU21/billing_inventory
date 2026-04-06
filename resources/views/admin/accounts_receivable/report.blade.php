<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cuenta por Cobrar #{{ $ar->id }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <style>
        /* Page setup */
        @page {
            margin: 90px 40px 70px 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        /* Header & footer */
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

        /* Content layout */
        .section {
            margin-bottom: 14px;
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

        /* Cards */
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

        /* Tables */
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

        tfoot {
            display: table-footer-group;
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

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .no-border td,
        .no-border th {
            border: none;
        }

        .striped tbody tr:nth-child(even) td {
            background: #fafafa;
        }

        /* Totals badges */
        .totals {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-top: 8px;
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

        /* Signatures */
        .sigs {
            margin-top: 18px;
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .sig {
            display: table-cell;
            text-align: center;
            padding: 10px;
        }

        .sig .line {
            margin-top: 30px;
            border-top: 1px solid #999;
            width: 90%;
            margin-left: auto;
            margin-right: auto;
        }

        .sig .who {
            font-size: 11px;
            color: #666;
            margin-top: 6px;
        }

        /* Footer content */
        .page-num:after {
            content: counter(page) " / " counter(pages);
        }
    </style>
</head>

<body>
    @php
        $e = $ar->entity;
        $clientName = $e?->short_name ?: trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? ''));
        $balance = round(($ar->amount_due ?? 0) - ($ar->amount_paid ?? 0), 2);
        $today = \Illuminate\Support\Carbon::now()->format('d/m/Y H:i');
    @endphp

    <!-- Header -->
    <header>
        <table class="no-border" style="width:100%">
            <tr>
                <td style="width:60%">
                    <div class="brand">
                        {{-- Optional logo placeholder; render if available --}}
                        @if (!empty($company?->logo_url))
                            <img src="{{ $company->logo_url }}" alt="Logo" style="height:38px;">
                        @endif
                        <div>
                            <div class="name">{{ $company?->name ?? 'Empresa' }}</div>
                            <div class="meta">{{ $company?->address ?? '' }}@if (!empty($company?->phone))
                                    · Tel: {{ $company->phone }}
                                @endif
                            </div>
                        </div>
                    </div>
                </td>
                <td class="doc-title" style="width:40%">
                    <div class="t1">Cuenta por Cobrar</div>
                    <div class="t2">#{{ $ar->id }} · Emitido: {{ $today }}</div>
                </td>
            </tr>
        </table>
        <div class="hf-line"></div>

    </header>

    <!-- Footer -->
    <footer>
        <div class="hf-line"></div>
        <table class="no-border" style="width:100%">
            <tr>
                <td style="width:60%; color:#777;">Generado por {{ config('app.name') }}</td>
                <td class="text-right" style="width:40%; color:#777;">Página <span class="page-num"></span></td>
            </tr>
        </table>
    </footer>

    <main>
        <!-- Client & Summary -->
        <div class="section">
            <div class="grid">
                <div class="col col-6">
                    <div class="card">
                        <h4>Cliente</h4>
                        <div class="row"><span class="muted">Nombre:</span> <span
                                class="strong">{{ $clientName }}</span></div>
                        <div class="row"><span class="muted">Documento:</span> <span
                                class="strong">{{ $e?->document_number ?? '-' }}</span></div>
                        <div class="row"><span class="muted">Teléfono:</span> <span
                                class="strong">{{ $e?->phone ?? '-' }}</span></div>
                        <div class="row"><span class="muted">Email:</span> <span
                                class="strong">{{ $e?->email ?? '-' }}</span></div>
                    </div>
                </div>
                <div class="col col-6">
                    <div class="card">
                        <h4>Resumen</h4>
                        <div class="row"><span class="muted">Venta:</span> <span
                                class="strong">#{{ $ar->sale?->id ?? '-' }}{{ $ar->sale?->sale_date ? ' (' . $ar->sale->sale_date . ')' : '' }}</span>
                        </div>
                        <div class="row"><span class="muted">Estado:</span> <span
                                class="strong">{{ $ar->translated_status }}</span></div>
                        <div class="totals">
                            <div class="box">
                                <div class="label">Monto total</div>
                                <div class="value">C$ {{ number_format($ar->amount_due, 2) }}</div>
                            </div>
                            <div class="box">
                                <div class="label">Pagado</div>
                                <div class="value">C$ {{ number_format($ar->amount_paid, 2) }}</div>
                            </div>
                            <div class="box">
                                <div class="label">Saldo</div>
                                <div class="value">C$ {{ number_format($balance, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sale details -->
        <div class="section">
            <h4
                style="margin: 0 0 6px 0; font-size: 13px; color: #444; text-transform: uppercase; letter-spacing: .3px;">
                Detalle de Venta</h4>
            <table class="striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-right">P. Unit</th>
                        <th class="text-right">Sub Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ar->sale?->saleDetails ?? [] as $d)
                        <tr>
                            <td>
                                {{ $d->productVariant?->product?->name }}
                                {{ $d->productVariant?->sku ? '(' . $d->productVariant->sku . ')' : '' }}
                                @if ($d->productVariant?->color || $d->productVariant?->size)
                                    <br>
                                    <span style="font-size: 10px; color: #666;">
                                        {{ $d->productVariant->color?->name ?? '-' }} /
                                        {{ $d->productVariant->size?->name ?? '-' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">{{ $d->quantity }}</td>
                            <td class="text-right">{{ number_format($d->unit_price, 2) }}</td>
                            <td class="text-right">{{ number_format($d->sub_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center" style="color:#777">Sin detalles</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Payments -->
        <div class="section">
            <h4
                style="margin: 0 0 6px 0; font-size: 13px; color: #444; text-transform: uppercase; letter-spacing: .3px;">
                Pagos</h4>
            <table class="striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Método</th>
                        <th class="text-right">Monto</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasPayments = ($ar->payments && count($ar->payments)); @endphp
                    @forelse ($ar->payments as $p)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($p->payment_date)->format('d/m/Y') }}</td>
                            <td>{{ $p->paymentMethod?->name ?? '-' }}</td>
                            <td class="text-right">{{ number_format($p->amount, 2) }}</td>
                            <td>{{ $p->user?->short_name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center" style="color:#777">Sin pagos aún</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($hasPayments)
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">Total Pagado</th>
                            <th class="text-right">C$ {{ number_format($ar->amount_paid, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <!-- Signatures -->
        <div class="sigs">
            <div class="sig">
                <div class="line"></div>
                <div class="who">Elaborado por</div>
            </div>
            <div class="sig">
                <div class="line"></div>
                <div class="who">Revisado por</div>
            </div>
            <div class="sig">
                <div class="line"></div>
                <div class="who">Recibido por</div>
            </div>
        </div>

        <div class="section" style="margin-top: 12px; color:#777; font-size: 11px;">
            Este documento es un resumen informativo de cuentas por cobrar y pagos asociados. Para fines legales, la
            información
            aquí reflejada corresponde a los registros del sistema a la fecha de emisión.
        </div>
    </main>
</body>

</html>
