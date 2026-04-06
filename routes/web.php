<?php

use App\Http\Controllers\Admin\AccountPayableController;
use App\Http\Controllers\Admin\AccountPayablePaymentController;
use App\Http\Controllers\Admin\AccountReceivableController as AdminAccountReceivableController;
use App\Http\Controllers\Admin\AccountReceivablePaymentController;
use App\Http\Controllers\Admin\AppUpdateController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\AutocompleteController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CashRegisterController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\EntityController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SaleController as AdminSaleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\Admin\UnitMeasureController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Cashier\SaleController as CashierSaleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Exports\PurchaseExportController;
use App\Http\Controllers\Exports\QuotationExportController;
use App\Http\Controllers\Exports\SaleExportController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'auth/login');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {

    // =========================================================================
    // 1. DASHBOARD & SYSTEM CORE
    // =========================================================================

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // --- Profile & Media ---
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/update', [ProfileController::class, 'update'])->name('update');
        Route::delete('/destroy', [ProfileController::class, 'destroy'])->name('destroy');
        Route::get('/avatar/{profile}', [MediaController::class, 'profileAvatar'])->name('avatar');
    });

    // --- Notifications ---
    Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/feed/latest', 'feed')->name('feed');
        Route::post('/mark-all', 'markAll')->name('markAll');
        Route::delete('/purge', 'destroyAll')->name('destroyAll');
        Route::patch('/{notificationId}', 'markAsRead')->whereUuid('notificationId')->name('markAsRead');
        Route::delete('/{notificationId}', 'destroy')->whereUuid('notificationId')->name('destroy');
    });

    // --- Native App Updates ---
    Route::prefix('native-app/updates')->name('native-app.updates.')->controller(AppUpdateController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/history', 'history')->name('history');
        Route::post('/check', 'check')->name('check');
        Route::post('/download', 'download')->name('download');
        Route::post('/install', 'install')->name('install');
    });

    // --- Settings & Configuration ---
    Route::prefix('admin/settings')->name('settings.')->controller(SettingController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/theme', 'updateTheme')->name('updateTheme');
        Route::post('/open-at-login', 'updateOpenAtLogin')->name('updateOpenAtLogin');
        Route::post('/notifications', 'updateNotificationSettings')->name('updateNotificationSettings');
        Route::post('/printer', 'updatePrinterSettings')->name('updatePrinterSettings');
        Route::post('/backup', 'updateBackupSettings')->name('updateBackupSettings');
        Route::post('/backup/select-path', 'selectBackupPath')->name('selectBackupPath');
        Route::post('/quotation', 'updateQuotationSettings')->name('updateQuotationSettings');
    });

    // --- Backups ---
    Route::prefix('admin/backups')->name('backups.')->controller(BackupController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/download', 'download')->name('download');
        Route::post('/restore', 'restore')->name('restore');
        Route::delete('/', 'destroy')->name('destroy');
    });

    // --- Audits ---
    Route::prefix('audits')->name('audits.')->controller(AuditController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{subjectType}/{subjectId}', 'show')->name('show');
    });

    // =========================================================================
    // 2. MASTER CATALOGS (Diccionarios de datos)
    // =========================================================================

    // Companies
    Route::get('/company/logo/{company}', [MediaController::class, 'companyLogo'])->name('company.logo');
    Route::resource('companies', CompanyController::class)->except(['destroy']);

    // Helper para agrupar catálogos simples
    $catalogs = [
        'categories' => CategoryController::class,
        'brands' => BrandController::class,
        'unit_measures' => UnitMeasureController::class,
        'payment_methods' => PaymentMethodController::class,
        'taxes' => TaxController::class,
    ];

    foreach ($catalogs as $uri => $controller) {
        Route::get("$uri/search", [$controller, 'search'])->name("$uri.search");
        Route::resource($uri, $controller);
    }

    Route::get('brands/category/{category}', [BrandController::class, 'byCategory'])->name('brands.byCategory');

    // =========================================================================
    // 3. ENTITIES & ACCESS MANAGEMENT
    // =========================================================================

    // Users & Permissions
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('autocomplete', [UserController::class, 'autocomplete'])->name('autocomplete');
        // Permissions logic
        Route::get('permissions/{user}', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::post('{user}/permissions/assign', [PermissionController::class, 'assignPermission'])->name('permissions.assign');
        Route::post('{user}/permissions/revoke', [PermissionController::class, 'revokePermission'])->name('permissions.revoke');
    });

    // Reports Exports (contextual, no index page)
    Route::prefix('reports')->name('reports.')->controller(ReportController::class)->group(function () {
        // Direct exports without filter pages
        Route::any('/sales/export', 'exportSales')->name('sales.export');
        Route::any('/inventory/export', 'exportInventory')->name('inventory.export');
        Route::any('/financial/export', 'exportFinancial')->name('financial.export');
    });

    Route::resource('users', UserController::class);

    // Entities (Clients & Suppliers Unified)
    Route::prefix('entities')->name('entities.')->controller(EntityController::class)->group(function () {
        Route::get('autocomplete', 'autocomplete')->name('autocomplete');
    });
    Route::resource('entities', EntityController::class);

    // Products
    Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {
        Route::get('autocomplete', 'autocomplete')->name('autocomplete');
    });
    Route::get('/product/image/{product}', [MediaController::class, 'productImage'])->name('product.image');
    Route::resource('products', ProductController::class);

    // Product Variants
    Route::prefix('product_variants')->name('product_variants.')->controller(ProductVariantController::class)->group(function () {
        Route::get('search', 'search')->name('search');
        Route::get('autocomplete', 'autocomplete')->name('autocomplete');
    });
    Route::get('/product/variant/image/{productVariant}', [MediaController::class, 'variantImage'])->name('product.variant.image');

    // =========================================================================
    // 4. INVENTORY OPERATIONS
    // =========================================================================

    // Inventories (Stock)
    Route::get('inventories/search', [InventoryController::class, 'index'])->name('inventories.search');
    Route::resource('inventories', InventoryController::class);

    // =========================================================================
    // 5. COMMERCIAL & TRANSACTIONS
    // =========================================================================

    // Purchases
    Route::prefix('purchases')->name('purchases.')->controller(PurchaseController::class)->group(function () {
        Route::get('search', 'search')->name('search');
        Route::get('autocomplete', 'autocomplete')->name('autocomplete');
        Route::patch('{purchase}/receive', 'receive')->name('receive'); // Action to receive/approve purchase
    });
    Route::resource('purchases/export', PurchaseExportController::class)
        ->names('purchases.export')
        ->parameters(['export' => 'purchase'])
        ->only(['index', 'show']);
    Route::resource('purchases', PurchaseController::class);

    // Quotations (Admin)
    Route::prefix('admin/quotations')->name('admin.quotations.')->controller(QuotationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/autocomplete', 'autocomplete')->name('autocomplete');
        Route::patch('/{quotation}/accept', 'accept')->name('accept');
        Route::patch('/{quotation}/cancel', 'cancel')->name('cancel');
    });

    // Resource route for generic access if needed, though custom routes above cover most
    Route::resource('quotations', QuotationController::class)->except(['index', 'create', 'store']);
    Route::get('admin/quotations/{quotation}/export', [QuotationExportController::class, 'show'])
        ->name('quotations.export.show');

    // Sales Export Routes
    Route::get('admin/sales/{sale}/receipt', [SaleExportController::class, 'receipt'])->name('sales.export.receipt');
    Route::get('admin/sales/{sale}/export', [SaleExportController::class, 'show'])->name('sales.export.show');

    // Sales (Admin)
    Route::prefix('admin/sales')->name('admin.sales.')->controller(AdminSaleController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');

        Route::get('/brands-by-category', 'brandsByCategory')->name('brandsByCategory');

        Route::get('/{sale}', 'show')->name('show');
        Route::delete('/{sale}', 'destroy')->name('destroy');
    });

    // Sales (Cashier / POS)
    Route::resource('sales', CashierSaleController::class);

    // Accounts Receivable (Admin)
    Route::prefix('admin/accounts-receivable')->name('admin.accounts_receivable.')->group(function () {
        Route::get('/', [AdminAccountReceivableController::class, 'index'])->name('index');
        Route::get('/{accountReceivable}', [AdminAccountReceivableController::class, 'show'])->name('show');
        Route::post('/{accountReceivable}/payments', [AccountReceivablePaymentController::class, 'store'])->name('payments.store');
    });

    // Accounts Payable (Admin)
    Route::prefix('admin/account-payables')->name('admin.account_payables.')->group(function () {
        Route::get('/', [AccountPayableController::class, 'index'])->name('index');
        Route::get('/{accountPayable}', [AccountPayableController::class, 'show'])->name('show');
        Route::post('/{accountPayable}/payments', [AccountPayablePaymentController::class, 'store'])->name('payments.store');
    });

    // =========================================================================
    // 6. CASH REGISTER (Caja Registradora)
    // =========================================================================
    Route::prefix('admin/cash-register')->name('admin.cash-register.')->controller(CashRegisterController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/my-session', 'mySession')->name('my-session');
        Route::get('/last-closing-balance', 'lastClosingBalance')->name('last-closing-balance');
        Route::get('/{session}', 'show')->name('show');
        Route::get('/{session}/close', 'closeForm')->name('close-form');
        Route::post('/{session}/close', 'close')->name('close');
        Route::post('/{session}/suspend', 'suspend')->name('suspend');
        Route::post('/{session}/resume', 'resume')->name('resume');
        Route::get('/{session}/movement', 'movementForm')->name('movement-form');
        Route::post('/{session}/movements', 'recordMovement')->name('movements.store');
    });

    // Autocomplete Routes
    Route::prefix('admin/autocomplete')->name('admin.autocomplete.')->controller(AutocompleteController::class)->group(function () {
        Route::get('/clients', 'clients')->name('clients');
        Route::get('/suppliers', 'suppliers')->name('suppliers');
    });

});
