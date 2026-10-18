<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use RuntimeException;

class QuizEngineService
{
    public static function initializeSession(string $funnelSlug): array
    {
        $funnel = FunnelService::getPublishedFunnelBySlug($funnelSlug);
        if (!$funnel) {
            throw new RuntimeException("This quiz is not currently available or published.");
        }

        $tenantId = (int)$funnel['tenant_id'];
        $funnelId = (int)$funnel['id'];
        $versionId = (int)$funnel['current_published_version_id'];

        $graphSnapshot = json_decode($funnel['graph_snapshot_json'], true) ?: [];
        $formSnapshot = json_decode($funnel['form_schema_snapshot_json'], true) ?: [];
        $settingsSnapshot = json_decode($funnel['settings_snapshot_json'], true) ?: [];

        $startNodeId = $graphSnapshot['start_node_id'] ?? null;
        if (!$startNodeId) {
            foreach ($graphSnapshot['nodes'] ?? [] as $n) {
                if (!empty($n['is_start'])) {
                    $startNodeId = (int)$n['id'];
                    break;
                }
            }
        }
        if (!$startNodeId) {
            foreach ($graphSnapshot['nodes'] ?? [] as $n) {
                if ($n['node_type'] === 'start') {
                    $startNodeId = (int)$n['id'];
                    break;
                }
            }
        }
        if (!$startNodeId) {
            foreach ($graphSnapshot['nodes'] ?? [] as $n) {
                if ($n['node_type'] !== 'destination') {
                    $startNodeId = (int)$n['id'];
                    break;
                }
            }
        }

        if (!$startNodeId) {
            throw new RuntimeException("Published funnel does not have an entry point.");
        }

        // Generate cryptographic token
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);

        // Resolve start node object from graph snapshot
        $startNodeObj = null;
        foreach ($graphSnapshot['nodes'] ?? [] as $n) {
            if ((int)$n['id'] === $startNodeId) {
                $startNodeObj = $n;
                break;
            }
        }

        // Only legacy funnels with an explicit 'start' node type use pre-quiz forms
        $isLegacyStartNode = ($startNodeObj && $startNodeObj['node_type'] === 'start');
        $hasForm = $isLegacyStartNode && !empty($formSnapshot['fields']);
        $initialStatus = $hasForm ? 'form_pending' : 'in_progress';
        $initialNodeId = $startNodeId;

        // Backward compatibility: If the start node is a legacy 'start' node with no form, follow the wire to target
        if ($isLegacyStartNode && !$hasForm) {
            $edges = $graphSnapshot['edges'] ?? [];
            foreach ($edges as $e) {
                if ((int)$e['source_node_id'] === (int)$startNodeId) {
                    $initialNodeId = (int)$e['target_node_id'];
                    break;
                }
            }
        }

