<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BillingRequestController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\MaintenanceOrderController;
use App\Http\Controllers\PartCatalogController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceCatalogController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/search', GlobalSearchController::class)->name('search');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/vehicle-history', [ReportController::class, 'vehicleHistory'])->name('vehicle-history');
        Route::get('/vehicle-history/pdf', [ReportController::class, 'exportVehicleHistoryPdf'])->name('vehicle-history.pdf');
        Route::get('/orders', [ReportController::class, 'ordersByPeriod'])->name('orders');
        Route::get('/quotations', [ReportController::class, 'quotationsByStatus'])->name('quotations');
    });

    Route::resource('users', UserController::class);

    Route::get('customers/options', [CustomerController::class, 'options'])->name('customers.options');
    Route::resource('customers', CustomerController::class);

    Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('vehicles/search', [VehicleController::class, 'search'])->name('vehicles.search');
    Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');

    Route::scopeBindings()->group(function () {
        Route::get('customers/{customer}/vehicles/create', [VehicleController::class, 'create'])
            ->name('customers.vehicles.create');
        Route::post('customers/{customer}/vehicles', [VehicleController::class, 'store'])
            ->name('customers.vehicles.store');
        Route::get('customers/{customer}/vehicles/{vehicle}', [VehicleController::class, 'show'])
            ->name('customers.vehicles.show');
        Route::get('customers/{customer}/vehicles/{vehicle}/history/pdf', [VehicleController::class, 'exportHistoryPdf'])
            ->name('customers.vehicles.history.pdf');
        Route::get('customers/{customer}/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])
            ->name('customers.vehicles.edit');
        Route::put('customers/{customer}/vehicles/{vehicle}', [VehicleController::class, 'update'])
            ->name('customers.vehicles.update');
        Route::delete('customers/{customer}/vehicles/{vehicle}', [VehicleController::class, 'destroy'])
            ->name('customers.vehicles.destroy');
    });

    Route::resource('service-categories', ServiceCategoryController::class);
    Route::post('service-categories/{service_category}/deactivate', [ServiceCategoryController::class, 'deactivate'])
        ->name('service-categories.deactivate');

    Route::resource('service-catalog', ServiceCatalogController::class);
    Route::post('service-catalog/{service_catalog}/deactivate', [ServiceCatalogController::class, 'deactivate'])
        ->name('service-catalog.deactivate');

    Route::resource('part-catalog', PartCatalogController::class);
    Route::post('part-catalog/{part_catalog}/deactivate', [PartCatalogController::class, 'deactivate'])
        ->name('part-catalog.deactivate');

    // MOD-005 — Órdenes de mantenimiento (Fase 5)
    Route::get('maintenance-orders', [MaintenanceOrderController::class, 'index'])->name('maintenance-orders.index');
    Route::get('maintenance-orders/create', [MaintenanceOrderController::class, 'create'])->name('maintenance-orders.create');
    Route::post('maintenance-orders', [MaintenanceOrderController::class, 'store'])->name('maintenance-orders.store');
    Route::get('maintenance-orders/{maintenance_order}', [MaintenanceOrderController::class, 'show'])->name('maintenance-orders.show');
    Route::put('maintenance-orders/{maintenance_order}', [MaintenanceOrderController::class, 'update'])->name('maintenance-orders.update');
    Route::post('maintenance-orders/{maintenance_order}/transition', [MaintenanceOrderController::class, 'transition'])
        ->name('maintenance-orders.transition');
    Route::post('maintenance-orders/{maintenance_order}/diagnose', [MaintenanceOrderController::class, 'diagnose'])
        ->name('maintenance-orders.diagnose');
    Route::post('maintenance-orders/{maintenance_order}/items', [MaintenanceOrderController::class, 'storeItem'])
        ->name('maintenance-orders.items.store');
    Route::post('maintenance-orders/{maintenance_order}/parts', [MaintenanceOrderController::class, 'storePart'])
        ->name('maintenance-orders.parts.store');
    Route::post('maintenance-orders/{maintenance_order}/reopen', [MaintenanceOrderController::class, 'reopen'])
        ->name('maintenance-orders.reopen');
    Route::post('maintenance-orders/{maintenance_order}/attachments', [MaintenanceOrderController::class, 'storeAttachment'])
        ->name('maintenance-orders.attachments.store');

    // MOD-008 — Cotizaciones (Fase 7)
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::get('quotations/customers/{customer}/vehicles', [QuotationController::class, 'vehicles'])
        ->name('quotations.customer-vehicles');
    Route::post('quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::post('quotations/{quotation}/version', [QuotationController::class, 'version'])->name('quotations.version');
    Route::post('quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
    Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
    Route::post('quotations/{quotation}/cancel', [QuotationController::class, 'cancel'])->name('quotations.cancel');
    Route::resource('quotations', QuotationController::class)->except(['destroy']);

    // MOD-009 — Solicitudes de facturación (Fase 8)
    Route::post('billing-requests/{billing_request}/submit', [BillingRequestController::class, 'submit'])
        ->name('billing-requests.submit');
    Route::post('billing-requests/{billing_request}/transition', [BillingRequestController::class, 'transition'])
        ->name('billing-requests.transition');
    Route::post('billing-requests/{billing_request}/process', [BillingRequestController::class, 'process'])
        ->name('billing-requests.process');
    Route::resource('billing-requests', BillingRequestController::class)->except(['destroy']);

    // MOD-012 / MOD-014 — Auditoría y configuración (Fase 10)
    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
});
