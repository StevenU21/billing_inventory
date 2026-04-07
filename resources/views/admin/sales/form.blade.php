@php
	$currencies = ['NIO' => 'NIO - Córdobas', 'USD' => 'USD - Dólares'];

	$clientOptions = $clientEntities->map(function ($client) {
		return [
			'id' => $client->id,
			'name' => trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? '')),
			'document' => $client->identity_document ?? '',
		];
	})->values();

	$variantOptions = $productVariants->map(function ($variant) {
		$attributes = $variant->attributeValues->pluck('value')->filter()->values()->all();
		$productName = $variant->product?->name ?? 'Producto';
		$variantLabel = count($attributes) > 0 ? ' (' . implode(' / ', $attributes) . ')' : '';

		return [
			'id' => $variant->id,
			'label' => $productName . $variantLabel,
			'sku' => $variant->sku,
			'unit_price' => $variant->price?->getAmount()->toFloat() ?? 0,
			'credit_price' => $variant->credit_price?->getAmount()->toFloat() ?? null,
			'tax_percentage' => (float) ($variant->product?->tax?->percentage ?? 0),
		];
	})->values();

	$initialItems = [];
	if (old('items')) {
		$initialItems = collect(old('items'))->map(function ($item) {
			return [
				'product_variant_id' => (string) ($item['product_variant_id'] ?? ''),
				'quantity' => (float) ($item['quantity'] ?? 1),
				'discount' => (bool) ($item['discount'] ?? false),
				'discount_percentage' => (float) ($item['discount_percentage'] ?? 0),
			];
		})->values()->all();
	}

	$defaultPaymentMethodId = (string) ($methods->firstWhere('is_cash', true)?->id ?? '');
	if ($defaultPaymentMethodId === '' && $methods->isNotEmpty()) {
		$defaultPaymentMethodId = (string) $methods->first()->id;
	}
@endphp

<div
	x-data="saleInvoiceForm({
		clients: @js($clientOptions),
		variants: @js($variantOptions),
		initialItems: @js($initialItems),
		selectedClient: @js((string) old('client_id', '')),
		selectedCurrency: @js(old('currency', 'NIO')),
		selectedPaymentMethod: @js((string) old('payment_method_id', $defaultPaymentMethodId)),
		initialIsCredit: @js((bool) old('is_credit', false))
	})"
	class="flex h-[calc(100dvh-11.5rem)] sm:h-[calc(100dvh-12.5rem)] lg:h-[calc(100dvh-14.5rem)] flex-col gap-3 overflow-hidden [@media(max-height:860px)]:overflow-y-auto"
>
	@include('admin.sales.components.validation-errors')

	<div class="grid min-h-0 flex-1 grid-cols-1 gap-3 overflow-hidden [@media(max-height:860px)]:overflow-y-auto lg:grid-cols-12">
		<div class="flex flex-col gap-3 lg:order-2 lg:col-span-4 lg:min-h-0">
			@include('admin.sales.components.billing-data')
			@include('admin.sales.components.invoice-summary')
		</div>

		<div class="flex flex-col gap-3 lg:order-1 lg:col-span-8 lg:min-h-0">
			@include('admin.sales.components.product-search')
			@include('admin.sales.components.sale-detail-header')
			@include('admin.sales.components.sale-detail-table')
		</div>
	</div>

	@include('admin.sales.components.form-actions')
</div>

@include('admin.sales.components.sale-form-script')
