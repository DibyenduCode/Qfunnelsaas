<div class="space-y-6">

    <!-- Page Header & Create Plan Trigger -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Subscription Plans & Limits</h2>
            <p class="text-xs text-slate-500 mt-0.5">Define, edit, and manage pricing tiers and funnel quotas for all users.</p>
        </div>
        <button type="button" onclick="openCreatePlanModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create New Plan
        </button>
    </div>

    <!-- Core Tier Cards (Free, Starter, Standard, Pro) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($plans as $p): 
            $limits = json_decode($p['limits_json'] ?? '{}', true) ?: [];
            $features = json_decode($p['features_json'] ?? '{}', true) ?: [];
            $maxFunnels = $limits['max_funnels'] ?? 1;
            $funnelsLabel = ($maxFunnels <= 0 || $maxFunnels == -1) ? 'Unlimited Funnels' : "{$maxFunnels} " . ($maxFunnels == 1 ? 'Funnel' : 'Funnels');
        ?>
            <div class="bg-white border rounded-xl p-5 shadow-sm flex flex-col justify-between transition-all hover:shadow-md <?= strtolower($p['slug']) === 'pro' ? 'border-purple-300 ring-2 ring-purple-100' : 'border-[#E2E8F0]' ?>">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-extrabold text-slate-900"><?= e($p['name']) ?></span>
                        <?php if (strtolower($p['slug']) === 'pro'): ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700">POPULAR</span>
                        <?php elseif ($p['is_active']): ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">Active</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500">Disabled</span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-slate-900">$<?= number_format((float)$p['price'], 2) ?></span>
                            <span class="text-xs text-slate-400">/<?= e($p['billing_interval'] ?? 'mo') ?></span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2"><?= e($p['description'] ?: 'Configured platform subscription tier') ?></p>
                    </div>

                    <!-- Quota Highlights -->
                    <div class="space-y-2 py-3 border-t border-slate-100 text-xs">
                        <div class="flex items-center justify-between text-slate-700">
                            <span class="text-slate-500">Funnel Limit:</span>
                            <strong class="font-bold text-indigo-600"><?= $funnelsLabel ?></strong>
                        </div>
                        <div class="flex items-center justify-between text-slate-700">
                            <span class="text-slate-500">Monthly Submissions:</span>
                            <span class="font-semibold"><?= ($limits['max_submissions_per_month'] ?? 100) <= 0 ? 'Unlimited' : number_format($limits['max_submissions_per_month'] ?? 100) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-700">
                            <span class="text-slate-500">Team Seats:</span>
                            <span class="font-semibold"><?= ($limits['max_team_members'] ?? 1) <= 0 ? 'Unlimited' : ($limits['max_team_members'] ?? 1) ?></span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">
                        <?= (int)$p['active_subscribers_count'] ?> active <?= (int)$p['active_subscribers_count'] === 1 ? 'tenant' : 'tenants' ?>
                    </span>
                    <button type="button" 
                            onclick='openEditPlanModal(<?= json_encode($p) ?>)' 
                            class="px-2.5 py-1 text-xs font-semibold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                        Edit Plan
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Detailed Plans Table -->
    <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-[#E2E8F0]">
            <h3 class="text-sm font-bold text-slate-900">All Subscription Tiers</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#E2E8F0] bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Plan Name & Slug</th>
                        <th class="py-3 px-4">Price</th>
                        <th class="py-3 px-4">Funnels Limit</th>
                        <th class="py-3 px-4">Submissions Limit</th>
                        <th class="py-3 px-4">Features</th>
                        <th class="py-3 px-4 text-center">Active Tenants</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0]">
                    <?php foreach ($plans as $p): 
                        $limits = json_decode($p['limits_json'] ?? '{}', true) ?: [];
                        $features = json_decode($p['features_json'] ?? '{}', true) ?: [];
                        $maxFunnels = $limits['max_funnels'] ?? 1;
                        $funnelsLabel = ($maxFunnels <= 0 || $maxFunnels == -1) ? 'Unlimited' : "{$maxFunnels} " . ($maxFunnels == 1 ? 'Funnel' : 'Funnels');
                    ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                <?= e($p['name']) ?>
                                <span class="block text-[10px] text-slate-400 font-mono">slug: <?= e($p['slug']) ?></span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                $<?= number_format((float)$p['price'], 2) ?>
                                <span class="text-[10px] font-normal text-slate-400">/<?= e($p['billing_interval']) ?></span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-indigo-600">
                                <?= $funnelsLabel ?>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                <?= ($limits['max_submissions_per_month'] ?? 100) <= 0 ? 'Unlimited' : number_format($limits['max_submissions_per_month'] ?? 100) . '/mo' ?>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    <?php if (!empty($features['custom_branding'])): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600">Branding</span>
                                    <?php endif; ?>
                                    <?php if (!empty($features['csv_export'])): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600">CSV Export</span>
                                    <?php endif; ?>
                                    <?php if (!empty($features['analytics'])): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600">Analytics</span>
                                    <?php endif; ?>
                                    <?php if (!empty($features['webhooks'])): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600">Webhooks</span>
                                    <?php endif; ?>
                                    <?php if (!empty($features['priority_support'])): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-purple-50 text-purple-700">Priority</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                <?= (int)$p['active_subscribers_count'] ?>
                            </td>
                            <td class="py-3.5 px-4">
                                <?php if ($p['is_active']): ?>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                <?php else: ?>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" 
                                            onclick='openEditPlanModal(<?= json_encode($p) ?>)' 
                                            class="px-2.5 py-1 text-xs font-semibold rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors">
                                        Edit
                                    </button>
                                    <form method="POST" action="<?= url("/admin/platform/plans/{$p['id']}/delete") ?>" class="inline" onsubmit="return confirm('Are you sure you want to remove or deactivate plan \'<?= e(addslashes($p['name'])) ?>\'?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Create New Plan -->
    <div id="createPlanModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-[#E2E8F0]">
            <div class="px-6 py-4 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900">Create New Subscription Plan</h3>
                <button type="button" onclick="closeCreatePlanModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="<?= url('/admin/platform/plans/create') ?>" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                <?= csrf_field() ?>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Plan Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Standard" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Slug</label>
                        <input type="text" name="slug" placeholder="e.g. standard (auto)" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Price ($) *</label>
                        <input type="number" step="0.01" name="price" value="0.00" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Billing Interval</label>
                        <select name="billing_interval" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>

                <!-- Limits Quota -->
                <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200 space-y-3">
                    <span class="block text-xs font-bold text-slate-800">Resource Quotas & Limits (-1 = Unlimited)</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">Funnels Limit</label>
                            <input type="number" name="max_funnels" value="1" required class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">Monthly Leads</label>
                            <input type="number" name="max_submissions_per_month" value="100" required class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">Team Members</label>
                            <input type="number" name="max_team_members" value="1" required class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 bg-white">
                        </div>
                    </div>
                </div>

                <!-- Features Checkboxes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Enabled Features</label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-2 text-slate-700">
                            <input type="checkbox" name="feature_custom_branding" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Custom Branding
                        </label>
                        <label class="flex items-center gap-2 text-slate-700">
                            <input type="checkbox" name="feature_csv_export" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            CSV Export
                        </label>
                        <label class="flex items-center gap-2 text-slate-700">
                            <input type="checkbox" name="feature_analytics" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Advanced Analytics
                        </label>
                        <label class="flex items-center gap-2 text-slate-700">
                            <input type="checkbox" name="feature_webhooks" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Webhooks / API
                        </label>
                        <label class="flex items-center gap-2 text-slate-700">
                            <input type="checkbox" name="feature_priority_support" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Priority Support
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief overview of plan target audience..." class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="create_is_active" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="create_is_active" class="text-xs font-medium text-slate-700">Make this plan immediately active</label>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeCreatePlanModal()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm transition-colors">
                        Save Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Existing Plan -->
    <div id="editPlanModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden border border-[#E2E8F0]">
            <div class="px-6 py-4 border-b border-[#E2E8F0] flex items-center justify-between bg-slate-50">
                <h3 class="text-sm font-bold text-slate-900">Edit Plan: <span id="editPlanModalTitle" class="text-indigo-600"></span></h3>
                <button type="button" onclick="closeEditPlanModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editPlanForm" method="POST" action="" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                <?= csrf_field() ?>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Plan Name *</label>
                        <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Slug (Identifier)</label>
                        <input type="text" id="edit_slug" readonly class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 bg-slate-100 text-slate-600 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Price ($) *</label>
                        <input type="number" step="0.01" name="price" id="edit_price" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Billing Interval</label>
                        <select name="billing_interval" id="edit_interval" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>

                <!-- Limits Quota -->
                <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200 space-y-3">
                    <span class="block text-xs font-bold text-slate-800">Resource Quotas & Limits (-1 = Unlimited)</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">Funnels Limit</label>
                            <input type="number" name="max_funnels" id="edit_max_funnels" required class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">Monthly Leads</label>
                            <input type="number" name="max_submissions_per_month" id="edit_max_submissions" required class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 mb-1">Team Members</label>
                            <input type="number" name="max_team_members" id="edit_max_team_members" required class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 bg-white">
                        </div>
                    </div>
                </div>

                <!-- Features Checkboxes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Enabled Features</label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-2 text-slate-700">
                            <input type="checkbox" name="feature_custom_branding" id="edit_feat_branding" value="1" class="rounded border-slate-300 text-indigo-600">
                            Custom Branding
                        </label>
                        <label class="flex items-center gap-2 text-slate-700">
                            <input type="checkbox" name="feature_csv_export" id="edit_feat_csv" value="1" class="rounded border-slate-300 text-indigo-600">
                            CSV Export
                        </label>
                        <label class="flex items-center gap-2 text-slate-700">
                            <input type="checkbox" name="feature_analytics" id="edit_feat_analytics" value="1" class="rounded border-slate-300 text-indigo-600">
                            Advanced Analytics
                        </label>
                        <label class="flex items-center gap-2 text-slate-700">
                            <input type="checkbox" name="feature_webhooks" id="edit_feat_webhooks" value="1" class="rounded border-slate-300 text-indigo-600">
                            Webhooks / API
                        </label>
                        <label class="flex items-center gap-2 text-slate-700">
                            <input type="checkbox" name="feature_priority_support" id="edit_feat_priority" value="1" class="rounded border-slate-300 text-indigo-600">
                            Priority Support
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                    <textarea name="description" id="edit_description" rows="2" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-slate-300 text-indigo-600">
                    <label for="edit_is_active" class="text-xs font-medium text-slate-700">Plan is Active for new subscriptions</label>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditPlanModal()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function openCreatePlanModal() {
    document.getElementById('createPlanModal').classList.remove('hidden');
}
function closeCreatePlanModal() {
    document.getElementById('createPlanModal').classList.add('hidden');
}

