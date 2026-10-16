<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use RuntimeException;

class FunnelGraphService
{
    public static function getGraph(int $tenantId, int $funnelId): array
    {
        $funnel = Database::fetchOne("SELECT * FROM funnels WHERE tenant_id = ? AND id = ?", [$tenantId, $funnelId]);
        if (!$funnel) {
            throw new RuntimeException("Funnel not found.");
        }

        // Fetch nodes
        $nodes = Database::query(
            "SELECT id, node_key, node_type, title, is_start, position_x, position_y, config_json 
             FROM funnel_nodes 
             WHERE tenant_id = ? AND funnel_id = ? 
             ORDER BY id ASC",
            [$tenantId, $funnelId]
        );

        // Fetch questions and their options
        $questions = Database::query(
            "SELECT * FROM questions WHERE tenant_id = ? AND funnel_id = ? ORDER BY display_order ASC",
            [$tenantId, $funnelId]
        );

        $questionsByNode = [];
        foreach ($questions as $q) {
            $options = Database::query(
                "SELECT * FROM question_options WHERE tenant_id = ? AND question_id = ? ORDER BY display_order ASC",
                [$tenantId, $q['id']]
            );
            $q['options'] = $options;
            $questionsByNode[$q['node_id']] = $q;
        }

        // Fetch destinations
        $destinations = Database::query(
            "SELECT * FROM destinations WHERE tenant_id = ? AND funnel_id = ?",
            [$tenantId, $funnelId]
        );
        $destinationsByNode = [];
        foreach ($destinations as $d) {
            $destinationsByNode[$d['node_id']] = $d;
        }

        // Enrich nodes
        $enrichedNodes = [];
        $startNodeId = null;
        foreach ($nodes as $node) {
            $node['id'] = (int)$node['id'];
            $node['position_x'] = (float)$node['position_x'];
            $node['position_y'] = (float)$node['position_y'];
            $node['is_start'] = !empty($node['is_start']);
            if ($node['is_start'] && !$startNodeId) {
                $startNodeId = (int)$node['id'];
            }
            if ($node['node_type'] === 'question') {
                $node['question'] = $questionsByNode[$node['id']] ?? null;
            } elseif ($node['node_type'] === 'destination') {
                $node['destination'] = $destinationsByNode[$node['id']] ?? null;
            } elseif ($node['node_type'] === 'form') {
                $formData = !empty($node['config_json']) ? (json_decode($node['config_json'], true) ?: []) : [];
                if (!empty($formData['fields']) && is_array($formData['fields'])) {
                    foreach ($formData['fields'] as $fIdx => &$fld) {
                        if (empty($fld['id'])) {
                            $fld['id'] = (int)($node['id'] * 1000 + $fIdx + 1);
                        }
                    }
                    unset($fld);
                }
                $node['form'] = $formData;
            }
            $enrichedNodes[] = $node;
        }

        // If no start node explicitly set, fallback to first non-destination node
        if (!$startNodeId && !empty($enrichedNodes)) {
            foreach ($enrichedNodes as $en) {
                if ($en['node_type'] !== 'destination') {
                    $startNodeId = (int)$en['id'];
                    break;
                }
            }
        }

        // Fetch edges
        $edges = Database::query(
            "SELECT fe.*, qo.option_key as option_key, qo.label as option_label 
             FROM funnel_edges fe 
             LEFT JOIN question_options qo ON fe.source_option_id = qo.id 
             WHERE fe.tenant_id = ? AND fe.funnel_id = ?",
            [$tenantId, $funnelId]
        );

        return [
            'funnel' => $funnel,
            'nodes' => $enrichedNodes,
            'edges' => $edges,
            'start_node_id' => $startNodeId,
        ];
    }

