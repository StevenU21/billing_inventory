<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base Permissions
    |--------------------------------------------------------------------------
    | Define your resources here. The manager will generate CRUD actions
    | (read, create, update, destroy) automatically.
    */
    'permissions' => [
        // Define your resources here
        'users',
        'permissions' => ['read'],
        'audits' => ['read'],
        'brands',
        'categories',
        'backups' => ['read'],
        'companies' => ['read', 'create', 'update'],
        'unit_measures',
        'departments',
        'municipalities',
        'payment_methods',
        'taxes',
        'entities' => ['destroy'],
        'products',
        'product_variants',
        'inventories',
        'inventory_movements' => ['read'],
        'purchases',

        'sales',
        'account_receivables' => ['read', 'create', 'update'],
        'payments' => ['read', 'create', 'update'],
        'quotations' => ['read', 'create', 'update'],
        'account_payables' => ['read', 'create', 'update'],
        'updates' => ['read'],
        'notifications' => ['read', 'destroy'],
        'settings' => ['read', 'update'],
        'cash_register' => ['read', 'create', 'update'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Special Permissions
    |--------------------------------------------------------------------------
    | Permissions that don't fit the CRUD pattern or standard resources.
    */
    'special_permissions' => [
        // Define special permissions here
        'permissions' => ['assign', 'revoke'],
        'products' => ['export'],
        'product_variants' => ['export'],
        'users' => ['reactivate', 'export'],
        'audits' => ['export'],
        'entities' => [
            'read suppliers',
            'create suppliers',
            'update suppliers',
            'read clients',
            'create clients',
            'update clients',
            'export entities',
        ],
        'inventory_movements' => ['export'],
        'inventories' => ['export'],

        'purchases' => ['export'],
        'sales' => ['export', 'generate invoice'],
        'account_receivables' => ['export'],
        'payments' => ['export'],
        'quotations' => ['export'],
        'account_payables' => ['export'],
        'updates' => ['check', 'download', 'install'],
        'notifications' => ['mark all as read', 'mark as read'],
        'backups' => ['download', 'delete'],
        'cash_register' => ['open', 'close', 'suspend', 'resume', 'deposit', 'withdraw'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles Definition
    |--------------------------------------------------------------------------
    */
    'roles' => [
        'cashier' => [
            'entities' => [
                'read clients',
                'create clients',
                'update clients',
                'export entities',
            ],
            'products' => ['read'],
            'sales' => [
                'read',
                'create',
            ],
            'quotations' => [
                'read',
                'create',
                'update',
            ],
            'account_receivables' => [
                'read',
                'create',
            ],
            'payments' => [
                'read',
                'create',
            ],
            'notifications' => [
                'read',
                'destroy',
                'mark all as read',
                'mark as read',
            ],
            'updates' => [
                'read',
                'download',
                'install',
            ],
            'cash_register' => [
                'read',
                'create',
                'update',
                'open',
                'close',
                'deposit',
                'withdraw',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin Role
    |--------------------------------------------------------------------------
    | The role(s) that bypass all permission checks.
    | Can be a string or an array of strings.
    | Default: 'admin'
    */
    'super_admin_role' => 'admin',
];
