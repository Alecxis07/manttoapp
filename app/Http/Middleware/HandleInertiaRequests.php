<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'permissions' => fn () => $request->user()?->getPermissionNames()->values()->all() ?? [],
            'roles' => fn () => $request->user()?->getRoleNames()->values()->all() ?? [],
            'can' => fn () => [
                'manageUsers' => $request->user()?->can('users.*') ?? false,
                'viewCustomers' => $request->user()?->can('customers.read') ?? false,
                'createCustomers' => $request->user()?->can('customers.create') ?? false,
                'viewVehicles' => $request->user()?->can('vehicles.read') ?? false,
                'createVehicles' => $request->user()?->can('vehicles.create') ?? false,
                'manageServiceCategories' => $request->user()?->can('service_categories.read') ?? false,
                'manageServices' => $request->user()?->can('service_catalog.read') ?? false,
                'manageParts' => $request->user()?->can('part_catalog.read') ?? false,
                'viewMaintenanceOrders' => $request->user()?->can('maintenance_orders.read') ?? false,
                'createMaintenanceOrders' => $request->user()?->can('maintenance_orders.create') ?? false,
                'viewQuotations' => $request->user()?->can('quotations.read') ?? false,
                'createQuotations' => $request->user()?->can('quotations.create') ?? false,
                'viewBillingRequests' => $request->user()?->can('billing_requests.read') ?? false,
                'createBillingRequests' => $request->user()?->can('billing_requests.create') ?? false,
                'viewReports' => $request->user()?->can('view-reports') ?? false,
                'viewFullReports' => $request->user()?->can('reports.full') ?? false,
                'exportReports' => $request->user()?->can('export-reports') ?? false,
                'manageSettings' => $request->user()?->can('settings.*') ?? false,
                'viewAudit' => $request->user()?->can('audit.view') ?? false,
            ],
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
            ],
        ];
    }
}
