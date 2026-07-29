<?php

namespace App\Providers;

use App\Models\BillingRequest;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\PartCatalog;
use App\Models\Quotation;
use App\Models\ServiceCatalog;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Vehicle;
use App\Policies\BillingRequestPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\MaintenanceOrderPolicy;
use App\Policies\PartCatalogPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\ServiceCatalogPolicy;
use App\Policies\ServiceCategoryPolicy;
use App\Policies\UserPolicy;
use App\Policies\VehiclePolicy;
use App\Support\DiscountLimiter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction() && ! $this->app->runningUnitTests());

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(ServiceCategory::class, ServiceCategoryPolicy::class);
        Gate::policy(ServiceCatalog::class, ServiceCatalogPolicy::class);
        Gate::policy(PartCatalog::class, PartCatalogPolicy::class);
        Gate::policy(MaintenanceOrder::class, MaintenanceOrderPolicy::class);
        Gate::policy(Quotation::class, QuotationPolicy::class);
        Gate::policy(BillingRequest::class, BillingRequestPolicy::class);

        // Spec 03 §4.3 — admin unlimited, administrativo ≤15%, técnico 0%.
        Gate::define('approve-discount', function (User $user, float|int|string $percent): bool {
            return app(DiscountLimiter::class)->allows($user, $percent);
        });

        Gate::define('view-reports', function (User $user): bool {
            return $user->can('reports.full') || $user->can('reports.limited');
        });

        Gate::define('export-reports', function (User $user): bool {
            return $user->can('exports.full') || $user->can('exports.basic');
        });
    }
}
