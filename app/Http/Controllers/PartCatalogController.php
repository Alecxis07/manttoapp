<?php

namespace App\Http\Controllers;

use App\Actions\Catalogs\CreatePartCatalog;
use App\Actions\Catalogs\DeactivatePartCatalog;
use App\Actions\Catalogs\DeletePartCatalog;
use App\Actions\Catalogs\UpdatePartCatalog;
use App\Enums\PartCatalogType;
use App\Http\Requests\StorePartCatalogRequest;
use App\Http\Requests\UpdatePartCatalogRequest;
use App\Models\PartCatalog;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartCatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PartCatalog::class);

        $parts = PartCatalog::query()
            ->with('category:id,name,code')
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->string('active')->toString() === '1', fn ($query) => $query->active())
            ->when($request->string('active')->toString() === '0', fn ($query) => $query->where('is_active', false))
            ->when($request->string('type')->toString(), function ($query, string $type): void {
                $query->where('type', $type);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (PartCatalog $part): array => $this->transform($part));

        return Inertia::render('Parts/Index', [
            'parts' => $parts,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'active' => $request->string('active')->toString(),
                'type' => $request->string('type')->toString(),
            ],
            'types' => $this->typeOptions(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', PartCatalog::class);

        return Inertia::render('Parts/Create', [
            'categories' => $this->categoryOptions(),
            'types' => $this->typeOptions(),
        ]);
    }

    public function store(StorePartCatalogRequest $request, CreatePartCatalog $create): RedirectResponse
    {
        $create->handle($request->toDto());

        return redirect()
            ->route('part-catalog.index')
            ->with('success', __('Concepto creado correctamente.'));
    }

    public function show(PartCatalog $partCatalog): Response
    {
        $this->authorize('view', $partCatalog);

        $partCatalog->load('category:id,name,code');

        return Inertia::render('Parts/Show', [
            'part' => [
                ...$this->transform($partCatalog),
                'in_use' => $partCatalog->isInUse(),
                'created_at' => $partCatalog->created_at?->toIso8601String(),
                'base_price_note' => __('Precio base referencial (RN-CAT-002): se sugiere al agregar partidas; no modifica documentos existentes.'),
            ],
        ]);
    }

    public function edit(PartCatalog $partCatalog): Response
    {
        $this->authorize('update', $partCatalog);

        return Inertia::render('Parts/Edit', [
            'part' => [
                'id' => $partCatalog->id,
                'code' => $partCatalog->code,
                'description' => $partCatalog->description,
                'type' => $partCatalog->type->value,
                'service_category_id' => $partCatalog->service_category_id,
                'base_price' => $partCatalog->base_price,
                'unit_of_measure' => $partCatalog->unit_of_measure,
                'is_active' => $partCatalog->is_active,
            ],
            'categories' => $this->categoryOptions(includeInactiveId: $partCatalog->service_category_id),
            'types' => $this->typeOptions(),
        ]);
    }

    public function update(
        UpdatePartCatalogRequest $request,
        PartCatalog $partCatalog,
        UpdatePartCatalog $update,
    ): RedirectResponse {
        $update->handle($partCatalog, $request->toDto());

        return redirect()
            ->route('part-catalog.index')
            ->with('success', __('Concepto actualizado correctamente.'));
    }

    public function destroy(
        PartCatalog $partCatalog,
        DeletePartCatalog $delete,
    ): RedirectResponse {
        $this->authorize('delete', $partCatalog);

        $delete->handle($partCatalog);

        return redirect()
            ->route('part-catalog.index')
            ->with('success', __('Concepto eliminado correctamente.'));
    }

    public function deactivate(
        PartCatalog $partCatalog,
        DeactivatePartCatalog $deactivate,
    ): RedirectResponse {
        $this->authorize('update', $partCatalog);

        $deactivate->handle($partCatalog);

        return redirect()
            ->route('part-catalog.index')
            ->with('success', __('Concepto desactivado correctamente.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function transform(PartCatalog $part): array
    {
        return [
            'id' => $part->id,
            'code' => $part->code,
            'description' => $part->description,
            'type' => $part->type->value,
            'type_label' => $part->type->label(),
            'base_price' => $part->base_price,
            'unit_of_measure' => $part->unit_of_measure,
            'is_active' => $part->is_active,
            'category' => $part->category ? [
                'id' => $part->category->id,
                'name' => $part->category->name,
                'code' => $part->category->code,
            ] : null,
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    protected function categoryOptions(?int $includeInactiveId = null): array
    {
        return ServiceCategory::query()
            ->where(function ($query) use ($includeInactiveId): void {
                $query->active();
                if ($includeInactiveId !== null) {
                    $query->orWhere('id', $includeInactiveId);
                }
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
        return collect(PartCatalogType::cases())
            ->map(fn (PartCatalogType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->values()
            ->all();
    }
}
