<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Resolvers
    |--------------------------------------------------------------------------
    |
    | Define the mapping between activity log fields and their corresponding
    | Eloquent models. This allows the package to automatically preload
    | related models.
    |
    */
    'resolvers' => [
        // Common case: resolve user_id foreign keys in activity properties.
        'user_id' => \App\Models\User::class,

        // Core domain foreign keys
        'client_id' => \App\Models\Entity::class,
        'entity_id' => \App\Models\Entity::class,
        'supplier_id' => \App\Models\Entity::class,

        'product_id' => \App\Models\Product::class,
        'product_variant_id' => \App\Models\ProductVariant::class,

        'category_id' => \App\Models\Category::class,
        'brand_id' => \App\Models\Brand::class,
        'department_id' => \App\Models\Department::class,
        'municipality_id' => \App\Models\Municipality::class,
        'payment_method_id' => \App\Models\PaymentMethod::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Label Attributes
    |--------------------------------------------------------------------------
    |
    | Specify which attribute should be used as the display label for each
    | resolved model. You can use standard attributes or accessors.
    |
    */
    'label_attribute' => [
        \App\Models\User::class => 'full_name',

        \App\Models\Entity::class => 'full_name',

        // Prefer audit_display where the model provides it
        \App\Models\Inventory::class => 'audit_display',
        \App\Models\InventoryMovement::class => 'audit_display',
        \App\Models\Sale::class => 'audit_display',
        \App\Models\SaleDetail::class => 'audit_display',
        \App\Models\AccountReceivable::class => 'audit_display',
        \App\Models\Purchase::class => 'audit_display',
        \App\Models\PurchaseDetail::class => 'audit_display',
        \App\Models\Quotation::class => 'audit_display',
        \App\Models\QuotationDetail::class => 'audit_display',
        \App\Models\ProductAttribute::class => 'audit_display',
        \App\Models\ProductPriceHistory::class => 'audit_display',
        \App\Models\Profile::class => 'audit_display',

        \App\Models\Product::class => 'name',
        \App\Models\ProductVariant::class => 'audit_display',

        \App\Models\Category::class => 'name',
        \App\Models\Brand::class => 'name',
        \App\Models\Department::class => 'name',
        \App\Models\Municipality::class => 'name',
        \App\Models\PaymentMethod::class => 'name',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hidden Attributes
    |--------------------------------------------------------------------------
    |
    | Attributes that should be excluded from the presentation DTOs, specifically
    | useful for diff views.
    |
    */
    'hidden_attributes' => [
        'password',
        'remember_token',
        'updated_at',
        'created_at',
        'deleted_at',
    ],
];
