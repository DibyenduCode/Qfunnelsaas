<?php

namespace App\Services;

use App\Core\Database;

class UsageLimitService
{
    /**
     * Complete system feature catalog with categorized metadata and premium flags.
     */
    public static function getFeatureCatalog(): array
    {
        return [
            // 1. Builder & Logic
            'visual_builder' => [
                'name' => 'Visual Node Canvas Builder',
                'category' => 'Builder & Logic',
                'is_premium' => false,
                'description' => 'Interactive visual drag-and-drop canvas with question, form, and outcome nodes.',
            ],
            'conditional_logic' => [
                'name' => 'Branching & Conditional Logic',
                'category' => 'Builder & Logic',
                'is_premium' => false,
                'description' => 'Multi-branch question pathing and smart conditional routing based on visitor responses.',
            ],
            'lead_capture_forms' => [
                'name' => 'Lead Capture Forms & Fields',
                'category' => 'Builder & Logic',
                'is_premium' => false,
                'description' => 'Custom form steps with validation, email, phone, name, and GDPR consent prompts.',
            ],
            'dynamic_scoring' => [
                'name' => 'Dynamic Scoring & Calculations',
                'category' => 'Builder & Logic',
                'is_premium' => false,
                'description' => 'Score points per answer and calculate category outcomes or custom recommendations.',
            ],
            'calculator_mode' => [
                'name' => 'Interactive Calculator & Quote Formulas',
                'category' => 'Builder & Logic',
                'is_premium' => true,
                'description' => 'Dynamic math formula engine (@Q1 * 100 + @Q2), pricing estimators, and instant quote cards.',
            ],
            'funnel_cloning' => [
                'name' => '1-Click Funnel Duplication',
                'category' => 'Builder & Logic',
                'is_premium' => false,
                'description' => 'Instantly clone funnels and duplicate complete node graphs with one click.',
            ],

            // 2. Branding & Presentation (White-Label)
            'custom_branding' => [
                'name' => 'Custom Brand Styling & Colors',
                'category' => 'Branding & White-Label',
                'is_premium' => false,
                'description' => 'Custom logo upload, header positioning, accent color palette, and corner radii.',
            ],
            'white_label' => [
                'name' => '100% Complete White-Labeling',
                'category' => 'Branding & White-Label',
                'is_premium' => true,
                'description' => 'Removes all "Powered by QFunnel" badges and platform branding from public quizzes.',
            ],
            'custom_trust_badges' => [
                'name' => 'Trust Badges & Security Seals',
                'category' => 'Branding & White-Label',
                'is_premium' => false,
                'description' => 'Add 256-bit SSL encryption seals, guarantee badges, and custom reassurance copy.',
            ],
            'custom_footer' => [
                'name' => 'Custom Footer & Legal Links',
                'category' => 'Branding & White-Label',
                'is_premium' => false,
                'description' => 'Customize footer copyright notices, Terms of Service, and Privacy Policy URLs.',
            ],

            // 3. Website Embedding & Distribution
            'embed_widgets' => [
                'name' => 'Universal Website Embed Widgets',
                'category' => 'Embedding & Distribution',
                'is_premium' => true,
                'description' => 'Embed funnels onto WordPress, Shopify, Webflow via Inline, Lightbox Popup, or Floating Bubble.',
            ],
            'quick_connect' => [
                'name' => '1-Click WhatsApp & Phone Direct Connect',
                'category' => 'Embedding & Distribution',
                'is_premium' => false,
                'description' => 'Direct WhatsApp click-to-chat with dynamic quote tags, and phone dialer outcome buttons.',
            ],
            'auto_redirect' => [
                'name' => 'Instant & Delayed Outcome Redirects',
                'category' => 'Embedding & Distribution',
                'is_premium' => false,
                'description' => 'Auto-redirect respondents to external outcome URLs, offer pages, or booking calendars.',
            ],
            'back_navigation' => [
                'name' => 'Back Navigation & Step Progress',
                'category' => 'Embedding & Distribution',
                'is_premium' => false,
                'description' => 'Allow respondents to navigate backwards to edit answers, with a dynamic progress bar.',
            ],

            // 4. Analytics & Data Export
            'analytics' => [
                'name' => 'Conversion & Traffic Analytics',
                'category' => 'Analytics & Data',
                'is_premium' => false,
                'description' => 'Comprehensive funnel metrics: visitors, starts, completions, conversion rate & device breakdown.',
            ],
            'step_dropoff_analytics' => [
                'name' => 'Visual Step Drop-Off Funnel',
                'category' => 'Analytics & Data',
                'is_premium' => true,
                'description' => 'Step-by-step abandonment analysis pinpointing where visitors drop off.',
            ],
            'csv_export' => [
                'name' => '1-Click CSV Lead & Journey Export',
                'category' => 'Analytics & Data',
                'is_premium' => true,
                'description' => 'Download all lead submissions, form answers, and visitor timeline data as CSV files.',
            ],
            'journey_inspector' => [
                'name' => 'Visitor Journey Timeline Inspector',
                'category' => 'Analytics & Data',
                'is_premium' => false,
                'description' => 'Inspect the full chronological step answer history and device info for every lead.',
            ],

            // 5. Integrations & Automation
            'webhooks' => [
                'name' => 'Outbound HTTP Webhooks',
                'category' => 'Integrations & Automation',
                'is_premium' => true,
                'description' => 'Send real-time JSON payloads to Zapier, Make, custom CRMs, or HTTP endpoints on completion.',
            ],
            'webhook_retry' => [
                'name' => 'Webhook Logs & Failed Delivery Retries',
                'category' => 'Integrations & Automation',
                'is_premium' => true,
                'description' => 'Inspect outbound HTTP logs, error responses, and 1-click retry failed deliveries.',
            ],
            'api_access' => [
                'name' => 'REST API & Developer Access',
                'category' => 'Integrations & Automation',
                'is_premium' => true,
                'description' => 'Programmatic workspace API keys to query funnels and submissions externally.',
            ],

            // 6. Team & Enterprise SLA
            'team_collaboration' => [
                'name' => 'Multi-User Team Roles & Permissions',
                'category' => 'Team & SLA Support',
                'is_premium' => false,
                'description' => 'Collaborate with team members across Owner, Admin, Editor, and Analyst roles.',
            ],
            'audit_logs' => [
                'name' => 'Security Audit & Activity Logs',
                'category' => 'Team & SLA Support',
                'is_premium' => true,
                'description' => 'Detailed audit trail of workspace configuration changes, publishes, and member actions.',
            ],
            'priority_support' => [
                'name' => '24/7 Priority SLA & Dedicated Support',
                'category' => 'Team & SLA Support',
                'is_premium' => true,
                'description' => 'Direct expedited support queue with prioritized SLA response times.',
            ],
        ];
    }