        $sessionId = (int) Database::transaction(function () use ($tenantId, $funnelId, $versionId, $tokenHash, $initialStatus, $initialNodeId, $hasForm) {
            Database::execute(
                "INSERT INTO quiz_sessions (tenant_id, funnel_id, funnel_version_id, public_token_hash, status, current_node_id, started_at, last_activity_at, expires_at) 
                 VALUES (?, ?, ?, ?, ?, ?, " . ($hasForm ? "NULL" : "NOW()") . ", NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY))",
                [$tenantId, $funnelId, $versionId, $tokenHash, $initialStatus, $initialNodeId]
            );
            $sId = (int) Database::lastInsertId();

            Database::execute(
                "INSERT INTO quiz_step_events (tenant_id, funnel_id, funnel_version_id, session_id, event_type, occurred_at) 
                 VALUES (?, ?, ?, ?, 'quiz_viewed', NOW())",
                [$tenantId, $funnelId, $versionId, $sId]
            );

            if (!$hasForm) {
                Database::execute(
                    "INSERT INTO quiz_step_events (tenant_id, funnel_id, funnel_version_id, session_id, event_type, occurred_at) 
                     VALUES (?, ?, ?, ?, 'quiz_started', NOW())",
                    [$tenantId, $funnelId, $versionId, $sId]
                );
            }

            return $sId;
        });

        return [
            'session_token' => $rawToken,
            'status' => $initialStatus,
            'funnel' => [
                'name' => $funnel['name'],
                'description' => $funnel['description'],
                'settings' => $settingsSnapshot,
            ],
            'has_form' => $hasForm,
            'form_fields' => $hasForm ? $formSnapshot['fields'] : [],
        ];
    }

    public static function resolveSession(string $rawToken): ?array
    {
        $hash = hash('sha256', $rawToken);
        return Database::fetchOne(
            "SELECT qs.*, f.name as funnel_name, f.slug as funnel_slug, fv.graph_snapshot_json, fv.form_schema_snapshot_json, fv.settings_snapshot_json
             FROM quiz_sessions qs
             JOIN funnels f ON qs.funnel_id = f.id
             JOIN funnel_versions fv ON qs.funnel_version_id = fv.id
             WHERE qs.public_token_hash = ?",
            [$hash]
        );
    }

    public static function submitForm(string $rawToken, array $formData): array
    {
        $session = self::resolveSession($rawToken);
        if (!$session) {
            throw new RuntimeException("Invalid or expired quiz session.");
        }

        if ($session['status'] !== 'form_pending') {
            $graphSnapshot = json_decode($session['graph_snapshot_json'], true) ?: [];
            $currentNodeId = (int)$session['current_node_id'];
            $node = null;
            foreach ($graphSnapshot['nodes'] ?? [] as $n) {
                if ((int)$n['id'] === $currentNodeId) {
                    $node = $n;
                    break;
                }
            }
            if ($node && $node['node_type'] === 'form') {
                return self::submitAnswer($rawToken, $currentNodeId, ['form_data' => $formData]);
            }
            // Already submitted form, proceed to current step
            return self::getCurrentStep($rawToken);
        }

        $formSnapshot = json_decode($session['form_schema_snapshot_json'], true) ?: [];
        $fields = $formSnapshot['fields'] ?? [];

        // Validate form submission
        $validation = FormSchemaService::validateSubmission($fields, $formData);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error_code' => 'FORM_VALIDATION_FAILED',
                'errors' => $validation['errors'],
            ];
        }

        $cleanData = $validation['clean_data'];
        $graphSnapshot = json_decode($session['graph_snapshot_json'], true) ?: [];
        $startNodeId = (int)$session['current_node_id'];

        // Determine next node from Start node edge
        $edges = $graphSnapshot['edges'] ?? [];
        $startEdge = null;
        foreach ($edges as $e) {
            if ((int)$e['source_node_id'] === $startNodeId) {
                $startEdge = $e;
                break;
            }
        }

        if (!$startEdge) {
            throw new RuntimeException("Funnel configuration error: Start node is not connected to a next step.");
        }

        $firstQuestionNodeId = (int)$startEdge['target_node_id'];

        Database::transaction(function () use ($session, $fields, $cleanData, $firstQuestionNodeId) {
            // 1. Save Form Submission
            Database::execute(
                "INSERT INTO form_submissions (tenant_id, funnel_id, funnel_version_id, session_id, submitted_at) VALUES (?, ?, ?, ?, NOW())",
                [$session['tenant_id'], $session['funnel_id'], $session['funnel_version_id'], $session['id']]
            );
            $formSubId = (int) Database::lastInsertId();

            // 2. Save Snapshotted Form Values
            foreach ($fields as $f) {
                $key = $f['field_key'];
                $val = $cleanData[$key] ?? '';
                Database::execute(
                    "INSERT INTO form_submission_values (tenant_id, form_submission_id, field_id, field_key_snapshot, field_label_snapshot, field_type_snapshot, value_text) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$session['tenant_id'], $formSubId, $f['id'] ?? null, $key, $f['label'], $f['field_type'], $val]
                );
            }

            // 3. Record Events
            Database::execute(
                "INSERT INTO quiz_step_events (tenant_id, funnel_id, funnel_version_id, session_id, event_type, occurred_at) VALUES (?, ?, ?, ?, 'pre_form_submitted', NOW())",
                [$session['tenant_id'], $session['funnel_id'], $session['funnel_version_id'], $session['id']]
            );
            Database::execute(
                "INSERT INTO quiz_step_events (tenant_id, funnel_id, funnel_version_id, session_id, event_type, occurred_at) VALUES (?, ?, ?, ?, 'quiz_started', NOW())",
                [$session['tenant_id'], $session['funnel_id'], $session['funnel_version_id'], $session['id']]
            );

            // 4. Advance Session
            Database::execute(
                "UPDATE quiz_sessions SET status = 'in_progress', started_at = NOW(), current_node_id = ?, last_activity_at = NOW() WHERE id = ?",
                [$firstQuestionNodeId, $session['id']]
            );
        });

        // 5. Trigger Webhook Event (lead.captured)
        try {
            WebhookService::trigger((int)$session['tenant_id'], 'lead.captured', [
                'session_id' => (int)$session['id'],
                'funnel_id' => (int)$session['funnel_id'],
                'funnel_name' => $session['funnel_name'],
                'lead_data' => $cleanData,
                'submitted_at' => date('Y-m-d H:i:s'),
            ], (int)$session['funnel_id']);
        } catch (\Throwable $e) {
            Logger::error("Webhook dispatch failed: " . $e->getMessage());
        }

        return self::getCurrentStep($rawToken);
    }

    public static function getCurrentStep(string $rawToken): array
    {
        $session = self::resolveSession($rawToken);
        if (!$session) {
            throw new RuntimeException("Invalid or expired session.");
        }

        $graphSnapshot = json_decode($session['graph_snapshot_json'], true) ?: [];
        $settingsSnapshot = json_decode($session['settings_snapshot_json'], true) ?: [];

        // Check if session is completed
        if ($session['status'] === 'completed') {
            return self::getCompletedStep($session, $graphSnapshot, $settingsSnapshot);
        }

        // Check if still pending form
        if ($session['status'] === 'form_pending') {
            $formSnapshot = json_decode($session['form_schema_snapshot_json'], true) ?: [];
            return [
                'success' => true,
                'status' => 'form_pending',
                'funnel_name' => $session['funnel_name'],
                'settings' => $settingsSnapshot,
                'form_fields' => $formSnapshot['fields'] ?? [],
            ];
        }

        $currentNodeId = (int)$session['current_node_id'];
        $node = null;
        foreach ($graphSnapshot['nodes'] ?? [] as $n) {
            if ((int)$n['id'] === $currentNodeId) {
                $node = $n;
                break;
            }
        }

        if (!$node) {
            throw new RuntimeException("Current question node could not be resolved from graph snapshot.");
        }

        // If current node is a destination node, complete it
        if ($node['node_type'] === 'destination') {
            return self::completeSession($session, $node, $graphSnapshot, $settingsSnapshot);
        }

        // If current node is a form node, return form step
        if ($node['node_type'] === 'form') {
            $formConfig = !empty($node['config_json']) ? (json_decode($node['config_json'], true) ?: []) : [];
            $historyCount = (int) Database::fetchColumn(
                "SELECT COUNT(DISTINCT node_id) FROM quiz_answers WHERE session_id = ? AND superseded_at IS NULL",
                [$session['id']]
            );

            return [
                'success' => true,
                'status' => 'form_step',
                'funnel_name' => $session['funnel_name'],
                'settings' => $settingsSnapshot,
                'step' => [
                    'node_id' => $currentNodeId,
                    'node_type' => 'form',
                    'title' => $node['title'] ?? 'Lead Capture Form',
                    'form_fields' => $formConfig['fields'] ?? [],
                    'submit_action' => $formConfig['submit_action'] ?? 'redirect',
                    'button_text' => $formConfig['button_text'] ?? 'Submit Details',
                    'success_heading' => $formConfig['success_heading'] ?? 'Thank You!',
                    'success_message' => $formConfig['success_message'] ?? 'Your submission has been received.',
                    'step_number' => $historyCount + 1,
                    'can_go_back' => $historyCount > 0 && !empty($settingsSnapshot['enable_back_button']),
                ]
            ];
        }

        // Resolve question & public options
        $question = null;
        foreach ($graphSnapshot['questions'] ?? [] as $q) {
            if ((int)$q['node_id'] === $currentNodeId) {
                $question = $q;
                break;
            }
        }

        if (!$question) {
            throw new RuntimeException("Question details missing for node {$currentNodeId}.");
        }

        $options = [];
        foreach ($graphSnapshot['options'] ?? [] as $opt) {
            if ((int)$opt['question_id'] === (int)$question['id']) {
                $options[] = [
                    'id' => $opt['id'],
                    'option_key' => $opt['option_key'],
                    'label' => $opt['label'],
                    'display_order' => $opt['display_order'],
                ];
            }
        }

        // Fetch previous answer if revisiting via back-button
        $existingAnswer = Database::fetchOne(
            "SELECT * FROM quiz_answers WHERE session_id = ? AND node_id = ? AND superseded_at IS NULL ORDER BY id DESC LIMIT 1",
            [$session['id'], $currentNodeId]
        );

        $selectedOptionIds = [];
        if ($existingAnswer && !empty($existingAnswer['selected_option_ids_json'])) {
            $selectedOptionIds = json_decode($existingAnswer['selected_option_ids_json'], true) ?: [];
        }

        // History count (for progress indication)
        $historyCount = (int) Database::fetchColumn(
            "SELECT COUNT(DISTINCT node_id) FROM quiz_answers WHERE session_id = ? AND superseded_at IS NULL",
            [$session['id']]
        );

        $hasPreviousStep = $historyCount > 0;

        return [
            'success' => true,
            'status' => 'in_progress',
            'funnel_name' => $session['funnel_name'],
            'settings' => $settingsSnapshot,
            'step' => [
                'node_id' => $currentNodeId,
                'question_id' => $question['id'],
                'question_text' => $question['question_text'],
                'help_text' => $question['help_text'],
                'question_type' => $question['question_type'],
                'is_required' => (bool)$question['is_required'],
                'options' => $options,
                'selected_option_ids' => $selectedOptionIds,
                'step_number' => $historyCount + 1,
                'can_go_back' => $hasPreviousStep && !empty($settingsSnapshot['enable_back_button']),
            ]
        ];
    }

    public static function submitAnswer(string $rawToken, int $nodeId, array $answerPayload): array
    {
        $session = self::resolveSession($rawToken);
        if (!$session) {
            throw new RuntimeException("Invalid or expired session.");
        }

        if ($session['status'] !== 'in_progress') {
            return self::getCurrentStep($rawToken);
        }

        if ((int)$session['current_node_id'] !== $nodeId) {
            throw new RuntimeException("Answer submission out of sequence.");
        }

        $graphSnapshot = json_decode($session['graph_snapshot_json'], true) ?: [];
        $settingsSnapshot = json_decode($session['settings_snapshot_json'], true) ?: [];

        $node = null;
        foreach ($graphSnapshot['nodes'] ?? [] as $n) {
            if ((int)$n['id'] === $nodeId) {
                $node = $n;
                break;
            }
        }

        if ($node && $node['node_type'] === 'form') {
            return self::submitFormNode($rawToken, $session, $node, $answerPayload, $graphSnapshot, $settingsSnapshot);
        }

        // Find question
        $question = null;
        foreach ($graphSnapshot['questions'] ?? [] as $q) {
            if ((int)$q['node_id'] === $nodeId) {
                $question = $q;
                break;
            }
        }

        if (!$question) {
            throw new RuntimeException("Question not found.");
        }

        $selectedOptionId = isset($answerPayload['option_id']) ? (int)$answerPayload['option_id'] : null;
        if (!$selectedOptionId) {
            throw new RuntimeException("Please select an answer to continue.");
        }

        // Verify option belongs to question
        $selectedOption = null;
        foreach ($graphSnapshot['options'] ?? [] as $opt) {
            if ((int)$opt['id'] === $selectedOptionId && (int)$opt['question_id'] === (int)$question['id']) {
                $selectedOption = $opt;
                break;
            }
        }

        if (!$selectedOption) {
            throw new RuntimeException("Invalid answer choice.");
        }

        // Find next edge from graph snapshot
        $targetNodeId = null;
        foreach ($graphSnapshot['edges'] ?? [] as $edge) {
            if ((int)$edge['source_node_id'] === $nodeId && (int)$edge['source_option_id'] === $selectedOptionId) {
                $targetNodeId = (int)$edge['target_node_id'];
                break;
            }
        }

        if (!$targetNodeId) {
            throw new RuntimeException("Routing error: No destination connected for this option.");
        }

        // Persist Answer and Advance
        Database::transaction(function () use ($session, $nodeId, $question, $selectedOption, $selectedOptionId, $targetNodeId) {
            // Invalidate any subsequent downstream answers if user had navigated back and changed an earlier answer
            // Find visit sequence after current node
            Database::execute(
                "UPDATE quiz_answers SET superseded_at = NOW() WHERE session_id = ? AND node_id = ? AND superseded_at IS NULL",
                [$session['id'], $nodeId]
            );

            // Record Answer
            Database::execute(
                "INSERT INTO quiz_answers (tenant_id, session_id, funnel_version_id, node_id, question_id, question_text_snapshot, answer_type, answer_value_text, selected_option_ids_json, answered_at) 
                 VALUES (?, ?, ?, ?, ?, ?, 'single_choice', ?, ?, NOW())",
                [
                    $session['tenant_id'],
                    $session['id'],
                    $session['funnel_version_id'],
                    $nodeId,
                    $question['id'],
                    $question['question_text'],
                    $selectedOption['label'],
                    json_encode([$selectedOptionId])
                ]
            );

            // Record Event
            Database::execute(
                "INSERT INTO quiz_step_events (tenant_id, funnel_id, funnel_version_id, session_id, event_type, node_id, option_id, occurred_at) 
                 VALUES (?, ?, ?, ?, 'branch_selected', ?, ?, NOW())",
                [$session['tenant_id'], $session['funnel_id'], $session['funnel_version_id'], $session['id'], $nodeId, $selectedOptionId]
            );

            // Advance Session current node
            Database::execute(
                "UPDATE quiz_sessions SET current_node_id = ?, last_activity_at = NOW() WHERE id = ?",
                [$targetNodeId, $session['id']]
            );
        });

        return self::getCurrentStep($rawToken);
    }

    public static function navigateBack(string $rawToken): array
    {
        $session = self::resolveSession($rawToken);
        if (!$session || $session['status'] === 'form_pending') {
            return self::getCurrentStep($rawToken);
        }

        $currentNodeId = (int)$session['current_node_id'];
        // Find the most recent active answer that is NOT the current node
        $previousAnswer = Database::fetchOne(
            "SELECT * FROM quiz_answers WHERE session_id = ? AND superseded_at IS NULL AND node_id != ? ORDER BY id DESC LIMIT 1",
            [$session['id'], $currentNodeId]
        );

        if ($previousAnswer) {
            // Set current node to this previous question node and ensure status is in_progress
            $prevNodeId = (int)$previousAnswer['node_id'];
            Database::execute(
                "UPDATE quiz_sessions SET status = 'in_progress', current_node_id = ?, last_activity_at = NOW() WHERE id = ?",
                [$prevNodeId, $session['id']]
            );
        }

        return self::getCurrentStep($rawToken);
    }

    private static function completeSession(array $session, array $destNode, array $graphSnapshot, array $settingsSnapshot): array
    {
        $destId = null;
        $destName = $destNode['title'] ?? 'Completion';
        $destUrl = 'https://example.com';

        foreach ($graphSnapshot['destinations'] ?? [] as $d) {
            if ((int)$d['node_id'] === (int)$destNode['id']) {
                $destId = (int)$d['id'];
                $destName = $d['name'];
                $destUrl = $d['url'];
                break;
            }
        }

        // Generate unguessable result token
        $rawResultToken = bin2hex(random_bytes(32));
        $resultTokenHash = hash('sha256', $rawResultToken);

        // Fetch lead form details
        $formVals = Database::query(
            "SELECT fsv.field_label_snapshot, fsv.value_text 
             FROM form_submissions fs
             JOIN form_submission_values fsv ON fsv.form_submission_id = fs.id
             WHERE fs.session_id = ? 
             ORDER BY fsv.id ASC",
            [$session['id']]
        );

        $answers = Database::query(
            "SELECT question_text_snapshot, answer_value_text, answered_at 
             FROM quiz_answers 
             WHERE session_id = ? AND superseded_at IS NULL AND (answer_type != 'form' OR answer_type IS NULL)
             ORDER BY id ASC",
            [$session['id']]
        );

        $summary = [
            'funnel_name' => $session['funnel_name'],
            'lead_fields' => $formVals,
            'answers' => $answers,
            'destination_name' => $destName,
            'destination_url' => $destUrl,
            'completed_at' => date('Y-m-d H:i:s'),
        ];

        Database::transaction(function () use ($session, $destNode, $destId, $resultTokenHash, $summary) {
            Database::execute(
                "UPDATE quiz_sessions SET status = 'completed', completed_at = NOW(), final_destination_id = ?, last_activity_at = NOW() WHERE id = ?",
                [$destId, $session['id']]
            );

            Database::execute(
                "INSERT INTO quiz_results (tenant_id, funnel_id, funnel_version_id, session_id, result_token_hash, summary_json, destination_id, redirect_status, completed_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW()) 
                 ON DUPLICATE KEY UPDATE summary_json = VALUES(summary_json), destination_id = VALUES(destination_id)",
                [$session['tenant_id'], $session['funnel_id'], $session['funnel_version_id'], $session['id'], $resultTokenHash, json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $destId]
            );

            Database::execute(
                "INSERT INTO quiz_step_events (tenant_id, funnel_id, funnel_version_id, session_id, event_type, node_id, occurred_at) 
                 VALUES (?, ?, ?, ?, 'quiz_completed', ?, NOW())",
                [$session['tenant_id'], $session['funnel_id'], $session['funnel_version_id'], $session['id'], $destNode['id']]
            );
        });

        // Trigger Webhook Event (quiz.completed)
        try {
            WebhookService::trigger((int)$session['tenant_id'], 'quiz.completed', [
                'session_id' => (int)$session['id'],
                'funnel_id' => (int)$session['funnel_id'],
                'funnel_name' => $session['funnel_name'],
                'destination_name' => $destName,
                'destination_url' => $destUrl,
                'lead_fields' => $formVals,
                'answers' => $answers,
                'completed_at' => date('Y-m-d H:i:s'),
            ], (int)$session['funnel_id']);
        } catch (\Throwable $e) {
            Logger::error("Webhook dispatch failed: " . $e->getMessage());
        }

        return [
            'success' => true,
            'status' => 'completed',
            'funnel_name' => $session['funnel_name'],
            'settings' => $settingsSnapshot,
            'result' => [
                'result_token' => $rawResultToken,
                'destination_name' => $destName,
                'destination_url' => $destUrl,
                'summary' => $summary,
                'auto_redirect' => !empty($settingsSnapshot['auto_redirect']),
                'redirect_delay_seconds' => (int)($settingsSnapshot['redirect_delay_seconds'] ?? 3),
            ]
        ];
    }

    public static function submitFormNode(string $rawToken, array $session, array $node, array $answerPayload, array $graphSnapshot, array $settingsSnapshot): array
    {
        $nodeId = (int)$node['id'];
        $formConfig = !empty($node['config_json']) ? (json_decode($node['config_json'], true) ?: []) : [];
        $fields = $formConfig['fields'] ?? [];
        $formData = $answerPayload['form_data'] ?? $answerPayload;

        // Validate required fields
        $errors = [];
        foreach ($fields as $f) {
            $key = $f['field_key'];
            $val = trim($formData[$key] ?? '');
            if (!empty($f['is_required']) && $val === '') {
                $errors[$key] = ($f['label'] ?? $key) . ' is required.';
            }
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'error_code' => 'FORM_VALIDATION_FAILED',
                'error' => reset($errors),
                'errors' => $errors,
            ];
        }

        $submitAction = $formConfig['submit_action'] ?? 'redirect';

        // 1. Save Form Submission into DB
        Database::transaction(function () use ($session, $nodeId, $node, $fields, $formData) {
            Database::execute(
                "INSERT INTO form_submissions (tenant_id, funnel_id, funnel_version_id, session_id, submitted_at) VALUES (?, ?, ?, ?, NOW())",
                [$session['tenant_id'], $session['funnel_id'], $session['funnel_version_id'], $session['id']]
            );
            $formSubId = (int) Database::lastInsertId();

            foreach ($fields as $f) {
                $key = $f['field_key'];
                $val = isset($formData[$key]) ? (string)$formData[$key] : '';
                Database::execute(
                    "INSERT INTO form_submission_values (tenant_id, form_submission_id, field_id, field_key_snapshot, field_label_snapshot, field_type_snapshot, value_text) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$session['tenant_id'], $formSubId, $f['id'] ?? null, $key, $f['label'] ?? $key, $f['field_type'] ?? 'text', $val]
                );
            }

            // Invalidate any previous answer for this node if user navigated back
            Database::execute(
                "UPDATE quiz_answers SET superseded_at = NOW() WHERE session_id = ? AND node_id = ? AND superseded_at IS NULL",
                [$session['id'], $nodeId]
            );

            // Record in quiz_answers for sequence tracking
            Database::execute(
                "INSERT INTO quiz_answers (tenant_id, session_id, funnel_version_id, node_id, question_id, question_text_snapshot, answer_type, answer_value_text, selected_option_ids_json, answered_at) 
                 VALUES (?, ?, ?, ?, NULL, ?, 'form', ?, ?, NOW())",
                [
                    $session['tenant_id'],
                    $session['id'],
                    $session['funnel_version_id'],
                    $nodeId,
                    $node['title'] ?? 'Form Step',
                    json_encode($formData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    json_encode(array_keys($formData))
                ]
            );

            Database::execute(
                "INSERT INTO quiz_step_events (tenant_id, funnel_id, funnel_version_id, session_id, event_type, node_id, occurred_at) 
                 VALUES (?, ?, ?, ?, 'form_step_submitted', ?, NOW())",
                [$session['tenant_id'], $session['funnel_id'], $session['funnel_version_id'], $session['id'], $nodeId]
            );
        });

        // 2. Post-submit decision: Message (No redirection) vs Redirect
        if ($submitAction === 'message') {
            return self::completeSessionWithMessage($session, $node, $formConfig, $graphSnapshot, $settingsSnapshot);
        }

        // Redirect: advance to target node from graph snapshot
        $edges = $graphSnapshot['edges'] ?? [];
        $targetNodeId = null;
        foreach ($edges as $e) {
            if ((int)$e['source_node_id'] === $nodeId) {
                $targetNodeId = (int)$e['target_node_id'];
                break;
            }
        }

        if (!$targetNodeId) {
            throw new RuntimeException("Routing error: No next step connected after form submission.");
        }

        Database::execute(
            "UPDATE quiz_sessions SET current_node_id = ?, last_activity_at = NOW() WHERE id = ?",
            [$targetNodeId, $session['id']]
        );

        return self::getCurrentStep($rawToken);
    }

    private static function completeSessionWithMessage(array $session, array $formNode, array $formConfig, array $graphSnapshot, array $settingsSnapshot): array
    {
        $rawResultToken = bin2hex(random_bytes(32));
        $resultTokenHash = hash('sha256', $rawResultToken);

        $heading = !empty($formConfig['success_heading']) ? $formConfig['success_heading'] : 'Thank You!';
        $message = !empty($formConfig['success_message']) ? $formConfig['success_message'] : 'Your submission has been received.';

        // Fetch lead form details
        $formVals = Database::query(
            "SELECT fsv.field_label_snapshot, fsv.value_text 
             FROM form_submissions fs
             JOIN form_submission_values fsv ON fsv.form_submission_id = fs.id
             WHERE fs.session_id = ? 
             ORDER BY fsv.id ASC",
            [$session['id']]
        );

        $answers = Database::query(
            "SELECT question_text_snapshot, answer_value_text, answered_at 
             FROM quiz_answers 
             WHERE session_id = ? AND superseded_at IS NULL AND (answer_type != 'form' OR answer_type IS NULL)
             ORDER BY id ASC",
            [$session['id']]
        );

        $summary = [
            'funnel_name' => $session['funnel_name'],
            'completion_type' => 'message',
            'success_heading' => $heading,
            'success_message' => $message,
            'lead_fields' => $formVals,
            'answers' => $answers,
            'completed_at' => date('Y-m-d H:i:s'),
        ];

        Database::transaction(function () use ($session, $formNode, $resultTokenHash, $summary) {
            Database::execute(
                "UPDATE quiz_sessions SET status = 'completed', completed_at = NOW(), last_activity_at = NOW() WHERE id = ?",
                [$session['id']]
            );

            Database::execute(
                "INSERT INTO quiz_results (tenant_id, funnel_id, funnel_version_id, session_id, result_token_hash, summary_json, destination_id, redirect_status, completed_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NULL, 'none', NOW()) 
                 ON DUPLICATE KEY UPDATE summary_json = VALUES(summary_json)",
                [$session['tenant_id'], $session['funnel_id'], $session['funnel_version_id'], $session['id'], $resultTokenHash, json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
            );

            Database::execute(
                "INSERT INTO quiz_step_events (tenant_id, funnel_id, funnel_version_id, session_id, event_type, node_id, occurred_at) 
                 VALUES (?, ?, ?, ?, 'quiz_completed', ?, NOW())",
                [$session['tenant_id'], $session['funnel_id'], $session['funnel_version_id'], $session['id'], $formNode['id']]
            );
        });

        // Trigger Webhook Event (quiz.completed)
        try {
            WebhookService::trigger((int)$session['tenant_id'], 'quiz.completed', [
                'session_id' => (int)$session['id'],
                'funnel_id' => (int)$session['funnel_id'],
                'funnel_name' => $session['funnel_name'],
                'completion_type' => 'message',
                'success_heading' => $heading,
                'lead_fields' => $formVals,
                'answers' => $answers,
                'completed_at' => date('Y-m-d H:i:s'),
            ], (int)$session['funnel_id']);
        } catch (\Throwable $e) {
            Logger::error("Webhook dispatch failed: " . $e->getMessage());
        }

        return [
            'success' => true,
            'status' => 'completed',
            'funnel_name' => $session['funnel_name'],
            'settings' => $settingsSnapshot,
            'result' => [
                'result_token' => $rawResultToken,
                'completion_type' => 'message',
                'success_heading' => $heading,
                'success_message' => $message,
                'summary' => $summary,
                'auto_redirect' => false,
            ]
        ];
    }

    private static function getCompletedStep(array $session, array $graphSnapshot, array $settingsSnapshot): array
    {
        $result = Database::fetchOne("SELECT * FROM quiz_results WHERE session_id = ?", [$session['id']]);
        $summary = $result ? json_decode($result['summary_json'], true) : [];

        if (($summary['completion_type'] ?? '') === 'message') {
            return [
                'success' => true,
                'status' => 'completed',
                'funnel_name' => $session['funnel_name'],
                'settings' => $settingsSnapshot,
                'result' => [
                    'completion_type' => 'message',
                    'success_heading' => $summary['success_heading'] ?? 'Thank You!',
                    'success_message' => $summary['success_message'] ?? 'Your submission has been received.',
                    'summary' => $summary,
                    'auto_redirect' => false,
                ]
            ];
        }

        $destName = $summary['destination_name'] ?? 'Your Results';
        $destUrl = $summary['destination_url'] ?? '#';

        return [
            'success' => true,
            'status' => 'completed',
            'funnel_name' => $session['funnel_name'],
            'settings' => $settingsSnapshot,
            'result' => [
                'destination_name' => $destName,
                'destination_url' => $destUrl,
                'summary' => $summary,
                'auto_redirect' => !empty($settingsSnapshot['auto_redirect']),
            ]
        ];
    }

    public static function getPublicResult(string $rawResultToken): ?array
    {
        $hash = hash('sha256', $rawResultToken);
        $result = Database::fetchOne(
            "SELECT qr.*, f.name as funnel_name, f.settings_json 
             FROM quiz_results qr
             JOIN funnels f ON qr.funnel_id = f.id
             WHERE qr.result_token_hash = ?",
            [$hash]
        );

        if (!$result) {
            return null;
        }

        return [
            'funnel_name' => $result['funnel_name'],
            'summary' => json_decode($result['summary_json'], true) ?: [],
            'completed_at' => $result['completed_at'],
        ];
    }
}
