<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\SubmissionService;
use App\Services\FunnelService;
use App\Services\TenantContextService;
use App\Services\AuthorizationService;

class SubmissionController
{
    public function index(Request $request): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();

        $filters = [
            'funnel_id' => $request->query('funnel_id'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
        ];
        $page = (int)$request->query('page', 1);

        $submissions = SubmissionService::listSubmissions($tenantId, $filters, $page, 15);
        $funnels = FunnelService::listFunnels($tenantId);

        return view('submissions.index', [
            'title' => 'Submissions & Leads — Quiz Funnel SaaS',
            'submissions' => $submissions,
            'funnels' => $funnels,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $tenantId = TenantContextService::getActiveTenantId();
        $detail = SubmissionService::getSubmissionDetail($tenantId, (int)$id);

        if (!$detail) {
            Session::setFlash('error', 'Submission record not found.');
            return Response::redirect(url('/admin/submissions'));
        }

        return view('submissions.show', [
            'title' => "Journey Detail: Submission #{$id} — Quiz Funnel SaaS",
            'detail' => $detail,
        ]);
    }

    public function export(Request $request): Response
    {
        if (!AuthorizationService::canExportSubmissions()) {
            Session::setFlash('error', 'Permission denied to export submissions.');
            return Response::redirect(url('/admin/submissions'));
        }

        $tenantId = TenantContextService::getActiveTenantId();
        $funnelId = $request->query('funnel_id') ? (int)$request->query('funnel_id') : null;

        $csv = SubmissionService::exportCsv($tenantId, $funnelId);
        $filename = 'submissions-export-' . date('Y-m-d-His') . '.csv';

        $response = new Response($csv, 200);
        $response->header('Content-Type', 'text/csv; charset=utf-8');
        $response->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        return $response;
    }
}