    /**
     * Get feature flags for a tenant.
     */
    public static function getTenantFeatures(int $tenantId): array
    {
        $sub = Database::fetchOne(
            "SELECT ts.*, p.name as plan_name, p.slug as plan_slug, p.features_json 
             FROM tenant_subscriptions ts
             JOIN plans p ON ts.plan_id = p.id
             WHERE ts.tenant_id = ? AND ts.status IN ('active', 'trial')
             ORDER BY ts.id DESC LIMIT 1",
            [$tenantId]
        );

        $catalog = self::getFeatureCatalog();
        $features = [];

        $planFeatures = [];
        $planSlug = $sub['plan_slug'] ?? 'starter';
        if ($sub && !empty($sub['features_json'])) {
            $planFeatures = json_decode($sub['features_json'], true) ?: [];
        }

        $isAllInclusive = in_array($planSlug, ['pro', 'enterprise'], true);

        foreach ($catalog as $key => $meta) {
            if (isset($planFeatures[$key])) {
                $features[$key] = (bool)$planFeatures[$key];
            } elseif ($isAllInclusive) {
                $features[$key] = true;
            } else {
                // If not set, standard features default to true, premium features default to false
                $features[$key] = !$meta['is_premium'];
            }
        }

        return $features;
    }

    /**
     * Check if a tenant has access to a specific feature.
     */
    public static function hasFeature(int $tenantId, string $featureKey): bool
    {
        $features = self::getTenantFeatures($tenantId);
        return !empty($features[$featureKey]);
    }

