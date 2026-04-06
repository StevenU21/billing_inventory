@extends('layouts.app')
@section('title', 'Detalles de la Entidad')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <x-breadcrumb :parents="[
            ['label' => 'Dashboard', 'href' => route('dashboard.index'), 'icon' => 'fa-home'],
            ['label' => 'Clientes y Proveedores', 'href' => route('entities.index')],
        ]" :current="'Detalles'" />

        <div class="mb-6">
            <x-page-header title="Detalles de la Entidad" subtitle="Consulta los datos de la entidad seleccionada."
                icon="fa-id-card">
                <x-page-header.link :href="route('entities.index')" icon="fas fa-arrow-left">
                    Volver
                </x-page-header.link>
            </x-page-header>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                    <div class="p-6 flex flex-col items-center text-center">
                        <div
                            class="w-32 h-32 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-5xl text-purple-600 dark:text-purple-400 border-4 border-white dark:border-gray-800 shadow-lg mb-4">
                            <i class="fas {{ $entity->is_client ? 'fa-user' : 'fa-building' }}"></i>
                        </div>

                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-1">
                            {{ $entity->first_name }} {{ $entity->last_name }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                            {{ $entity->email ?? 'Sin correo electrónico' }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $entity->formatted_phone ?? ($entity->phone ?? 'Sin teléfono') }}
                        </p>

                        <div class="flex flex-wrap justify-center gap-2 mb-6">
                            @if ($entity->is_client)
                                <span
                                    class="px-3 py-1 text-xs font-semibold leading-tight text-white bg-blue-600 rounded-full dark:bg-blue-700">
                                    Cliente
                                </span>
                            @endif
                            @if ($entity->is_supplier)
                                <span
                                    class="px-3 py-1 text-xs font-semibold leading-tight text-white bg-indigo-600 rounded-full dark:bg-indigo-700">
                                    Proveedor
                                </span>
                            @endif
                            @if ($entity->is_active)
                                <span
                                    class="px-3 py-1 text-xs font-semibold leading-tight text-white bg-green-600 rounded-full dark:bg-green-700">
                                    Activo
                                </span>
                            @else
                                <span
                                    class="px-3 py-1 text-xs font-semibold leading-tight text-white bg-red-600 rounded-full dark:bg-red-700">
                                    Inactivo
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700"></div>

                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                            Información del Sistema
                        </h3>
                        <ul class="space-y-2 text-xs">
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">ID Entidad</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">#{{ $entity->id }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Registrado</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300" title="{{ $entity->formatted_created_at }}">{{ $entity->formatted_created_at_date ?? '-' }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Última act.</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300" title="{{ $entity->formatted_updated_at }}">{{ $entity->formatted_updated_at_date ?? '-' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Saldo pendiente</div>
                        @php($saldoClass = ($is_saldo_zero ?? false) ? 'text-gray-500 dark:text-gray-400' : (($is_saldo_positive ?? false) ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400')))
                        <div class="mt-1 text-3xl font-bold {{ $saldoClass }}">{{ $saldo_pendiente?->formatTo('es_NI') ?? '-' }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total vendido</div>
                        <div class="mt-1">
                            <x-money :amount="$sales_total" size="xl" class="font-semibold" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total comprado</div>
                        <div class="mt-1">
                            <x-money :amount="$purchases_total" size="xl" class="font-semibold" />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Última venta</div>
                        @php($lastSale = $last_sale)
                        <div class="mt-1 text-xl font-semibold text-gray-800 dark:text-gray-100">{{ $lastSale?->sale_date?->format('d/m/Y') ?? '-' }}</div>
                        @if($lastSale)
                            <div class="mt-1 flex items-center justify-between text-sm">
                                <a class="text-purple-600 dark:text-purple-400 hover:underline" href="{{ route('admin.sales.show', $lastSale) }}">Ver Factura #{{ $lastSale->id }}</a>
                                <x-money :amount="$lastSale->total" variant="positive" size="sm" />
                            </div>
                        @endif
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Última compra</div>
                        @php($lastPurchase = $last_purchase)
                        <div class="mt-1 text-xl font-semibold text-gray-800 dark:text-gray-100">{{ $lastPurchase?->purchase_date?->format('d/m/Y') ?? '-' }}</div>
                        @if($lastPurchase)
                            <div class="mt-1 flex items-center justify-between text-sm">
                                <a class="text-purple-600 dark:text-purple-400 hover:underline" href="{{ route('purchases.show', $lastPurchase) }}">Ver Compra #{{ $lastPurchase->id }}</a>
                                <x-money :amount="$lastPurchase->total" size="sm" class="text-blue-600 dark:text-blue-400" />
                            </div>
                        @endif
                    </div>
                </div>

                <x-ui.tabs default="perfil">
                    <x-slot:tabs>
                        <x-ui.tabs.button name="perfil">Perfil</x-ui.tabs.button>
                        <x-ui.tabs.button name="historial">Historial</x-ui.tabs.button>
                        <x-ui.tabs.button name="cuenta">Cuenta Corriente</x-ui.tabs.button>
                    </x-slot:tabs>

                    <x-ui.tabs.panel name="perfil">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">RUC</div>
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $entity->ruc ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Cédula</div>
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $entity->formatted_identity_card ?? ($entity->identity_card ?? '-') }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Dirección</div>
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $entity->address ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Municipio</div>
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $entity->municipality->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Departamento</div>
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $entity->municipality->department->name ?? '-' }}</div>
                            </div>
                            @if($entity->description)
                                <div class="md:col-span-2">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Notas</div>
                                    <div class="mt-1 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-3 rounded text-gray-700 dark:text-gray-200">{{ $entity->description }}</div>
                                </div>
                            @endif
                        </div>
                    </x-ui.tabs.panel>

                    <x-ui.tabs.panel name="historial">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Ventas recientes</div>
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($sales as $row)
                                        <div class="py-3 px-2 flex items-center justify-between rounded hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                            <div class="text-sm text-gray-700 dark:text-gray-200">
                                                <a href="{{ route('admin.sales.show', $row) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Venta #{{ $row->id }}</a>
                                                · {{ ($row->sale_date ?? $row->created_at)?->format('d/m/Y') ?? '-' }}
                                            </div>
                                            <x-money :amount="$row->total" size="sm" />
                                        </div>
                                    @empty
                                        <div class="py-6 text-sm text-gray-500">Sin ventas registradas.</div>
                                    @endforelse
                                    <div class="px-2 py-2">{{ $sales->links() }}</div>
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Compras recientes</div>
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($purchases as $row)
                                        <div class="py-3 px-2 flex items-center justify-between rounded hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                            <div class="text-sm text-gray-700 dark:text-gray-200">
                                                <a href="{{ route('purchases.show', $row) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Compra #{{ $row->id }}</a>
                                                · {{ ($row->purchase_date ?? $row->created_at)?->format('d/m/Y') ?? '-' }}
                                            </div>
                                            <x-money :amount="$row->total" size="sm" />
                                        </div>
                                    @empty
                                        <div class="py-6 text-sm text-gray-500">Sin compras registradas.</div>
                                    @endforelse
                                    <div class="px-2 py-2">{{ $purchases->links() }}</div>
                                </div>
                            </div>
                        </div>
                    </x-ui.tabs.panel>

                    <x-ui.tabs.panel name="cuenta">
                        <div class="overflow-x-auto mt-2">
                            <div class="min-w-[880px] overflow-hidden rounded-xl border border-gray-100 dark:border-gray-700">
                                <div class="grid grid-cols-12 px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/60">
                                    <div class="col-span-2">Fecha</div>
                                    <div class="col-span-4">Concepto</div>
                                    <div class="col-span-2 text-right tabular-nums">Debe</div>
                                    <div class="col-span-2 text-right tabular-nums">Haber</div>
                                    <div class="col-span-2 text-right tabular-nums">Saldo</div>
                                </div>
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @php($rows = $ledger)
                                    @php($totals = $ledger_totals)
                                    @forelse($ledger as $row)
                                        <div class="grid grid-cols-12 items-center px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                            <div class="col-span-2 text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $row->date->format('d/m/Y') }}</div>
                                            <div class="col-span-4 text-gray-800 dark:text-gray-100">
                                                @php($routeName = null)
                                                @php($routeParams = [])
                                                @if($row->type === 'invoice')
                                                    @if(!empty($row->saleId))
                                                        @php($routeName = 'admin.sales.show')
                                                        @php($routeParams = ['sale' => $row->saleId])
                                                    @elseif(!empty($row->accountReceivableId))
                                                        @php($routeName = 'admin.accounts_receivable.show')
                                                        @php($routeParams = ['accountReceivable' => $row->accountReceivableId])
                                                    @endif
                                                @elseif($row->type === 'payment' && !empty($row->accountReceivableId))
                                                    @php($routeName = 'admin.accounts_receivable.show')
                                                    @php($routeParams = ['accountReceivable' => $row->accountReceivableId])
                                                @endif
                                                @if($routeName)
                                                    <a href="{{ route($routeName, $routeParams) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $row->description }}</a>
                                                @else
                                                    {{ $row->description }}
                                                @endif
                                            </div>
                                            <div class="col-span-2 text-right whitespace-nowrap">
                                                <x-money :amount="$row->debit" size="sm" class="{{ $row->debit->isZero() ? 'text-gray-500' : '' }}" />
                                            </div>

                                            <div class="col-span-2 text-right whitespace-nowrap">
                                                <x-money :amount="$row->credit" size="sm" class="{{ $row->credit->isZero() ? 'text-gray-500' : '' }}" />
                                            </div>

                                            <div class="col-span-2 text-right whitespace-nowrap">
                                                <x-money :amount="$row->balance" variant="balance" size="sm" />
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-4 py-6 text-sm text-gray-500">Sin movimientos en la cuenta.
                                        </div>
                                    @endforelse
                                    @if($rows->isNotEmpty())
                                        <div class="grid grid-cols-12 items-center px-4 py-3 text-sm font-semibold bg-gray-50 dark:bg-gray-800/60 border-t border-gray-100 dark:border-gray-700">
                                            <div class="col-span-2 text-gray-700 dark:text-gray-200">Totales / Saldo Final</div>
                                            <div class="col-span-4 text-gray-800 dark:text-gray-100"></div>
                                            <div class="col-span-2 text-right whitespace-nowrap font-semibold">
                                                <x-money :amount="$totals->charge" size="sm" class="{{ $totals->charge->isZero() ? 'text-gray-500' : '' }}" />
                                            </div>

                                            <div class="col-span-2 text-right whitespace-nowrap font-semibold">
                                                <x-money :amount="$totals->credit" size="sm" class="{{ $totals->credit->isZero() ? 'text-gray-500' : '' }}" />
                                            </div>

                                            <div class="col-span-2 text-right whitespace-nowrap font-semibold">
                                                <x-money :amount="$totals->balance" variant="balance" size="sm" />
                                            </div>
                                        </div>
                                        <div class="px-4 py-3">{{ $ledger->links() }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-ui.tabs.panel>
                </x-ui.tabs>
            </div>
        </div>
    </div>
@endsection
