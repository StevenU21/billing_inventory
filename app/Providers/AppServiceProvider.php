<?php

namespace App\Providers;

use App\Classes\NativePhpWindowOpener;
use App\Contracts\SkuGeneratorInterface;
use App\Listeners\AutoUpdaterEventSubscriber;
use App\Models\AccountReceivable;
use App\Models\AccountReceivablePayment;
use App\Models\Backup;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\Entity;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Municipality;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\Tax;
use App\Models\UnitMeasure;
use App\Models\User;
use App\Observers\InventoryObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductVariantObserver;
use App\Policies\AccountReceivablePaymentPolicy;
use App\Policies\AccountReceivablePolicy;
use App\Policies\AuditPolicy;
use App\Policies\BackupPolicy;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EntityPolicy;
use App\Policies\InventoryMovementPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\MunicipalityPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\PaymentMethodPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductVariantPolicy;
use App\Policies\PurchaseDetailPolicy;
use App\Policies\PurchasePolicy;
use App\Policies\QuotationPolicy;
use App\Policies\SalePolicy;
use App\Policies\SettingPolicy;
use App\Policies\TaxPolicy;
use App\Policies\UnitMeasurePolicy;
use App\Policies\UpdatePolicy;
use App\Policies\UserPolicy;
use App\Services\NativeUpdaterStatusStore;
use App\Services\SkuGeneratorService;
use Deifhelt\LaravelReports\Interfaces\PreviewWindowOpener;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            SkuGeneratorInterface::class,
            SkuGeneratorService::class
        );

        $this->app->singleton(PreviewWindowOpener::class, NativePhpWindowOpener::class);

        // Register BackupService with configuration
        $this->app->singleton(\App\Services\BackupService::class, function ($app) {
            return new \App\Services\BackupService(
                databasePath: config('native-backups.sqlite_path'),
                defaultBackupPath: config('native-backups.backups_path')
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // View Composers
        view()->composer('components.cash-register-widget', \App\View\Composers\CashRegisterWidgetComposer::class);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Activity::class, AuditPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Backup::class, BackupPolicy::class);
        Gate::policy(UnitMeasure::class, UnitMeasurePolicy::class);
        Gate::policy(PaymentMethod::class, PaymentMethodPolicy::class);
        Gate::policy(Tax::class, TaxPolicy::class);
        Gate::policy(Entity::class, EntityPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);

        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Municipality::class, MunicipalityPolicy::class);
        Gate::policy(Inventory::class, InventoryPolicy::class);
        Gate::policy(InventoryMovement::class, InventoryMovementPolicy::class);
        Gate::policy(ProductVariant::class, ProductVariantPolicy::class);
        Gate::policy(Purchase::class, PurchasePolicy::class);
        Gate::policy(PurchaseDetail::class, PurchaseDetailPolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);
        Gate::policy(AccountReceivable::class, AccountReceivablePolicy::class);
        Gate::policy(AccountReceivablePayment::class, AccountReceivablePaymentPolicy::class);
        Gate::policy(Quotation::class, QuotationPolicy::class);
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);
        Gate::policy(NativeUpdaterStatusStore::class, UpdatePolicy::class);
        Gate::policy('settings', SettingPolicy::class);

        Event::subscribe(AutoUpdaterEventSubscriber::class);

        Inventory::observe(InventoryObserver::class);
        Product::observe(ProductObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);
    }
}
