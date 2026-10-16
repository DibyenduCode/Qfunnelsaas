<?php

namespace App\Services;

use App\Core\Database;

class UsageLimitService
{
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
