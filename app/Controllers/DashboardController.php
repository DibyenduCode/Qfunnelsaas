<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AnalyticsService;
use App\Services\UsageLimitService;
use App\Services\TenantContextService;

class DashboardController
{
    public function index(Request $request): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();
        $metrics = AnalyticsService::getDashboardMetrics($tenantId);
        $usage = UsageLimitService::getUsageOverview($tenantId);

        return view('dashboard.index', [
            'title' => 'Dashboard Overview — Quiz Funnel SaaS',
            'metrics' => $metrics,
            'usage' => $usage,
        ]);
    }
}
