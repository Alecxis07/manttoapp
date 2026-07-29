<?php

namespace App\Http\Controllers;

use App\Actions\Catalogs\CreateServiceCatalog;
use App\Actions\Catalogs\DeactivateServiceCatalog;
use App\Actions\Catalogs\DeleteServiceCatalog;
use App\Actions\Catalogs\UpdateServiceCatalog;
use App\Enums\ServiceCatalogType;
use App\Http\Requests\StoreServiceCatalogRequest;
use App\Http\Requests\UpdateServiceCatalogRequest;
use App\Models\ServiceCatalog;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceCatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ServiceCatalog::class);

        $services = ServiceCatalog::query()
            ->with('category:id,name,code')
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->string('active')->toString() === '1', fn ($query) => $query->active())
            ->when($request->string('active')->toString() === '0', fn ($query) => $query->where('is_active', false))
            ->when($request->integer('service_category_id'), function ($query, int $categoryId): void {
                $query->where('service_category_id', $categoryId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ServiceCatalog $service): array => $this->transform($service));

        return Inertia::render('Services/Index', [
            'services' => $services,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'active' => $request->string('active')->toString(),
                'service_category_id' => $request->integer('service_category_id') ?: '',
            ],
            'categories' => $this->categoryOptions(includeInactive: true),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ServiceCatalog::class);

        return Inertia::render('Services/Create', [
            'categories' => $this->categoryOptions(),
            'types' => $this->typeOptions(),
        ]);
    }

    public function store(StoreServiceCatalogRequest $request, CreateServiceCatalog $create): RedirectResponse
    {
        $create->handle($request->toDto());

        return redirect()
            ->route('service-catalog.index')
            ->with('success', __('Servicio creado correctamente.'));
    }

    public function show(ServiceCatalog $serviceCatalog): Response
    {
        $this->authorize('view', $serviceCatalog);

        $serviceCatalog->load('category:id,name,code');

        return Inertia::render('Services/Show', [
            'service' => [
                ...$this->transform($serviceCatalog),
                'in_use' => $serviceCatalog->isInUse(),
                'created_at' => $serviceCatalog->created_at?->toIso8601String(),
                'base_price_note' => __('Precio base referencial (RN-CAT-002): se sugiere al agregar partidas; no modifica documentos existentes.'),
            ],
        ]);
    }

    public function edit(ServiceCatalog $serviceCatalog): Response
    {
        $this->authorize('update', $serviceCatalog);

        return Inertia::render('Services/Edit', [
            'service' => [
                'id' => $serviceCatalog->id,
                'code' => $serviceCatalog->code,
                'description' => $serviceCatalog->description,
                'service_category_id' => $serviceCatalog->service_category_id,
                'type' => $serviceCatalog->type->value,
                'base_price' => $serviceCatalog->base_price,
                'unit_of_measure' => $serviceCatalog->unit_of_measure,
                'estimated_minutes' => $serviceCatalog->estimated_minutes,
                'is_active' => $serviceCatalog->is_active,
            ],
            'categories' => $this->categoryOptions(includeInactiveId: $serviceCatalog->service_category_id),
            'types' => $this->typeOptions(),
        ]);
    }

    public function update(
        UpdateServiceCatalogRequest $request,
        ServiceCatalog $serviceCatalog,
        UpdateServiceCatalog $update,
    ): RedirectResponse {
        $update->handle($serviceCatalog, $request->toDto());

        return redirect()
            ->route('service-catalog.index')
            ->with('success', __('Servicio actualizado correctamente.'));
    }

    public function destroy(
        ServiceCatalog $serviceCatalog,
        DeleteServiceCatalog $delete,
    ): RedirectResponse {
        $this->authorize('delete', $serviceCatalog);

        $delete->handle($serviceCatalog);

        return redirect()
            ->route('service-catalog.index')
            ->with('success', __('Servicio eliminado correctamente.'));
    }

    public function deactivate(
        ServiceCatalog $serviceCatalog,
        DeactivateServiceCatalog $deactivate,
    ): RedirectResponse {
        $this->authorize('update', $serviceCatalog);

        $deactivate->handle($serviceCatalog);

        return redirect()
            ->route('service-catalog.index')
            ->with('success', __('Servicio desactivado correctamente.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function transform(ServiceCatalog $service): array
    {
        return [
            'id' => $service->id,
            'code' => $service->code,
            'description' => $service->description,
            'type' => $service->type->value,
            'type_label' => $service->type->label(),
            'base_price' => $service->base_price,
            'unit_of_measure' => $service->unit_of_measure,
            'estimated_minutes' => $service->estimated_minutes,
            'is_active' => $service->is_active,
            'category' => $service->category ? [
                'id' => $service->category->id,
                'name' => $service->category->name,
                'code' => $service->category->code,
            ] : null,
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    protected function categoryOptions(bool $includeInactive = false, ?int $includeInactiveId = null): array
    {
        return ServiceCategory::query()
            ->when(! $includeInactive, function ($query) use ($includeInactiveId): void {
                $query->where(function ($query) use ($includeInactiveId): void {
                    $query->active();
                    if ($includeInactiveId !== null) {
                        $query->orWhere('id', $includeInactiveId);
                    }
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (ServiceCategory $category): array => [
                'value' => $category->id,
                'label' => "{$category->code} — {$category->name}",
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function typeOptions(): array
    {
        return collect(ServiceCatalogType::cases())
            ->map(fn (ServiceCatalogType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->values()
            ->all();
    }
}
