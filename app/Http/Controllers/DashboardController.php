<?php

namespace App\Http\Controllers;

use App\Services\DashboardKpiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardKpiService $kpiService): Response
    {
        $user = $request->user();
        $canViewReports = $user?->can('reports.full') || $user?->can('reports.limited');

        return Inertia::render('Dashboard', [
            'kpis' => $canViewReports && $user !== null
                ? $kpiService->forUser($user)
                : null,
            'can' => [
                'viewReports' => (bool) $canViewReports,
                'viewFullReports' => $user?->can('reports.full') ?? false,
            ],
        ]);
    }
}
