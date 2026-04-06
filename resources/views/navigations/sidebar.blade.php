<x-sidebar>
    <li class="px-6 mt-2 mb-2" x-show="!sidebarCollapsed" x-cloak>
        <span
            class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Analítica</span>
    </li>

    <x-sidebar.group label="Dashboard" icon="fas fa-home" :permissions="['read dashboard']" :active="request()->routeIs('dashboard.index')">
        <x-sidebar.group-item href="{{ route('dashboard.index') }}" icon="fas fa-home" title="Dashboard"
            permission="read dashboard" :active="request()->routeIs('dashboard.index')" />
    </x-sidebar.group>

    <li class="px-6 mt-4 mb-2" x-show="!sidebarCollapsed" x-cloak>
        <span
            class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Operaciones</span>
    </li>

    <x-sidebar.group label="Comercial" icon="fas fa-cash-register" :permissions="['read sales', 'read quotations', 'read purchases']" :active="request()->routeIs('admin.sales.*', 'admin.quotations.*')">

        <x-sidebar.group-item href="{{ route('admin.sales.index') }}" icon="fas fa-receipt" title="Ventas"
            permission="read sales" :active="request()->routeIs('admin.sales.index', 'admin.sales.show')" />

        <x-sidebar.group-item href="{{ route('admin.quotations.index') }}" icon="fas fa-file-invoice"
            title="Cotizaciones" permission="read quotations" :active="request()->routeIs('admin.quotations.*')" />

        <x-sidebar.group-item href="{{ route('purchases.index') }}" icon="fas fa-shopping-cart" title="Compras"
            permission="read purchases" :active="request()->routeIs('purchases.*')" />

    </x-sidebar.group>

    <x-sidebar.group label="Finanzas" icon="fas fa-user-clock" :permissions="['read account_receivables', 'read account_payables', 'read cash_register']"
        :active="request()->routeIs('admin.accounts_receivable.*', 'purchases.*', 'admin.account_payables.*', 'admin.cash-register.*')">

        <x-sidebar.group-item href="{{ route('admin.cash-register.index') }}" icon="fas fa-cash-register"
            title="Caja Registradora" permission="read cash_register"
            :active="request()->routeIs('admin.cash-register.*')" />

        <x-sidebar.group-item href="{{ route('admin.accounts_receivable.index') }}" icon="fas fa-user-clock"
            title="Por cobrar" permission="read account_receivables"
            :active="request()->routeIs('admin.accounts_receivable.*')" />
        <x-sidebar.group-item href="{{ route('admin.account_payables.index') }}" icon="fas fa-file-invoice-dollar"
            title="Por pagar" permission="read account_payables"
            :active="request()->routeIs('admin.account_payables.*')" />

    </x-sidebar.group>

    <x-sidebar.group label="Terceros" icon="fas fa-users" :permissions="['read suppliers', 'read clients']"
        :active="request()->routeIs('entities.*')">

        @if (
                auth()->user()->can('read suppliers') ||
                auth()->user()->can('read clients')
            )
            @php
                $entitiesLabel = 'Proveedores';
                if (
                    auth()->user()->can('read clients') &&
                    auth()->user()->can('read suppliers')
                ) {
                    $entitiesLabel = 'Clientes & Proveedores';
                } elseif (auth()->user()->can('read clients')) {
                    $entitiesLabel = 'Clientes';
                }
            @endphp
            <x-sidebar.group-item href="{{ route('entities.index') }}" icon="fas fa-users" :title="$entitiesLabel"
                :active="request()->routeIs('entities.*')" />
        @endif

    </x-sidebar.group>

    <x-sidebar.group label="Inventario" icon="fas fa-boxes" :permissions="[
        'read products',
        'read inventories',
    ]"    :active="request()->routeIs('products.*', 'inventories.*')">

        <x-sidebar.group-item href="{{ route('products.index') }}" icon="fas fa-tags" title="Productos"
            permission="read products" :active="request()->routeIs('products.*')" />

        <x-sidebar.group-item href="{{ route('inventories.index') }}" icon="fas fa-box-open" title="Inventario"
            permission="read inventories" :active="request()->routeIs('inventories.*')" />

    </x-sidebar.group>

    <li class="px-6 mt-4 mb-2" x-show="!sidebarCollapsed" x-cloak>
        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Sistema</span>
    </li>


    <x-sidebar.group label="Catálogo" icon="fas fa-sign-in-alt" :permissions="['read categories', 'read brands', 'read unit_measures', 'read taxes', 'read payment_methods']" :active="request()->routeIs('categories.*', 'brands.*', 'colors.*', 'sizes.*', 'unit_measures.*', 'taxes.*', 'payment_methods.*')">

        <x-sidebar.group-item href="{{ route('categories.index') }}" icon="fas fa-th-list" title="Categorías"
            permission="read categories" :active="request()->routeIs('categories.*')" />

        <x-sidebar.group-item href="{{ route('brands.index') }}" icon="fas fa-tags" title="Marcas"
            permission="read brands" :active="request()->routeIs('brands.*')" />

        <x-sidebar.group-item href="{{ route('unit_measures.index') }}" icon="fas fa-balance-scale"
            title="Unidades de Medida" permission="read unit_measures"
            :active="request()->routeIs('unit_measures.*')" />

        <x-sidebar.group-item href="{{ route('taxes.index') }}" icon="fas fa-percent" title="Impuestos"
            permission="read taxes" :active="request()->routeIs('taxes.*')" />

        <x-sidebar.group-item href="{{ route('payment_methods.index') }}" icon="fas fa-credit-card"
            title="Métodos de Pago" permission="read payment_methods"
            :active="request()->routeIs('payment_methods.*')" />

    </x-sidebar.group>

    <x-sidebar.group label="Administración" icon="fas fa-cogs" :permissions="[
        'read users',
        'read audits',
        'read companies',
    ]" :active="request()->routeIs(
        'users.*',
        'audits.*',
        'companies.*',
        'notifications.*',
        'backups.*',
        'native-app.updates.*',
    )">

        <x-sidebar.group-item href="{{ route('users.index') }}" icon="fas fa-users-cog" title="Usuarios"
            permission="read users" :active="request()->routeIs('users.*')" />

        <x-sidebar.group-item href="{{ route('audits.index') }}" icon="fas fa-clipboard-list" title="Bitácora"
            permission="read audits" :active="request()->routeIs('audits.*')" />

        @php
            $company = \App\Models\Company::first();
            $companyRoute = $company ? route('companies.show', $company) : route('companies.create');
        @endphp
        <x-sidebar.group-item href="{{ $companyRoute }}" icon="fas fa-building" title="Empresa"
            permission="read companies" :active="request()->routeIs('companies.*')" />

    </x-sidebar.group>

</x-sidebar>
