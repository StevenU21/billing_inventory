<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Factura #{{ $sale->id }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            color: #000;
            font-size: 12px;
            line-height: 1.2;
            padding: 5mm;
        }

        .receipt-container {
            width: 100%;
            max-width: 72mm;
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .header {
            margin-bottom: 2mm;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .company-info {
            font-size: 10px;
            color: #333;
        }

        .invoice-details {
            margin: 3mm 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 2mm 0;
            font-size: 10px;
        }

        .customer-info {
            margin-bottom: 3mm;
            font-size: 11px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .items-table th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding-bottom: 1mm;
            font-size: 10px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 2mm 0;
            vertical-align: top;
        }

        .item-row {
            border-bottom: 1px dotted #ccc;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-name {
            font-weight: bold;
            display: block;
        }

        .item-meta {
            font-size: 10px;
            color: #444;
            margin-top: 1px;
        }

        .totals {
            margin-top: 3mm;
            border-top: 1px dashed #000;
            padding-top: 2mm;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        .grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px dashed #000;
            padding-top: 2mm;
            margin-top: 1mm;
        }

        .footer {
            margin-top: 5mm;
            text-align: center;
            font-size: 10px;
        }

        .signatures {
            margin-top: 8mm;
            width: 100%;
        }

        .sig-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto;
            padding-top: 2px;
        }
    </style>
    @php /* Currency symbol comes from formatted_* accessors via HasFormattedMoney */ @endphp
</head>

<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header text-center">
            <div class="company-name uppercase">{{ $company->name ?? 'EMPRESA' }}</div>
            <div class="company-info">
                {{ $company->address ?? '' }}<br>
                Tel: {{ $company->phone ?? '' }}<br>
                RUC: {{ $company->tax_id ?? '' }}
            </div>
        </div>

        <!-- Invoice Meta -->
        <div class="invoice-details text-center uppercase">
            <div>FACTURA #: <strong>{{ $sale->id }}</strong></div>
            <div>Fecha: {{ $sale->created_at->format('d/m/Y h:i A') }}</div>
            <div>Tipo: {{ $sale->is_credit ? 'CRÉDITO' : 'CONTADO' }}</div>
            <div>Vendedor: {{ $sale->user?->short_name ?? substr($sale->user?->first_name ?? 'Admin', 0, 10) }}</div>
        </div>

        <!-- Customer -->
        <div class="customer-info">
            <strong>CLIENTE:</strong> {{ $sale->client?->full_name ?? 'CLIENTE GENERAL' }}
        </div>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-left">DESCRIPCIÓN</th>
                    <th class="text-center" style="width: 15%">IVA</th>
                    <th class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($details as $d)
                    @php
                        $variant = $d->productVariant;
                        $product = $variant?->product;
                        $name = $product?->name ?? 'Producto';
                        $variantDisplay = $d->variant_display;
                        $metaString = ($variantDisplay && $variantDisplay !== 'Simple') ? $variantDisplay : '';
                    @endphp
                    <tr>
                        <td colspan="3" style="padding-top: 2mm; padding-bottom: 0;">
                            <span class="item-name">{{ $name }}</span>
                            @if (!empty($metaString))
                                <div class="item-meta">{{ $metaString }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr class="item-row">
                        <td style="padding-top: 1px; padding-bottom: 2mm; font-size: 10px; color: #333;">
                            {{ $d->quantity + 0 }} x {{ $d->formatted_unit_price }}
                        </td>
                        <td class="text-center" style="padding-top: 1px; padding-bottom: 2mm; font-size: 10px; vertical-align: top;">
                            {{ ($d->tax_percentage + 0) }}%
                        </td>
                        <td class="text-right bold" style="padding-top: 1px; padding-bottom: 2mm; vertical-align: top;">
                            {{ $d->formatted_sub_total }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals text-right">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ $sale->formatted_sub_total }}</span>
            </div>

            @if (($sale->discount_total?->getMinorAmount()?->toInt() ?? 0) > 0)
                <div class="total-row">
                    <span>Descuento:</span>
                    <span>-{{ $sale->formatted_discount_total }}</span>
                </div>
            @endif

            @if (($sale->tax_amount?->getMinorAmount()?->toInt() ?? 0) > 0)
                <div class="total-row">
                    <span>IVA:</span>
                    <span>{{ $sale->formatted_tax_amount }}</span>
                </div>
            @endif

            <div class="total-row grand-total">
                <span class="uppercase">Total:</span>
                <span>{{ $sale->formatted_total }}</span>
            </div>

            <div class="text-left" style="margin-top: 5px; font-size: 10px;">
                Artículos: {{ $details->sum('quantity') }}
            </div>

            @if($sale->is_credit)
                @php $ar = $sale->accountReceivable; @endphp
                <div class="total-row" style="margin-top: 5px; font-weight: bold;">
                    <span>Saldo Pendiente:</span>
                    <span>{{ $ar ? $ar->formatted_balance : $sale->formatted_total }}</span>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>¡Gracias por su compra!</p>
            <p>No se aceptan devoluciones después de 30 días.</p>
        </div>

        <div class="signatures">
            <table style="width: 100%">
                <tr>
                    <td class="text-center" style="width: 45%;">
                        <div class="sig-line"></div>
                        Firma Vendedor
                    </td>
                    <td style="width: 10%"></td>
                    <td class="text-center" style="width: 45%;">
                        <div class="sig-line"></div>
                        Firma Cliente
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