    public static function saveGraph(int $tenantId, int $funnelId, array $data, int $userId): array
    {
        $funnel = Database::fetchOne("SELECT * FROM funnels WHERE tenant_id = ? AND id = ?", [$tenantId, $funnelId]);
        if (!$funnel) {
            throw new RuntimeException("Funnel not found.");
        }

        $nodes = $data['nodes'] ?? [];
        $edges = $data['edges'] ?? [];

        return Database::transaction(function () use ($tenantId, $funnelId, $nodes, $edges, $userId) {
            // Map client keys / old IDs to newly created or updated database IDs
            $nodeIdMap = [];
            $existingNodes = Database::query("SELECT id, node_key FROM funnel_nodes WHERE funnel_id = ?", [$funnelId]);
            $existingNodeMap = [];
            foreach ($existingNodes as $en) {
                $existingNodeMap[$en['node_key']] = (int)$en['id'];
            }

            $touchedNodeIds = [];

            // 1. Process Nodes
            foreach ($nodes as $node) {
                $nodeKey = $node['node_key'] ?? ('node_' . bin2hex(random_bytes(4)));
                $nodeType = $node['node_type'] ?? 'question';
                $title = $node['title'] ?? ($nodeType === 'start' ? 'Start' : 'Node');
                $posX = (float)($node['position_x'] ?? 100);
                $posY = (float)($node['position_y'] ?? 100);

                $isStart = (!empty($node['is_start']) && $nodeType !== 'destination') ? 1 : 0;

                $configJson = null;
                if ($nodeType === 'form') {
                    $formPayload = $node['form'] ?? $node['config_json'] ?? [];
                    if (is_string($formPayload)) {
                        $formPayload = json_decode($formPayload, true) ?: [];
                    }
                    $configJson = json_encode($formPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }

                if (isset($existingNodeMap[$nodeKey])) {
                    $dbNodeId = $existingNodeMap[$nodeKey];
                    Database::execute(
                        "UPDATE funnel_nodes SET title = ?, is_start = ?, position_x = ?, position_y = ?, node_type = ?, config_json = ? WHERE id = ? AND tenant_id = ?",
                        [$title, $isStart, $posX, $posY, $nodeType, $configJson, $dbNodeId, $tenantId]
                    );
                } else {
                    Database::execute(
                        "INSERT INTO funnel_nodes (tenant_id, funnel_id, node_key, node_type, title, is_start, position_x, position_y, config_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [$tenantId, $funnelId, $nodeKey, $nodeType, $title, $isStart, $posX, $posY, $configJson]
                    );
                    $dbNodeId = (int) Database::lastInsertId();
                }

                $nodeIdMap[$nodeKey] = $dbNodeId;
                if (!empty($node['id'])) {
                    $nodeIdMap[$node['id']] = $dbNodeId;
                }
                $touchedNodeIds[] = $dbNodeId;

                // Handle Question Node configuration
                if ($nodeType === 'question' && !empty($node['question'])) {
                    $qData = $node['question'];
                    $qText = trim($qData['question_text'] ?? 'Untitled Question');
                    $helpText = $qData['help_text'] ?? null;
                    $qType = $qData['question_type'] ?? 'single_choice';
                    $isRequired = !empty($qData['is_required']) ? 1 : 0;

                    $existingQ = Database::fetchOne("SELECT id FROM questions WHERE node_id = ?", [$dbNodeId]);
                    if ($existingQ) {
                        $qId = (int)$existingQ['id'];
                        Database::execute(
                            "UPDATE questions SET question_text = ?, help_text = ?, question_type = ?, is_required = ? WHERE id = ?",
                            [$qText, $helpText, $qType, $isRequired, $qId]
                        );
                    } else {
                        Database::execute(
                            "INSERT INTO questions (tenant_id, funnel_id, node_id, question_text, help_text, question_type, is_required) VALUES (?, ?, ?, ?, ?, ?, ?)",
                            [$tenantId, $funnelId, $dbNodeId, $qText, $helpText, $qType, $isRequired]
                        );
                        $qId = (int) Database::lastInsertId();
                    }

                    // Handle Options
                    $options = $qData['options'] ?? [];
                    $touchedOptionIds = [];
                    $optOrder = 1;

                    foreach ($options as $opt) {
                        $optKey = $opt['option_key'] ?? ('opt_' . bin2hex(random_bytes(4)));
                        $label = trim($opt['label'] ?? 'Option');
                        $value = trim($opt['value'] ?? $label);

                        $existingOpt = Database::fetchOne("SELECT id FROM question_options WHERE question_id = ? AND option_key = ?", [$qId, $optKey]);
                        if ($existingOpt) {
                            $optId = (int)$existingOpt['id'];
                            Database::execute(
                                "UPDATE question_options SET label = ?, value = ?, display_order = ? WHERE id = ?",
                                [$label, $value, $optOrder++, $optId]
                            );
                        } else {
                            Database::execute(
                                "INSERT INTO question_options (tenant_id, question_id, option_key, label, value, display_order) VALUES (?, ?, ?, ?, ?, ?)",
                                [$tenantId, $qId, $optKey, $label, $value, $optOrder++]
                            );
                            $optId = (int) Database::lastInsertId();
                        }

                        $touchedOptionIds[] = $optId;
                        $nodeIdMap["opt_{$optKey}"] = $optId;
                        if (!empty($opt['id'])) {
                            $nodeIdMap["opt_{$opt['id']}"] = $optId;
                        }
                    }

                    // Delete removed options
                    if (!empty($touchedOptionIds)) {
                        $placeholders = implode(',', array_fill(0, count($touchedOptionIds), '?'));
                        Database::execute("DELETE FROM question_options WHERE question_id = ? AND id NOT IN ({$placeholders})", array_merge([$qId], $touchedOptionIds));
                    }
                }

                // Handle Destination Node configuration
                if ($nodeType === 'destination' && !empty($node['destination'])) {
                    $dData = $node['destination'];
                    $name = trim($dData['name'] ?? $title);
                    $url = trim($dData['url'] ?? '');

                    $existingDest = Database::fetchOne("SELECT id FROM destinations WHERE node_id = ?", [$dbNodeId]);
                    if ($existingDest) {
                        Database::execute(
                            "UPDATE destinations SET name = ?, url = ? WHERE id = ?",
                            [$name, $url, $existingDest['id']]
                        );
                    } else {
                        Database::execute(
                            "INSERT INTO destinations (tenant_id, funnel_id, node_id, name, url) VALUES (?, ?, ?, ?, ?)",
                            [$tenantId, $funnelId, $dbNodeId, $name, $url]
                        );
                    }
                }
            }

            // Delete nodes not in payload
            if (!empty($touchedNodeIds)) {
                $placeholders = implode(',', array_fill(0, count($touchedNodeIds), '?'));
                Database::execute("DELETE FROM funnel_nodes WHERE funnel_id = ? AND id NOT IN ({$placeholders})", array_merge([$funnelId], $touchedNodeIds));
            }

            // Ensure exactly one node is designated as start if any valid nodes exist
            $startCount = (int) Database::fetchColumn("SELECT COUNT(*) FROM funnel_nodes WHERE funnel_id = ? AND is_start = 1", [$funnelId]);
            if ($startCount === 0) {
                $hasLegacyStart = Database::fetchOne("SELECT id FROM funnel_nodes WHERE funnel_id = ? AND node_type = 'start' ORDER BY id ASC LIMIT 1", [$funnelId]);
                if ($hasLegacyStart) {
                    Database::execute("UPDATE funnel_nodes SET is_start = 1 WHERE id = ?", [$hasLegacyStart['id']]);
                } else {
                    Database::execute("UPDATE funnel_nodes SET is_start = 1 WHERE funnel_id = ? AND node_type IN ('question', 'form') ORDER BY id ASC LIMIT 1", [$funnelId]);
                }
            }

            // 2. Process Edges
            Database::execute("DELETE FROM funnel_edges WHERE funnel_id = ?", [$funnelId]);

            foreach ($edges as $edge) {
                $srcKey = $edge['source_node_key'] ?? $edge['source_node_id'] ?? null;
                $tgtKey = $edge['target_node_key'] ?? $edge['target_node_id'] ?? null;
                $optKey = $edge['source_option_key'] ?? $edge['source_option_id'] ?? null;

                $srcNodeId = $nodeIdMap[$srcKey] ?? (is_numeric($srcKey) ? (int)$srcKey : null);
                $tgtNodeId = $nodeIdMap[$tgtKey] ?? (is_numeric($tgtKey) ? (int)$tgtKey : null);
                $optId = null;
                if (!empty($optKey)) {
                    $optId = $nodeIdMap["opt_{$optKey}"] ?? (is_numeric($optKey) ? (int)$optKey : null);
                }

                if ($srcNodeId && $tgtNodeId) {
                    Database::execute(
                        "INSERT INTO funnel_edges (tenant_id, funnel_id, source_node_id, source_option_id, target_node_id) VALUES (?, ?, ?, ?, ?)",
                        [$tenantId, $funnelId, $srcNodeId, $optId, $tgtNodeId]
                    );
                }
            }

            // Update funnel timestamp
            Database::execute("UPDATE funnels SET updated_at = NOW() WHERE id = ?", [$funnelId]);

            AuditLogService::log($tenantId, $userId, 'save_funnel_graph', 'funnel', $funnelId);

            return self::getGraph($tenantId, $funnelId);
        });
    }

    public static function validateGraph(int $tenantId, int $funnelId): array
    {
        $graph = self::getGraph($tenantId, $funnelId);
        $nodes = $graph['nodes'];
        $edges = $graph['edges'];

        $issues = [];

        // 1. Check Starting Step
        $startNodes = array_filter($nodes, fn($n) => !empty($n['is_start']));
        if (count($startNodes) === 0) {
            $issues[] = [
                'type' => 'missing_start_node',
                'severity' => 'error',
                'message' => 'Please designate a starting step for your funnel (select any Question or Form node and set it as Starting Step).',
                'node_id' => null,
            ];
        } elseif (count($startNodes) > 1) {
            $issues[] = [
                'type' => 'multiple_start_nodes',
                'severity' => 'error',
                'message' => 'Multiple starting steps detected. Only one node can be designated as the starting step.',
                'node_id' => null,
            ];
        } else {
            $startNode = reset($startNodes);
            if ($startNode['node_type'] === 'destination') {
                $issues[] = [
                    'type' => 'invalid_start_node',
                    'severity' => 'error',
                    'message' => 'A destination page cannot be designated as the starting step.',
                    'node_id' => $startNode['id'],
                ];
            }
        }

        // 2. Check Question Nodes and Option Routes
        $nodeMap = [];
        foreach ($nodes as $n) {
            $nodeMap[$n['id']] = $n;
        }

        $edgesBySourceOption = [];
        $edgesBySourceNode = [];
        foreach ($edges as $e) {
            $edgesBySourceNode[$e['source_node_id']][] = $e;
            if (!empty($e['source_option_id'])) {
                $edgesBySourceOption[$e['source_option_id']] = $e;
            }
        }

        foreach ($nodes as $node) {
            if ($node['node_type'] === 'question') {
                $q = $node['question'];
                if (!$q || empty($q['options'])) {
                    $issues[] = [
                        'type' => 'question_no_options',
                        'severity' => 'error',
                        'message' => "Question '{$node['title']}' has no answer options configured.",
                        'node_id' => $node['id'],
                    ];
                } else {
                    foreach ($q['options'] as $opt) {
                        if (!isset($edgesBySourceOption[$opt['id']])) {
                            $issues[] = [
                                'type' => 'option_missing_route',
                                'severity' => 'error',
                                'message' => "Option '{$opt['label']}' in '{$node['title']}' has no next step connected.",
                                'node_id' => $node['id'],
                                'option_id' => $opt['id'],
                            ];
                        }
                    }
                }
            } elseif ($node['node_type'] === 'destination') {
                $d = $node['destination'];
                if (!$d || empty($d['url']) || !filter_var($d['url'], FILTER_VALIDATE_URL)) {
                    $issues[] = [
                        'type' => 'invalid_destination_url',
                        'severity' => 'error',
                        'message' => "Destination '{$node['title']}' has an invalid or missing landing page URL.",
                        'node_id' => $node['id'],
                    ];
                }
            } elseif ($node['node_type'] === 'form') {
                $form = $node['form'] ?? [];
                $fields = $form['fields'] ?? [];
                if (empty($fields)) {
                    $issues[] = [
                        'type' => 'form_no_fields',
                        'severity' => 'warning',
                        'message' => "Form '{$node['title']}' has no fields configured.",
                        'node_id' => $node['id'],
                    ];
                }
                $submitAction = $form['submit_action'] ?? 'redirect';
                $formEdges = $edgesBySourceNode[$node['id']] ?? [];
                if ($submitAction === 'redirect' && empty($formEdges)) {
                    $issues[] = [
                        'type' => 'form_missing_route',
                        'severity' => 'error',
                        'message' => "Form '{$node['title']}' is set to route to a next step, but no next node is connected.",
                        'node_id' => $node['id'],
                    ];
                }
            }
        }

        // 3. Unreachable Node Detection (BFS from Start)
        if (!empty($startNodes)) {
            $startNode = reset($startNodes);
            $visited = [(int)$startNode['id'] => true];
            $queue = [(int)$startNode['id']];

            while (!empty($queue)) {
                $currId = array_shift($queue);
                $outgoing = $edgesBySourceNode[$currId] ?? [];
                foreach ($outgoing as $e) {
                    $tgtId = (int)$e['target_node_id'];
                    if (!isset($visited[$tgtId])) {
                        $visited[$tgtId] = true;
                        $queue[] = $tgtId;
                    }
                }
            }

            foreach ($nodes as $node) {
                if (!isset($visited[(int)$node['id']])) {
                    $issues[] = [
                        'type' => 'unreachable_node',
                        'severity' => 'warning',
                        'message' => "Node '{$node['title']}' is not reachable from the Start node.",
                        'node_id' => $node['id'],
                    ];
                }
            }
        }

        $isValid = count(array_filter($issues, fn($i) => $i['severity'] === 'error')) === 0;

        return [
            'is_valid' => $isValid,
            'errors_count' => count(array_filter($issues, fn($i) => $i['severity'] === 'error')),
            'warnings_count' => count(array_filter($issues, fn($i) => $i['severity'] === 'warning')),
            'issues' => $issues,
        ];
    }
}
