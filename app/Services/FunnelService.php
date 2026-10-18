<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use RuntimeException;

class FunnelService
{
    public static function listFunnels(int $tenantId, ?string $search = null, ?string $status = null, ?int $page = null, int $perPage = 10): array
    {
        $sql = "SELECT f.*, 
                       (SELECT COUNT(*) FROM quiz_sessions qs WHERE qs.funnel_id = f.id) as total_starts,
                       (SELECT COUNT(*) FROM quiz_sessions qs WHERE qs.funnel_id = f.id AND qs.status = 'completed') as total_completions,
                       u.name as creator_name
                FROM funnels f
                LEFT JOIN users u ON f.created_by = u.id
                WHERE f.tenant_id = ?";
        $params = [$tenantId];

        if (!empty($search)) {
            $sql .= " AND (f.name LIKE ? OR f.slug LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($status) && in_array($status, ['draft', 'published', 'archived'], true)) {
            $sql .= " AND f.status = ?";
            $params[] = $status;
        }

        if ($page === null) {
            $sql .= " ORDER BY f.updated_at DESC";
            return Database::query($sql, $params);
        }

        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as total_funnels_table";
        $totalRows = (int) Database::fetchColumn($countSql, $params);

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY f.updated_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $items = Database::query($sql, $params);

        return [
            'items' => $items,
            'total' => $totalRows,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($totalRows / $perPage),
        ];
    }

    public static function getFunnel(int $tenantId, int $funnelId): ?array
    {
        return Database::fetchOne(
            "SELECT f.*, u.name as creator_name 
             FROM funnels f 
             LEFT JOIN users u ON f.created_by = u.id 
             WHERE f.tenant_id = ? AND f.id = ?",
            [$tenantId, $funnelId]
        );
    }

    public static function getPublishedFunnelBySlug(string $slug): ?array
    {
        return Database::fetchOne(
            "SELECT f.*, t.name as tenant_name, t.slug as tenant_slug, fv.graph_snapshot_json, fv.form_schema_snapshot_json, fv.settings_snapshot_json, fv.version_number
             FROM funnels f
             JOIN tenants t ON f.tenant_id = t.id
             JOIN funnel_versions fv ON f.current_published_version_id = fv.id
             WHERE f.slug = ? AND f.status = 'published' AND t.status = 'active'",
            [$slug]
        );
    }

    public static function createFunnel(int $tenantId, array $data, int $userId): array
    {
        // Check plan limits
        $limitCheck = UsageLimitService::checkCanCreateFunnel($tenantId);
        if (!$limitCheck['allowed']) {
            throw new RuntimeException($limitCheck['message']);
        }

        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            throw new RuntimeException("Funnel name is required.");
        }

        $baseSlug = !empty($data['slug']) ? trim($data['slug']) : strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $baseSlug = trim($baseSlug, '-');
        if (empty($baseSlug)) {
            $baseSlug = 'quiz-' . bin2hex(random_bytes(3));
        }

        // Ensure unique slug across all funnels for unambiguous public URLs
        $slug = $baseSlug;
        $counter = 1;
        while (Database::fetchOne("SELECT id FROM funnels WHERE slug = ?", [$slug])) {
            $slug = "{$baseSlug}-" . (++$counter);
        }

        $description = $data['description'] ?? null;
        $settings = json_encode([
            'theme' => $data['theme'] ?? 'light',
            'accent_color' => $data['accent_color'] ?? '#5B5BD6',
            'enable_back_button' => true,
            'show_progress_bar' => true,
            'auto_redirect' => false,
            'redirect_delay_seconds' => 3,
        ]);