function openEditPlanModal(plan) {
    const modal = document.getElementById('editPlanModal');
    const form = document.getElementById('editPlanForm');
    form.action = '<?= url("/admin/platform/plans") ?>/' + plan.id + '/update';

    document.getElementById('editPlanModalTitle').textContent = plan.name;
    document.getElementById('edit_name').value = plan.name;
    document.getElementById('edit_slug').value = plan.slug;
    document.getElementById('edit_price').value = plan.price || '0.00';
    document.getElementById('edit_interval').value = plan.billing_interval || 'monthly';
    document.getElementById('edit_description').value = plan.description || '';
    document.getElementById('edit_is_active').checked = plan.is_active == 1;

    let limits = {};
    try {
        limits = typeof plan.limits_json === 'string' ? JSON.parse(plan.limits_json) : (plan.limits_json || {});
    } catch (e) {}

    document.getElementById('edit_max_funnels').value = limits.max_funnels !== undefined ? limits.max_funnels : 1;
    document.getElementById('edit_max_submissions').value = limits.max_submissions_per_month !== undefined ? limits.max_submissions_per_month : 100;
    document.getElementById('edit_max_team_members').value = limits.max_team_members !== undefined ? limits.max_team_members : 1;

    let features = {};
    try {
        features = typeof plan.features_json === 'string' ? JSON.parse(plan.features_json) : (plan.features_json || {});
    } catch (e) {}

    document.getElementById('edit_feat_branding').checked = !!features.custom_branding;
    document.getElementById('edit_feat_csv').checked = !!features.csv_export;
    document.getElementById('edit_feat_analytics').checked = !!features.analytics;
    document.getElementById('edit_feat_webhooks').checked = !!features.webhooks;
    document.getElementById('edit_feat_priority').checked = !!features.priority_support;

    modal.classList.remove('hidden');
}

function closeEditPlanModal() {
    document.getElementById('editPlanModal').classList.add('hidden');
}
</script>
