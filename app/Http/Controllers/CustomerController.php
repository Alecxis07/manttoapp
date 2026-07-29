<?php

namespace App\Http\Controllers;

use App\Actions\Customers\CreateCustomer;
use App\Actions\Customers\DeactivateCustomer;
use App\Actions\Customers\UpdateCustomer;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->with('defaultFiscalProfile')
            ->search($request->string('search')->toString() ?: null)
            ->when($request->string('status')->toString(), function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->when($request->string('type')->toString(), function ($query, string $type): void {
                $query->where('type', $type);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Customer $customer): array => $this->customerSummary($customer));

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'type' => $request->string('type')->toString(),
            ],
            'statuses' => $this->statusOptions(),
            'types' => $this->typeOptions(),
            'can' => [
                'create' => $request->user()?->can('create', Customer::class) ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Customer::class);

        return Inertia::render('Customers/Create', [
            'statuses' => $this->statusOptions(),
            'types' => $this->typeOptions(),
        ]);
    }

    public function store(StoreCustomerRequest $request, CreateCustomer $createCustomer): RedirectResponse
    {
        $customer = $createCustomer->handle($request->toDto(), $request->user());

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Cliente creado correctamente.'));
    }

    public function show(Customer $customer): Response
    {
        $this->authorize('view', $customer);

        $customer->load(['fiscalProfiles', 'creator', 'updater']);

        return Inertia::render('Customers/Show', [
            'customer' => $this->customerDetail($customer),
            // Hooks for related modules (Fase 3+); empty until those models exist.
            'vehicles' => $this->relatedVehicles($customer),
            'documents' => [
                'maintenance_orders' => [],
                'quotations' => [],
                'billing_requests' => [],
            ],
            'can' => [
                'update' => request()->user()?->can('update', $customer) ?? false,
                'delete' => request()->user()?->can('delete', $customer) ?? false,
            ],
        ]);
    }

    public function edit(Customer $customer): Response
    {
        $this->authorize('update', $customer);

        $customer->load('fiscalProfiles');

        return Inertia::render('Customers/Edit', [
            'customer' => $this->customerDetail($customer),
            'statuses' => $this->statusOptions(),
            'types' => $this->typeOptions(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer, UpdateCustomer $updateCustomer): RedirectResponse
    {
        $updateCustomer->handle($customer, $request->toDto(), $request->user());

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Cliente actualizado correctamente.'));
    }

    public function destroy(Request $request, Customer $customer, DeactivateCustomer $deactivateCustomer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $deactivateCustomer->handle($customer, $request->user());

        return redirect()
            ->route('customers.index')
            ->with('success', __('Cliente desactivado correctamente.'));
    }

    /**
     * Active customers for selectors (RN-CLI-002).
     *
     * @return list<array{id: int, name: string, trade_name: string|null, email: string|null}>
     */
    public function options(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->active()
            ->search($request->string('search')->toString() ?: null)
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'trade_name', 'email']);

        return response()->json([
            'data' => $customers,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function customerSummary(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'type' => $customer->type->value,
            'type_label' => $customer->type->label(),
            'name' => $customer->name,
            'trade_name' => $customer->trade_name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'status' => $customer->status->value,
            'status_label' => $customer->status->label(),
            'rfc' => $customer->defaultFiscalProfile?->rfc,
            'created_at' => $customer->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function customerDetail(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'type' => $customer->type->value,
            'type_label' => $customer->type->label(),
            'name' => $customer->name,
            'trade_name' => $customer->trade_name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'status' => $customer->status->value,
            'status_label' => $customer->status->label(),
            'created_by' => $customer->creator?->only(['id', 'name']),
            'updated_by' => $customer->updater?->only(['id', 'name']),
            'created_at' => $customer->created_at?->toIso8601String(),
            'updated_at' => $customer->updated_at?->toIso8601String(),
            'fiscal_profiles' => $customer->fiscalProfiles
                ->sortByDesc('is_default')
                ->values()
                ->map(fn ($profile): array => [
                    'id' => $profile->id,
                    'legal_name' => $profile->legal_name,
                    'rfc' => $profile->rfc,
                    'tax_regime_code' => $profile->tax_regime_code,
                    'cfdi_use_code' => $profile->cfdi_use_code,
                    'postal_code' => $profile->postal_code,
                    'email' => $profile->email,
                    'is_default' => $profile->is_default,
                ])
                ->all(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function relatedVehicles(Customer $customer): array
    {
        return $customer->vehicles()
            ->with('vehicleType:id,name')
            ->orderBy('license_plate')
            ->get()
            ->map(fn ($vehicle): array => [
                'id' => $vehicle->id,
                'license_plate' => $vehicle->license_plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'vehicle_type' => $vehicle->vehicleType?->name,
                'status' => $vehicle->status->value,
                'status_label' => $vehicle->status->label(),
            ])
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function statusOptions(): array
    {
        return collect(CustomerStatus::cases())
            ->map(fn (CustomerStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function typeOptions(): array
    {
        return collect(CustomerType::cases())
            ->map(fn (CustomerType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->values()
            ->all();
    }
}
