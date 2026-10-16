<?php

namespace App\Services;

use App\Core\Database;

class AnalyticsService
{
    public static function getDashboardMetrics(int $tenantId): array
    {
        $totalFunnels = (int) Database::fetchColumn("SELECT COUNT(*) FROM funnels WHERE tenant_id = ?", [$tenantId]);
        $publishedFunnels = (int) Database::fetchColumn("SELECT COUNT(*) FROM funnels WHERE tenant_id = ? AND status = 'published'", [$tenantId]);
        $draftFunnels = (int) Database::fetchColumn("SELECT COUNT(*) FROM funnels WHERE tenant_id = ? AND status = 'draft'", [$tenantId]);

        $totalStarts = (int) Database::fetchColumn("SELECT COUNT(*) FROM quiz_sessions WHERE tenant_id = ? AND started_at IS NOT NULL", [$tenantId]);
        $totalCompletions = (int) Database::fetchColumn("SELECT COUNT(*) FROM quiz_sessions WHERE tenant_id = ? AND status = 'completed'", [$tenantId]);
        $totalLeads = (int) Database::fetchColumn("SELECT COUNT(*) FROM form_submissions WHERE tenant_id = ?", [$tenantId]);

        $completionRate = $totalStarts > 0 ? round(($totalCompletions / $totalStarts) * 100, 1) : 0.0;

        // Recent Submissions
        $recentSubmissions = Database::query(
            "SELECT qs.id, qs.status, qs.created_at, f.name as funnel_name,
                    (SELECT fsv.value_text FROM form_submissions fs JOIN form_submission_values fsv ON fsv.form_submission_id = fs.id WHERE fs.session_id = qs.id AND fsv.field_key_snapshot = 'full_name' LIMIT 1) as lead_name,
                    (SELECT fsv.value_text FROM form_submissions fs JOIN form_submission_values fsv ON fsv.form_submission_id = fs.id WHERE fs.session_id = qs.id AND fsv.field_key_snapshot = 'email' LIMIT 1) as lead_email
             FROM quiz_sessions qs
             JOIN funnels f ON qs.funnel_id = f.id
             WHERE qs.tenant_id = ?
             ORDER BY qs.created_at DESC LIMIT 5",
            [$tenantId]
        );

        // Recent Funnels
        $recentFunnels = Database::query(
            "SELECT f.*, 
                    (SELECT COUNT(*) FROM quiz_sessions qs WHERE qs.funnel_id = f.id) as starts_count,
                    (SELECT COUNT(*) FROM quiz_sessions qs WHERE qs.funnel_id = f.id AND qs.status = 'completed') as completions_count
             FROM funnels f 
             WHERE f.tenant_id = ? 
             ORDER BY f.updated_at DESC LIMIT 5",
            [$tenantId]
        );

        return [
            'total_funnels' => $totalFunnels,
            'published_funnels' => $publishedFunnels,
            'draft_funnels' => $draftFunnels,
            'total_starts' => $totalStarts,
            'total_completions' => $totalCompletions,
            'total_leads' => $totalLeads,
            'completion_rate' => $completionRate,
            'recent_submissions' => $recentSubmissions,
            'recent_funnels' => $recentFunnels,
        ];
    }

    public static function getFunnelMetrics(int $tenantId, int $funnelId): array
    {
        $funnel = Database::fetchOne("SELECT * FROM funnels WHERE tenant_id = ? AND id = ?", [$tenantId, $funnelId]);
        if (!$funnel) {
            return [];
        }

        $views = (int) Database::fetchColumn("SELECT COUNT(*) FROM quiz_step_events WHERE tenant_id = ? AND funnel_id = ? AND event_type = 'quiz_viewed'", [$tenantId, $funnelId]);
        $formStarts = (int) Database::fetchColumn("SELECT COUNT(*) FROM quiz_step_events WHERE tenant_id = ? AND funnel_id = ? AND event_type = 'pre_form_submitted'", [$tenantId, $funnelId]);
        $quizStarts = (int) Database::fetchColumn("SELECT COUNT(*) FROM quiz_sessions WHERE tenant_id = ? AND funnel_id = ? AND started_at IS NOT NULL", [$tenantId, $funnelId]);
        $completions = (int) Database::fetchColumn("SELECT COUNT(*) FROM quiz_sessions WHERE tenant_id = ? AND funnel_id = ? AND status = 'completed'", [$tenantId, $funnelId]);

        $completionRate = $quizStarts > 0 ? round(($completions / $quizStarts) * 100, 1) : 0.0;
        $leadCaptureRate = $views > 0 ? round(($formStarts / $views) * 100, 1) : 0.0;

        // Question Progress & Drop-Off
        $questions = Database::query(
            "SELECT q.id, q.node_id, q.question_text, fn.title as node_title,
                    (SELECT COUNT(*) FROM quiz_answers qa WHERE qa.question_id = q.id AND qa.superseded_at IS NULL) as answers_count
             FROM questions q
             JOIN funnel_nodes fn ON q.node_id = fn.id
             WHERE q.tenant_id = ? AND q.funnel_id = ?
             ORDER BY q.display_order ASC",
            [$tenantId, $funnelId]
        );

        // Option Distribution
        $optionStats = Database::query(
            "SELECT qo.id, qo.label, qo.question_id, q.question_text,
                    (SELECT COUNT(*) FROM quiz_answers qa WHERE qa.question_id = qo.question_id AND JSON_CONTAINS(qa.selected_option_ids_json, CAST(qo.id AS CHAR), '$') AND qa.superseded_at IS NULL) as select_count
             FROM question_options qo
             JOIN questions q ON qo.question_id = q.id
             WHERE qo.tenant_id = ? AND q.funnel_id = ?
             ORDER BY select_count DESC",
            [$tenantId, $funnelId]
        );

        // Destination Distribution
        $destinations = Database::query(
            "SELECT d.id, d.name, d.url,
                    (SELECT COUNT(*) FROM quiz_sessions qs WHERE qs.final_destination_id = d.id AND qs.status = 'completed') as reach_count
             FROM destinations d
             WHERE d.tenant_id = ? AND d.funnel_id = ?
             ORDER BY reach_count DESC",
            [$tenantId, $funnelId]
        );

        return [
            'funnel' => $funnel,
            'views' => $views,
            'form_starts' => $formStarts,
            'quiz_starts' => $quizStarts,
            'completions' => $completions,
            'completion_rate' => $completionRate,
            'lead_capture_rate' => $leadCaptureRate,
            'questions' => $questions,
            'options' => $optionStats,
            'destinations' => $destinations,
        ];
    }
}
