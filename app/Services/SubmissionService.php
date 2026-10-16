<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use RuntimeException;

class SubmissionService
{
    public static function listSubmissions(int $tenantId, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT qs.id, qs.status, qs.started_at, qs.completed_at, qs.created_at,
                       f.name as funnel_name, f.slug as funnel_slug,
                       d.name as destination_name,
                       (SELECT fsv.value_text FROM form_submissions fs JOIN form_submission_values fsv ON fsv.form_submission_id = fs.id WHERE fs.session_id = qs.id AND fsv.field_key_snapshot = 'full_name' LIMIT 1) as lead_name,
                       (SELECT fsv.value_text FROM form_submissions fs JOIN form_submission_values fsv ON fsv.form_submission_id = fs.id WHERE fs.session_id = qs.id AND fsv.field_key_snapshot = 'email' LIMIT 1) as lead_email
                FROM quiz_sessions qs
                JOIN funnels f ON qs.funnel_id = f.id
                LEFT JOIN destinations d ON qs.final_destination_id = d.id
                WHERE qs.tenant_id = ?";
        $params = [$tenantId];

        if (!empty($filters['funnel_id'])) {
            $sql .= " AND qs.funnel_id = ?";
            $params[] = (int)$filters['funnel_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND qs.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM form_submissions fs 
                JOIN form_submission_values fsv ON fsv.form_submission_id = fs.id 
                WHERE fs.session_id = qs.id AND fsv.value_text LIKE ?
            )";
            $params[] = "%{$filters['search']}%";
        }

        // Count total
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as total_table";
        $totalRows = (int) Database::fetchColumn($countSql, $params);

        $sql .= " ORDER BY qs.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $items = Database::query($sql, $params);

        return [
            'items' => $items,
            'total' => $totalRows,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($totalRows / $perPage),
        ];
    }

    public static function getSubmissionDetail(int $tenantId, int $sessionId): ?array
    {
        $session = Database::fetchOne(
            "SELECT qs.*, f.name as funnel_name, f.slug as funnel_slug, fv.version_number, d.name as destination_name, d.url as destination_url
             FROM quiz_sessions qs
             JOIN funnels f ON qs.funnel_id = f.id
             JOIN funnel_versions fv ON qs.funnel_version_id = fv.id
             LEFT JOIN destinations d ON qs.final_destination_id = d.id
             WHERE qs.tenant_id = ? AND qs.id = ?",
            [$tenantId, $sessionId]
        );

        if (!$session) {
            return null;
        }

        // Pre-Quiz Form Values
        $formSubmission = Database::fetchOne(
            "SELECT * FROM form_submissions WHERE session_id = ?",
            [$sessionId]
        );

        $formValues = [];
        if ($formSubmission) {
            $formValues = Database::query(
                "SELECT * FROM form_submission_values WHERE form_submission_id = ? ORDER BY id ASC",
                [$formSubmission['id']]
            );
        }

        // Answer History (only active, non-superseded answers)
        $answers = Database::query(
            "SELECT qa.*, q.question_type 
             FROM quiz_answers qa
             LEFT JOIN questions q ON qa.question_id = q.id
             WHERE qa.session_id = ? AND qa.superseded_at IS NULL
             ORDER BY qa.id ASC",
            [$sessionId]
        );

        // Complete Journey Step Events Timeline
        $events = Database::query(
            "SELECT qse.*, fn.title as node_title, fn.node_type, qo.label as option_label
             FROM quiz_step_events qse
             LEFT JOIN funnel_nodes fn ON qse.node_id = fn.id
             LEFT JOIN question_options qo ON qse.option_id = qo.id
             WHERE qse.session_id = ?
             ORDER BY qse.occurred_at ASC, qse.id ASC",
            [$sessionId]
        );

        // Result record
        $result = Database::fetchOne(
            "SELECT * FROM quiz_results WHERE session_id = ?",
            [$sessionId]
        );

        return [
            'session' => $session,
            'form_submission' => $formSubmission,
            'form_values' => $formValues,
            'answers' => $answers,
            'events' => $events,
            'result' => $result ? json_decode($result['summary_json'], true) : null,
        ];
    }

    public static function exportCsv(int $tenantId, ?int $funnelId = null): string
    {
        $sql = "SELECT qs.id as session_id, f.name as funnel_name, qs.status, qs.started_at, qs.completed_at, d.name as destination_name,
                       (SELECT fsv.value_text FROM form_submissions fs JOIN form_submission_values fsv ON fsv.form_submission_id = fs.id WHERE fs.session_id = qs.id AND fsv.field_key_snapshot = 'full_name' LIMIT 1) as full_name,
                       (SELECT fsv.value_text FROM form_submissions fs JOIN form_submission_values fsv ON fsv.form_submission_id = fs.id WHERE fs.session_id = qs.id AND fsv.field_key_snapshot = 'email' LIMIT 1) as email,
                       (SELECT fsv.value_text FROM form_submissions fs JOIN form_submission_values fsv ON fsv.form_submission_id = fs.id WHERE fs.session_id = qs.id AND fsv.field_key_snapshot = 'phone' LIMIT 1) as phone
                FROM quiz_sessions qs
                JOIN funnels f ON qs.funnel_id = f.id
                LEFT JOIN destinations d ON qs.final_destination_id = d.id
                WHERE qs.tenant_id = ?";
        $params = [$tenantId];

        if ($funnelId) {
            $sql .= " AND qs.funnel_id = ?";
            $params[] = $funnelId;
        }

        $sql .= " ORDER BY qs.created_at DESC";

        $rows = Database::query($sql, $params);

        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, ['Session ID', 'Funnel', 'Lead Name', 'Email', 'Phone', 'Status', 'Started At', 'Completed At', 'Destination']);

        foreach ($rows as $r) {
            fputcsv($fp, [
                $r['session_id'],
                $r['funnel_name'],
                $r['full_name'] ?? 'N/A',
                $r['email'] ?? 'N/A',
                $r['phone'] ?? 'N/A',
                ucfirst(str_replace('_', ' ', $r['status'])),
                $r['started_at'] ?? '',
                $r['completed_at'] ?? '',
                $r['destination_name'] ?? 'N/A',
            ]);
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return $csv;
    }
}
