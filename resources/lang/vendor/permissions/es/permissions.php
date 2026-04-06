<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permission Translations - Spanish (ES)
    |--------------------------------------------------------------------------
    |
    | Here you can define the translations for your permissions.
    | The system uses a composite approach: Action + Resource.
    |
    */

    // 1. Special Permissions & Overrides (Full string matches)
    'special' => [
        'assign permissions' => 'Asignar permisos',
        'revoke permissions' => 'Revocar permisos',
        'reactivate users' => 'Reactivar usuarios',
        'generate invoice sales' => 'Generar factura de venta',
        'mark all as read notifications' => 'Marcar todas como leidas',
        'mark as read notifications' => 'Marcar como leida',
        'read suppliers' => 'Ver proveedores',
        'create suppliers' => 'Crear proveedores',
        'update suppliers' => 'Actualizar proveedores',
        'read clients' => 'Ver clientes',
        'create clients' => 'Crear clientes',
        'update clients' => 'Actualizar clientes',
        'export entities' => 'Exportar entidades',
    ],

    // 2. Actions (Verbs)
    'actions' => [
        'create' => 'Crear',
        'read' => 'Ver',
        'update' => 'Actualizar',
        'destroy' => 'Eliminar',
        'assign' => 'Asignar',
        'revoke' => 'Revocar',
        'export' => 'Exportar',
        'generate' => 'Generar',
        'download' => 'Descargar',
        'install' => 'Instalar',
        'check' => 'Revisar',
        'reactivate' => 'Reactivar',
        'delete' => 'Eliminar',
    ],

    // 3. Resources (Nouns)
    'resources' => [
        'users' => 'Usuarios',
        'permissions' => 'Permisos',
        'audits' => 'Auditorias',
        'brands' => 'Marcas',
        'categories' => 'Categorias',
        'backups' => 'Respaldos',
        'companies' => 'Empresas',
        'unit_measures' => 'Unidades de medida',
        'departments' => 'Departamentos',
        'municipalities' => 'Municipios',
        'payment_methods' => 'Metodos de pago',
        'taxes' => 'Impuestos',
        'entities' => 'Entidades',
        'products' => 'Productos',
        'product_variants' => 'Variantes de producto',
        'roles' => 'Roles',
        'warehouses' => 'Bodegas',
        'inventories' => 'Inventarios',
        'inventory_movements' => 'Movimientos de inventario',
        'sizes' => 'Tallas',
        'colors' => 'Colores',
        'purchases' => 'Compras',

        'sales' => 'Ventas',
        'account_receivables' => 'Cuentas por cobrar',
        'payments' => 'Pagos',
        'quotations' => 'Cotizaciones',
        'updates' => 'Actualizaciones',
        'notifications' => 'Notificaciones',
        'settings' => 'Configuraciones',
        'suppliers' => 'Proveedores',
        'clients' => 'Clientes',
    ],

    // 4. Global Dictionary (Fallback)
    'dictionary' => [
        // Common terms or compatibility keys
    ],
];