    public static function getTenantLimits(int $tenantId): array
    {
        $sub = Database::fetchOne(
            "SELECT ts.*, p.name as plan_name, p.slug as plan_slug, p.limits_json, p.features_json 
             FROM tenant_subscriptions ts
             JOIN plans p ON ts.plan_id = p.id
             WHERE ts.tenant_id = ? AND ts.status IN ('active', 'trial')
             ORDER BY ts.id DESC LIMIT 1",
            [$tenantId]
        );

        $limits = [
            'max_funnels' => 3,
            'max_submissions_per_month' => 200,
            'max_team_members' => 2,
            'plan_name' => 'Starter Trial',
            'plan_slug' => 'starter',
            'status' => 'trial',
        ];

        if ($sub) {
            $planLimits = json_decode($sub['limits_json'] ?? '{}', true) ?: [];
            $limits['max_funnels'] = (int)($planLimits['max_funnels'] ?? 3);
            $limits['max_submissions_per_month'] = (int)($planLimits['max_submissions_per_month'] ?? 200);
            $limits['max_team_members'] = (int)($planLimits['max_team_members'] ?? 2);
            $limits['plan_name'] = $sub['plan_name'];
            $limits['plan_slug'] = $sub['plan_slug'];
            $limits['status'] = $sub['status'];
        }

        return $limits;
    }

    public static function checkCanCreateFunnel(int $tenantId): array
    {
        $limits = self::getTenantLimits($tenantId);
        $currentCount = (int) Database::fetchColumn("SELECT COUNT(*) FROM funnels WHERE tenant_id = ? AND status != 'archived'", [$tenantId]);

        if ($limits['max_funnels'] > 0 && $currentCount >= $limits['max_funnels']) {
            return [
                'allowed' => false,
                'message' => "You have reached your plan limit of {$limits['max_funnels']} funnels. Upgrade your subscription to create more.",
                'current' => $currentCount,
                'limit' => $limits['max_funnels'],
            ];
        }

        return ['allowed' => true, 'current' => $currentCount, 'limit' => $limits['max_funnels']];
    }

    public static function checkCanAddTeamMember(int $tenantId): array
    {
        $limits = self::getTenantLimits($tenantId);
        $currentCount = (int) Database::fetchColumn("SELECT COUNT(*) FROM tenant_memberships WHERE tenant_id = ?", [$tenantId]);

        if ($limits['max_team_members'] > 0 && $currentCount >= $limits['max_team_members']) {
            return [
                'allowed' => false,
                'message' => "You have reached your plan limit of {$limits['max_team_members']} team members. Upgrade your plan to add more seats.",
                'current' => $currentCount,
                'limit' => $limits['max_team_members'],
            ];
        }

        return ['allowed' => true, 'current' => $currentCount, 'limit' => $limits['max_team_members']];
    }

    public static function getUsageOverview(int $tenantId): array
    {
        $limits = self::getTenantLimits($tenantId);
        $funnelCount = (int) Database::fetchColumn("SELECT COUNT(*) FROM funnels WHERE tenant_id = ? AND status != 'archived'", [$tenantId]);
        $memberCount = (int) Database::fetchColumn("SELECT COUNT(*) FROM tenant_memberships WHERE tenant_id = ?", [$tenantId]);

        $firstDayOfMonth = date('Y-m-01 00:00:00');
        $monthlySubmissions = (int) Database::fetchColumn(
            "SELECT COUNT(*) FROM quiz_sessions WHERE tenant_id = ? AND started_at >= ?",
            [$tenantId, $firstDayOfMonth]
        );

        return [
            'plan_name' => $limits['plan_name'],
            'plan_slug' => $limits['plan_slug'],
            'status' => $limits['status'],
            'funnels' => [
                'used' => $funnelCount,
                'limit' => $limits['max_funnels'],
                'is_unlimited' => $limits['max_funnels'] <= 0,
                'percentage' => $limits['max_funnels'] > 0 ? min(100, round(($funnelCount / $limits['max_funnels']) * 100)) : 0,
            ],
            'submissions' => [
                'used' => $monthlySubmissions,
                'limit' => $limits['max_submissions_per_month'],
                'is_unlimited' => $limits['max_submissions_per_month'] <= 0,
                'percentage' => $limits['max_submissions_per_month'] > 0 ? min(100, round(($monthlySubmissions / $limits['max_submissions_per_month']) * 100)) : 0,
            ],
            'team_members' => [
                'used' => $memberCount,
                'limit' => $limits['max_team_members'],
                'is_unlimited' => $limits['max_team_members'] <= 0,
                'percentage' => $limits['max_team_members'] > 0 ? min(100, round(($memberCount / $limits['max_team_members']) * 100)) : 0,
            ],
        ];
    }
}
