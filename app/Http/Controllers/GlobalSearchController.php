<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request, GlobalSearchService $search): Response|JsonResponse
    {
        $this->authorize('view-reports');

        $query = $request->string('q')->toString() ?: $request->string('search')->toString();
        $results = $search->search($query);

        if ($request->wantsJson() || $request->boolean('json')) {
            return response()->json($results);
        }

        return Inertia::render('Search/Index', $results);
    }
}
