<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\TenantContextService;

class TenantMiddleware
{
    public function handle(Request $request): ?Response
    {
        $tenant = TenantContextService::getActiveTenant();
        if (!$tenant) {
            $user = current_user();
            if ($user) {
                $tenants = TenantContextService::getUserTenants((int)$user['id']);
                if (!empty($tenants)) {
                    TenantContextService::setActiveTenant((int)$tenants[0]['id'], (int)$user['id']);
                    $tenant = TenantContextService::getActiveTenant();
                }
            }
        }

        if (!$tenant) {
            if ($request->isAjax()) {
                return Response::error("No active workspace found for user.", "FORBIDDEN", null, 403);
            }
            if (is_platform_admin()) {
                return Response::redirect(url('/admin/platform'));
            }
            return Response::redirect(url('/login'));
        }

        return null;
    }
}
