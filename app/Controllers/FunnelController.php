<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\FunnelService;
use App\Services\FunnelPublishService;
use App\Services\FormSchemaService;
use App\Services\TenantContextService;
use App\Services\AuthorizationService;

class FunnelController
{
    public function index(Request $request): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();
        $search = $request->query('search');
        $status = $request->query('status');
        $page = (int)$request->query('page', 1);

        $funnels = FunnelService::listFunnels($tenantId, $search, $status, $page, 10);

        return view('funnels.index', [
            'title' => 'Funnels — Quiz Funnel SaaS',
            'funnels' => $funnels,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function showCreate(Request $request): Response
    {
        if (!AuthorizationService::canCreateFunnel()) {
            Session::setFlash('error', 'You do not have permission to create funnels.');
            return Response::redirect(url('/admin/funnels'));
        }

        return view('funnels.create', [
            'title' => 'Create New Funnel — Quiz Funnel SaaS',
        ]);
    }

    public function store(Request $request): Response
    {
        if (!AuthorizationService::canCreateFunnel()) {
            return Response::error("Permission denied.", "FORBIDDEN", null, 403);
        }

        $tenantId = TenantContextService::getActiveTenantId();
        $user = current_user();

        try {
            $funnel = FunnelService::createFunnel($tenantId, $request->all(), (int)$user['id']);
            Session::setFlash('success', "Funnel '{$funnel['name']}' created successfully.");
            return Response::redirect(url("/admin/funnels/{$funnel['id']}/builder"));
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            Session::set('_old_input', $request->all());
            return Response::redirect(url('/admin/funnels/create'));
        }
    }

    public function editSettings(Request $request, string $id): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();
        $funnel = FunnelService::getFunnel($tenantId, (int)$id);

        if (!$funnel) {
            Session::setFlash('error', 'Funnel not found.');
            return Response::redirect(url('/admin/funnels'));
        }

        $formFields = FormSchemaService::getFormFields($tenantId, (int)$id);
        $settings = json_decode($funnel['settings_json'] ?? '{}', true) ?: [];

        return view('funnels.settings', [
            'title' => "Settings: {$funnel['name']} — Quiz Funnel SaaS",
            'funnel' => $funnel,
            'formFields' => $formFields,
            'settings' => $settings,
        ]);
    }

    public function updateSettings(Request $request, string $id): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();
        $user = current_user();

        try {
            $data = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'settings' => [
                    'theme' => $request->input('theme', 'light'),
                    'accent_color' => $request->input('accent_color', '#5B5BD6'),
                    'logo_url' => trim($request->input('logo_url', '')),
                    'company_name' => trim($request->input('company_name', '')),
                    'white_label' => !empty($request->input('white_label')),
                    'footer_text' => trim($request->input('footer_text', '')),
                    'trust_badge_text' => trim($request->input('trust_badge_text', '')),
                    'enable_back_button' => !empty($request->input('enable_back_button')),
                    'show_progress_bar' => !empty($request->input('show_progress_bar')),
                    'auto_redirect' => !empty($request->input('auto_redirect')),
                    'redirect_delay_seconds' => (int)$request->input('redirect_delay_seconds', 3),
                    'privacy_policy_url' => $request->input('privacy_policy_url', ''),
                    'terms_url' => $request->input('terms_url', ''),
                ]
            ];

            FunnelService::updateFunnel($tenantId, (int)$id, $data, (int)$user['id']);
            Session::setFlash('success', 'Funnel settings updated successfully.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        return Response::redirect(url("/admin/funnels/{$id}/settings"));
    }

    public function saveFormFields(Request $request, string $id): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();
        $user = current_user();
        $fields = $request->input('fields', []);

        try {
            $saved = FormSchemaService::saveFormFields($tenantId, (int)$id, $fields, (int)$user['id']);
            return Response::success($saved, 'Pre-quiz form fields saved successfully.');
        } catch (\Throwable $e) {
            return Response::error($e->getMessage(), 'SAVE_ERROR', null, 400);
        }
    }

    public function duplicate(Request $request, string $id): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();
        $user = current_user();

        try {
            $copy = FunnelService::duplicateFunnel($tenantId, (int)$id, (int)$user['id']);
            Session::setFlash('success', "Funnel duplicated as '{$copy['name']}'.");
            return Response::redirect(url("/admin/funnels/{$copy['id']}/builder"));
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
            return Response::redirect(url('/admin/funnels'));
        }
    }

    public function publish(Request $request, string $id): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();
        $user = current_user();

        try {
            $res = FunnelPublishService::publish($tenantId, (int)$id, (int)$user['id']);
            if ($request->isAjax()) {
                return Response::success($res, "Funnel published successfully (Version {$res['version_number']}).");
            }
            Session::setFlash('success', "Funnel published successfully as Version {$res['version_number']}.");
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return Response::error($e->getMessage(), 'PUBLISH_ERROR', null, 400);
            }
            Session::setFlash('error', $e->getMessage());
        }

        return Response::redirect(url("/admin/funnels/{$id}/builder"));
    }

    public function unpublish(Request $request, string $id): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();
        $user = current_user();

        try {
            FunnelService::unpublishFunnel($tenantId, (int)$id, (int)$user['id']);
            Session::setFlash('success', 'Funnel unpublished and reverted to draft status.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        return Response::redirect(url("/admin/funnels/{$id}/builder"));
    }

    public function delete(Request $request, string $id): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();
        $user = current_user();

        try {
            FunnelService::deleteFunnel($tenantId, (int)$id, (int)$user['id']);
            Session::setFlash('success', 'Funnel deleted successfully.');
        } catch (\Throwable $e) {
            Session::setFlash('error', $e->getMessage());
        }

        return Response::redirect(url('/admin/funnels'));
    }
}
