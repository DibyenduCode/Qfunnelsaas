<div class="space-y-4">

    <!-- Filters & Export Header -->
    <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="<?= url('/admin/submissions') ?>" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative w-full md:w-60">
                <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search lead name/email..."
                       class="w-full pl-9 pr-3.5 py-1.5 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] placeholder-[#687080] focus:ring-2 focus:ring-[#5B5BD6]">
                <svg class="w-4 h-4 text-[#687080] absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <select name="funnel_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] bg-white">
                <option value="">All Funnels</option>
                <?php foreach ($funnels as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= ($filters['funnel_id'] ?? '') == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] bg-white">
                <option value="">All Statuses</option>
                <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="form_pending" <?= ($filters['status'] ?? '') === 'form_pending' ? 'selected' : '' ?>>Form Pending</option>
            </select>

            <?php if (!empty($filters['search']) || !empty($filters['funnel_id']) || !empty($filters['status'])): ?>
                <a href="<?= url('/admin/submissions') ?>" class="text-xs text-[#687080] hover:text-[#171A21] underline">Clear</a>
            <?php endif; ?>
        </form>

        <a href="<?= url('/admin/submissions/export' . (!empty($filters['funnel_id']) ? '?funnel_id=' . $filters['funnel_id'] : '')) ?>" 
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg border border-[#E4E7EC] bg-white text-xs font-semibold text-[#171A21] hover:bg-[#F7F8FA] shadow-sm">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </div>

    <!-- Submissions Table -->
    <div class="bg-white border border-[#E4E7EC] rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($submissions['items'])): ?>
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-[#F1F3F6] text-[#687080] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-sm font-semibold text-[#171A21] mb-1">No submissions found</h3>
                <p class="text-xs text-[#687080]">Submissions and lead journeys will appear here when visitors take your quiz.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#E4E7EC] bg-[#F7F8FA] text-[11px] font-semibold text-[#687080] uppercase tracking-wider">
                            <th class="py-3 px-4">Visitor / Lead</th>
                            <th class="py-3 px-4">Funnel</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Destination Reached</th>
                            <th class="py-3 px-4">Date & Time</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E4E7EC] text-xs">
                        <?php foreach ($submissions['items'] as $sub): ?>
                            <tr class="hover:bg-[#F7F8FA] transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-[#171A21]">
                                    <?= e($sub['lead_name'] ?: 'Visitor #' . $sub['id']) ?>
                                    <span class="block text-[11px] font-normal text-[#687080]"><?= e($sub['lead_email'] ?: 'No email submitted') ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-[#171A21] font-medium"><?= e($sub['funnel_name']) ?></td>
                                <td class="py-3.5 px-4">
                                    <?php if ($sub['status'] === 'completed'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>
                                    <?php elseif ($sub['status'] === 'in_progress'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">In Progress</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200"><?= ucfirst(str_replace('_', ' ', $sub['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-[#687080] truncate max-w-xs">
                                    <?= e($sub['destination_name'] ?: '—') ?>
                                </td>
                                <td class="py-3.5 px-4 text-[#687080]">
                                    <?= date('M d, Y H:i', strtotime($sub['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="<?= url("/admin/submissions/{$sub['id']}") ?>" 
                                       class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-[#E4E7EC] hover:bg-white text-xs font-semibold text-[#5B5BD6]">
                                        Inspect Journey →
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php
            $pagination = $submissions;
            $queryParams = array_filter([
                'funnel_id' => $filters['funnel_id'] ?? '',
                'status' => $filters['status'] ?? '',
                'search' => $filters['search'] ?? '',
            ]);
            require __DIR__ . '/../partials/pagination.php';
            ?>
        <?php endif; ?>
    </div>

</div>
