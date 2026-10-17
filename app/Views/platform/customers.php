<?php
$tenantItems = $tenants['items'] ?? $tenants;
$userItems = $users['items'] ?? $users;
$totalTenantsCount = $tenants['total'] ?? count($tenantItems);
$totalUsersCount = $users['total'] ?? count($userItems);
?>
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Customer & User Management</h2>
            <p class="text-xs text-slate-500 mt-0.5">Control customer workspaces, user status, and subscription tier assignments.</p>
        </div>
        
        <!-- Search and Filter Form -->
        <form method="GET" action="<?= url('/admin/platform/customers') ?>" class="flex items-center gap-2">
            <div class="relative">
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search name or slug..." 
                       class="w-56 px-3 py-1.5 pl-8 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-sm">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <select name="status" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Only</option>
                <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>Suspended Only</option>
            </select>

            <?php if ($search !== '' || $statusFilter !== ''): ?>
                <a href="<?= url('/admin/platform/customers') ?>" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabs switcher: Customers (Workspaces) vs Users -->
    <div class="border-b border-[#E2E8F0] flex gap-4 text-xs font-semibold" id="mgmtTabs">
        <button type="button" onclick="switchMgmtTab('tab-tenants')" id="btn-tab-tenants" class="pb-3 border-b-2 border-indigo-600 text-indigo-600 font-bold transition-all">
            Customer Workspaces (<?= $totalTenantsCount ?>)
        </button>
        <button type="button" onclick="switchMgmtTab('tab-users')" id="btn-tab-users" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition-all">
            Platform Users (<?= $totalUsersCount ?>)
        </button>
    </div>

    <!-- TAB 1: Customer Workspaces (Tenants) -->
    <div id="tab-tenants" class="space-y-4">
        <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#E2E8F0] bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Workspace / Slug</th>
                            <th class="py-3 px-4">Owner Contact</th>
                            <th class="py-3 px-4">Current Plan</th>
                            <th class="py-3 px-4 text-center">Funnels</th>
                            <th class="py-3 px-4 text-center">Submissions</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Administrative Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        <?php if (empty($tenantItems)): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    No customer workspaces found matching criteria.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($tenantItems as $t): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-slate-900">
                                    <?= e($t['name']) ?>
                                    <span class="block text-[10px] text-slate-400 font-mono">slug: <?= e($t['slug']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    <span class="font-medium"><?= e($t['owner_name'] ?: 'N/A') ?></span>
                                    <span class="block text-[11px] text-slate-400"><?= e($t['owner_email'] ?: 'No owner linked') ?></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <?= e($t['plan_name'] ?: 'Starter') ?>
                                    </span>
                                    <span class="block text-[10px] text-slate-400 capitalize">status: <?= e($t['subscription_status'] ?? 'trial') ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    <?= (int)$t['funnels_count'] ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    <?= (int)$t['sessions_count'] ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($t['status'] === 'active'): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Suspended
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Assign Plan Button -->
                                        <button type="button" 
                                                onclick="openAssignPlanModal(<?= (int)$t['id'] ?>, '<?= e(addslashes($t['name'])) ?>', <?= (int)($t['current_plan_id'] ?? 1) ?>)"
                                                class="px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-semibold border border-indigo-200 transition-colors">
                                            Assign Plan
                                        </button>

                                        <!-- Suspend / Reactivate Workspace Button -->
                                        <form method="POST" action="<?= url("/admin/platform/tenants/{$t['id']}/toggle") ?>" class="inline" onsubmit="return confirm('<?= $t['status'] === 'active' ? 'Suspend this customer workspace?' : 'Reactivate this customer workspace?' ?>');">
                                            <?= csrf_field() ?>
                                            <?php if ($t['status'] === 'active'): ?>
                                                <button type="submit" class="px-2.5 py-1 rounded bg-rose-50 text-rose-700 font-semibold border border-rose-200 hover:bg-rose-100 transition-colors">
                                                    Suspend
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="px-2.5 py-1 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200 hover:bg-emerald-100 transition-colors">
                                                    Reactivate
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($tenants['total_pages'])): ?>
                <?php
                $pagination = $tenants;
                $queryParams = array_filter(['q' => $search ?? '', 'status' => $statusFilter ?? '']);
                require __DIR__ . '/../partials/pagination.php';
                ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB 2: Platform Users (Suspend any user) -->
    <div id="tab-users" class="hidden space-y-4">
        <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-[#E2E8F0] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Platform Registered Users</h3>
                    <p class="text-xs text-slate-500">View all registered accounts and suspend or reactivate access.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#E2E8F0] bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">User</th>
                            <th class="py-3 px-4">Email Address</th>
                            <th class="py-3 px-4">Associated Workspaces</th>
                            <th class="py-3 px-4">Role / Level</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Last Login</th>
                            <th class="py-3 px-4 text-right">Suspend Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        <?php if (empty($userItems)): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    No platform users found.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($userItems as $u): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 font-bold text-[10px] flex items-center justify-center">
                                            <?= strtoupper(substr($u['name'] ?: 'U', 0, 1)) ?>
                                        </div>
                                        <span><?= e($u['name']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-mono text-[11px]">
                                    <?= e($u['email']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    <?= e($u['workspace_names'] ?: 'None') ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($u['is_platform_admin'])): ?>
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            Super Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            Customer User
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($u['status'] === 'active'): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Suspended
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    <?= $u['last_login_at'] ? date('M j, Y H:i', strtotime($u['last_login_at'])) : 'Never' ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <?php if ((int)$u['id'] === (int)current_user()['id']): ?>
                                        <span class="text-[11px] text-slate-400 italic">Current Admin</span>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url("/admin/platform/users/{$u['id']}/toggle-status") ?>" class="inline" onsubmit="return confirm('<?= $u['status'] === 'active' ? "Suspend user {$u['email']}?" : "Reactivate user {$u['email']}?" ?>');">
                                            <?= csrf_field() ?>
                                            <?php if ($u['status'] === 'active'): ?>
                                                <button type="submit" class="px-2.5 py-1 rounded bg-rose-50 text-rose-700 font-semibold border border-rose-200 hover:bg-rose-100 transition-colors">
                                                    Suspend User
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="px-2.5 py-1 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200 hover:bg-emerald-100 transition-colors">
                                                    Activate User
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($users['total_pages'])): ?>
                <?php
                $pagination = $users;
                $queryParams = array_filter(['q' => $search ?? '', 'status' => $statusFilter ?? '']);
                $pageParam = 'user_page';
                require __DIR__ . '/../partials/pagination.php';
                ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal: Assign Plan to Customer -->
    <div id="assignPlanModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden border border-[#E2E8F0]">
            <div class="px-6 py-4 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900">Assign Subscription Plan</h3>
                <button type="button" onclick="closeAssignPlanModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="assignPlanForm" method="POST" action="" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target Workspace</label>
                    <input type="text" id="modalTenantName" readonly class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-slate-100 font-medium text-slate-700 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Select Subscription Plan</label>
                    <select name="plan_id" id="modalPlanSelect" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($plans as $p): ?>
                            <option value="<?= (int)$p['id'] ?>">
                                <?= e($p['name']) ?> (₹<?= number_format((float)$p['price'], 2) ?> / <?= e($p['billing_interval']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Subscription Status</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="active">Active (Paid)</option>
                        <option value="trial">Trial</option>
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeAssignPlanModal()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm transition-colors">
                        Confirm Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function switchMgmtTab(tabId) {
    document.getElementById('tab-tenants').classList.add('hidden');
    document.getElementById('tab-users').classList.add('hidden');

    document.getElementById('btn-tab-tenants').classList.remove('border-indigo-600', 'text-indigo-600', 'font-bold');
    document.getElementById('btn-tab-tenants').classList.add('border-transparent', 'text-slate-500');

    document.getElementById('btn-tab-users').classList.remove('border-indigo-600', 'text-indigo-600', 'font-bold');
    document.getElementById('btn-tab-users').classList.add('border-transparent', 'text-slate-500');

    document.getElementById(tabId).classList.remove('hidden');

    if (tabId === 'tab-tenants') {
        document.getElementById('btn-tab-tenants').classList.add('border-indigo-600', 'text-indigo-600', 'font-bold');
        document.getElementById('btn-tab-tenants').classList.remove('border-transparent', 'text-slate-500');
    } else {
        document.getElementById('btn-tab-users').classList.add('border-indigo-600', 'text-indigo-600', 'font-bold');
        document.getElementById('btn-tab-users').classList.remove('border-transparent', 'text-slate-500');
    }
}

function openAssignPlanModal(tenantId, tenantName, currentPlanId) {
    const modal = document.getElementById('assignPlanModal');
    const form = document.getElementById('assignPlanForm');
    const nameInput = document.getElementById('modalTenantName');
    const planSelect = document.getElementById('modalPlanSelect');

    form.action = '<?= url("/admin/platform/tenants") ?>/' + tenantId + '/assign-plan';
    nameInput.value = tenantName;
    if (planSelect) {
        planSelect.value = currentPlanId;
    }

    modal.classList.remove('hidden');
}

function closeAssignPlanModal() {
    document.getElementById('assignPlanModal').classList.add('hidden');
}

// Automatically activate users tab if user_page parameter is in URL
if (new URLSearchParams(window.location.search).has('user_page')) {
    switchMgmtTab('tab-users');
}
</script>