        return Database::transaction(function () use ($tenantId, $name, $slug, $description, $settings, $userId) {
            Database::execute(
                "INSERT INTO funnels (tenant_id, name, slug, description, status, settings_json, created_by) VALUES (?, ?, ?, ?, 'draft', ?, ?)",
                [$tenantId, $name, $slug, $description, $settings, $userId]
            );
            $funnelId = (int) Database::lastInsertId();

            // Start with a completely blank canvas so the user can drag & drop any nodes they choose
            AuditLogService::log($tenantId, $userId, 'create_funnel', 'funnel', $funnelId, ['name' => $name, 'slug' => $slug]);

            return self::getFunnel($tenantId, $funnelId);
        });
    }

    public static function updateFunnel(int $tenantId, int $funnelId, array $data, int $userId): array
    {
        $funnel = self::getFunnel($tenantId, $funnelId);
        if (!$funnel) {
            throw new RuntimeException("Funnel not found.");
        }

        $name = trim($data['name'] ?? $funnel['name']);
        $description = $data['description'] ?? $funnel['description'];

        $existingSettings = json_decode($funnel['settings_json'] ?? '{}', true) ?: [];
        $newSettings = array_merge($existingSettings, $data['settings'] ?? []);

        Database::execute(
            "UPDATE funnels SET name = ?, description = ?, settings_json = ? WHERE tenant_id = ? AND id = ?",
            [$name, $description, json_encode($newSettings), $tenantId, $funnelId]
        );

        AuditLogService::log($tenantId, $userId, 'update_funnel_settings', 'funnel', $funnelId);

        return self::getFunnel($tenantId, $funnelId);
    }

    public static function duplicateFunnel(int $tenantId, int $funnelId, int $userId): array
    {
        $limitCheck = UsageLimitService::checkCanCreateFunnel($tenantId);
        if (!$limitCheck['allowed']) {
            throw new RuntimeException($limitCheck['message']);
        }

        $original = self::getFunnel($tenantId, $funnelId);
        if (!$original) {
            throw new RuntimeException("Funnel not found.");
        }

        $newName = $original['name'] . ' (Copy)';
        $newSlug = $original['slug'] . '-copy-' . bin2hex(random_bytes(2));

        return Database::transaction(function () use ($tenantId, $funnelId, $newName, $newSlug, $original, $userId) {
            Database::execute(
                "INSERT INTO funnels (tenant_id, name, slug, description, status, settings_json, created_by) VALUES (?, ?, ?, ?, 'draft', ?, ?)",
                [$tenantId, $newName, $newSlug, $original['description'], $original['settings_json'], $userId]
            );
            $newFunnelId = (int) Database::lastInsertId();

            // Duplicate nodes and map old ID to new ID
            $nodes = Database::query("SELECT * FROM funnel_nodes WHERE funnel_id = ?", [$funnelId]);
            $nodeMap = [];
            foreach ($nodes as $node) {
                Database::execute(
                    "INSERT INTO funnel_nodes (tenant_id, funnel_id, node_key, node_type, title, position_x, position_y, config_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$tenantId, $newFunnelId, $node['node_key'], $node['node_type'], $node['title'], $node['position_x'], $node['position_y'], $node['config_json']]
                );
                $newNodeId = (int) Database::lastInsertId();
                $nodeMap[$node['id']] = $newNodeId;
            }

            // Duplicate questions and options
            $questions = Database::query("SELECT * FROM questions WHERE funnel_id = ?", [$funnelId]);
            $optionMap = [];
            foreach ($questions as $q) {
                if (!isset($nodeMap[$q['node_id']])) continue;
                $newNodeId = $nodeMap[$q['node_id']];

                Database::execute(
                    "INSERT INTO questions (tenant_id, funnel_id, node_id, question_text, help_text, question_type, is_required, validation_json, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$tenantId, $newFunnelId, $newNodeId, $q['question_text'], $q['help_text'], $q['question_type'], $q['is_required'], $q['validation_json'], $q['display_order']]
                );
                $newQuestionId = (int) Database::lastInsertId();

                $options = Database::query("SELECT * FROM question_options WHERE question_id = ?", [$q['id']]);
                foreach ($options as $opt) {
                    Database::execute(
                        "INSERT INTO question_options (tenant_id, question_id, option_key, label, value, display_order) VALUES (?, ?, ?, ?, ?, ?)",
                        [$tenantId, $newQuestionId, $opt['option_key'], $opt['label'], $opt['value'], $opt['display_order']]
                    );
                    $newOptionId = (int) Database::lastInsertId();
                    $optionMap[$opt['id']] = $newOptionId;
                }
            }

            // Duplicate destinations
            $destinations = Database::query("SELECT * FROM destinations WHERE funnel_id = ?", [$funnelId]);
            foreach ($destinations as $d) {
                if (!isset($nodeMap[$d['node_id']])) continue;
                $newNodeId = $nodeMap[$d['node_id']];
                Database::execute(
                    "INSERT INTO destinations (tenant_id, funnel_id, node_id, name, url, allowed_host, tracking_config_json) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$tenantId, $newFunnelId, $newNodeId, $d['name'], $d['url'], $d['allowed_host'], $d['tracking_config_json']]
                );
            }

            // Duplicate edges
            $edges = Database::query("SELECT * FROM funnel_edges WHERE funnel_id = ?", [$funnelId]);
            foreach ($edges as $edge) {
                if (!isset($nodeMap[$edge['source_node_id']]) || !isset($nodeMap[$edge['target_node_id']])) continue;
                $newSourceNodeId = $nodeMap[$edge['source_node_id']];
                $newTargetNodeId = $nodeMap[$edge['target_node_id']];
                $newSourceOptionId = $edge['source_option_id'] ? ($optionMap[$edge['source_option_id']] ?? null) : null;

                Database::execute(
                    "INSERT INTO funnel_edges (tenant_id, funnel_id, source_node_id, source_option_id, target_node_id) VALUES (?, ?, ?, ?, ?)",
                    [$tenantId, $newFunnelId, $newSourceNodeId, $newSourceOptionId, $newTargetNodeId]
                );
            }

            // Duplicate form fields
            $formFields = Database::query("SELECT * FROM form_fields WHERE funnel_id = ?", [$funnelId]);
            foreach ($formFields as $ff) {
                Database::execute(
                    "INSERT INTO form_fields (tenant_id, funnel_id, field_key, label, field_type, placeholder, help_text, is_required, validation_json, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$tenantId, $newFunnelId, $ff['field_key'], $ff['label'], $ff['field_type'], $ff['placeholder'], $ff['help_text'], $ff['is_required'], $ff['validation_json'], $ff['display_order'], $ff['is_active']]
                );
                $newFieldId = (int) Database::lastInsertId();

                $ffOptions = Database::query("SELECT * FROM form_field_options WHERE field_id = ?", [$ff['id']]);
                foreach ($ffOptions as $ffo) {
                    Database::execute(
                        "INSERT INTO form_field_options (tenant_id, field_id, label, value, display_order) VALUES (?, ?, ?, ?, ?)",
                        [$tenantId, $newFieldId, $ffo['label'], $ffo['value'], $ffo['display_order']]
                    );
                }
            }

            AuditLogService::log($tenantId, $userId, 'duplicate_funnel', 'funnel', $newFunnelId, ['source_funnel_id' => $funnelId]);

            return self::getFunnel($tenantId, $newFunnelId);
        });
    }

    public static function unpublishFunnel(int $tenantId, int $funnelId, int $userId): array
    {
        $funnel = self::getFunnel($tenantId, $funnelId);
        if (!$funnel) {
            throw new RuntimeException("Funnel not found.");
        }

        Database::execute(
            "UPDATE funnels SET status = 'draft' WHERE tenant_id = ? AND id = ?",
            [$tenantId, $funnelId]
        );

        AuditLogService::log($tenantId, $userId, 'unpublish_funnel', 'funnel', $funnelId);

        return self::getFunnel($tenantId, $funnelId);
    }

    public static function deleteFunnel(int $tenantId, int $funnelId, int $userId): bool
    {
        $funnel = self::getFunnel($tenantId, $funnelId);
        if (!$funnel) {
            throw new RuntimeException("Funnel not found.");
        }

        Database::execute("DELETE FROM funnels WHERE tenant_id = ? AND id = ?", [$tenantId, $funnelId]);
        AuditLogService::log($tenantId, $userId, 'delete_funnel', 'funnel', $funnelId, ['name' => $funnel['name']]);
        return true;
    }
}
