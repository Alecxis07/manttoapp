<?php

namespace App\Http\Controllers;

use App\Actions\Catalogs\CreateServiceCategory;
use App\Actions\Catalogs\DeactivateServiceCategory;
use App\Actions\Catalogs\DeleteServiceCategory;
use App\Actions\Catalogs\UpdateServiceCategory;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ServiceCategory::class);

        $categories = ServiceCategory::query()
            ->withCount('services')
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->has('active'), function ($query) use ($request): void {
                if ($request->string('active')->toString() === '1') {
                    $query->active();
                } elseif ($request->string('active')->toString() === '0') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ServiceCategory $category): array => [
                'id' => $category->id,
                'code' => $category->code,
                'name' => $category->name,
                'description' => $category->description,
                'is_active' => $category->is_active,
                'services_count' => $category->services_count,
                'in_use' => $category->services_count > 0,
            ]);

        return Inertia::render('ServiceCategories/Index', [
            'categories' => $categories,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'active' => $request->string('active')->toString(),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ServiceCategory::class);

        return Inertia::render('ServiceCategories/Create');
    }

    public function store(StoreServiceCategoryRequest $request, CreateServiceCategory $create): RedirectResponse
    {
        $create->handle($request->toDto());

        return redirect()
            ->route('service-categories.index')
            ->with('success', __('Categoría creada correctamente.'));
    }

    public function show(ServiceCategory $serviceCategory): Response
    {
        $this->authorize('view', $serviceCategory);

        $serviceCategory->loadCount(['services', 'parts']);

        return Inertia::render('ServiceCategories/Show', [
            'category' => [
                'id' => $serviceCategory->id,
                'code' => $serviceCategory->code,
                'name' => $serviceCategory->name,
                'description' => $serviceCategory->description,
                'is_active' => $serviceCategory->is_active,
                'services_count' => $serviceCategory->services_count,
                'parts_count' => $serviceCategory->parts_count,
                'in_use' => $serviceCategory->isInUse(),
                'created_at' => $serviceCategory->created_at?->toIso8601String(),
            ],
        ]);
    }

    public function edit(ServiceCategory $serviceCategory): Response
    {
        $this->authorize('update', $serviceCategory);

        return Inertia::render('ServiceCategories/Edit', [
            'category' => [
                'id' => $serviceCategory->id,
                'code' => $serviceCategory->code,
                'name' => $serviceCategory->name,
                'description' => $serviceCategory->description,
                'is_active' => $serviceCategory->is_active,
            ],
        ]);
    }

    public function update(
        UpdateServiceCategoryRequest $request,
        ServiceCategory $serviceCategory,
        UpdateServiceCategory $update,
    ): RedirectResponse {
        $update->handle($serviceCategory, $request->toDto());

        return redirect()
            ->route('service-categories.index')
            ->with('success', __('Categoría actualizada correctamente.'));
    }

    public function destroy(
        ServiceCategory $serviceCategory,
        DeleteServiceCategory $delete,
    ): RedirectResponse {
        $this->authorize('delete', $serviceCategory);

        $delete->handle($serviceCategory);

        return redirect()
            ->route('service-categories.index')
            ->with('success', __('Categoría eliminada correctamente.'));
    }

    public function deactivate(
        ServiceCategory $serviceCategory,
        DeactivateServiceCategory $deactivate,
    ): RedirectResponse {
        $this->authorize('update', $serviceCategory);

        $deactivate->handle($serviceCategory);

        return redirect()
            ->route('service-categories.index')
            ->with('success', __('Categoría desactivada correctamente.'));
    }
}
