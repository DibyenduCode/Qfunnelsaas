<div class="space-y-6">

    <!-- Top KPI Metric Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Funnels -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 shadow-sm">
            <span class="text-xs font-medium text-[#687080] block mb-1">Total Funnels</span>
            <span class="text-2xl font-bold text-[#171A21]"><?= (int)$metrics['total_funnels'] ?></span>
            <span class="text-[11px] text-[#687080] block mt-1"><?= (int)$metrics['published_funnels'] ?> published</span>
        </div>

        <!-- Quiz Starts -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 shadow-sm">
            <span class="text-xs font-medium text-[#687080] block mb-1">Quiz Starts</span>
            <span class="text-2xl font-bold text-[#171A21]"><?= (int)$metrics['total_starts'] ?></span>
            <span class="text-[11px] text-emerald-600 block mt-1 font-medium">Session initialized</span>
        </div>

        <!-- Captured Leads -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 shadow-sm">
            <span class="text-xs font-medium text-[#687080] block mb-1">Captured Leads</span>
            <span class="text-2xl font-bold text-[#171A21]"><?= (int)$metrics['total_leads'] ?></span>
            <span class="text-[11px] text-[#687080] block mt-1">Pre-quiz submissions</span>
        </div>

        <!-- Completed Quizzes -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 shadow-sm">
            <span class="text-xs font-medium text-[#687080] block mb-1">Completions</span>
            <span class="text-2xl font-bold text-[#171A21]"><?= (int)$metrics['total_completions'] ?></span>
            <span class="text-[11px] text-[#687080] block mt-1">Reached destination</span>
        </div>

        <!-- Completion Rate -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 shadow-sm">
            <span class="text-xs font-medium text-[#687080] block mb-1">Completion Rate</span>
            <span class="text-2xl font-bold text-[#5B5BD6]"><?= $metrics['completion_rate'] ?>%</span>
            <span class="text-[11px] text-[#687080] block mt-1">Finished / Starts</span>
        </div>

        <!-- Plan Usage -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl p-4 shadow-sm">
            <span class="text-xs font-medium text-[#687080] block mb-1">Plan: <?= e($usage['plan_name']) ?></span>
            <div class="w-full bg-[#E4E7EC] h-2 rounded-full mt-2 overflow-hidden">
                <div class="bg-[#5B5BD6] h-full rounded-full" style="width: <?= $usage['funnels']['percentage'] ?>%"></div>
            </div>
            <span class="text-[11px] text-[#687080] block mt-1.5"><?= $usage['funnels']['used'] ?> / <?= $usage['funnels']['limit'] ?> funnels used</span>
        </div>
    </div>

    <!-- Middle Grid: Recent Funnels & Recent Submissions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Funnels Table -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#171A21]">Recent Funnels</h3>
                    <a href="<?= url('/admin/funnels') ?>" class="text-xs text-[#5B5BD6] font-medium hover:underline">View all funnels →</a>
                </div>

                <?php if (empty($metrics['recent_funnels'])): ?>
                    <div class="p-8 text-center">
                        <p class="text-sm text-[#687080] mb-4">No funnels created yet in this workspace.</p>
                        <a href="<?= url('/admin/funnels/create') ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#5B5BD6] text-white text-xs font-medium shadow-sm hover:bg-[#4B4BC0]">
                            Create your first funnel
                        </a>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-[#E4E7EC]">
                        <?php foreach ($metrics['recent_funnels'] as $f): ?>
                            <div class="p-4 flex items-center justify-between hover:bg-[#F7F8FA] transition-colors">
                                <div class="min-w-0 pr-4">
                                    <div class="flex items-center gap-2">
                                        <a href="<?= url("/admin/funnels/{$f['id']}/builder") ?>" class="text-sm font-semibold text-[#171A21] hover:text-[#5B5BD6] truncate">
                                            <?= e($f['name']) ?>
                                        </a>
                                        <?php if ($f['status'] === 'published'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Published</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Draft</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-[#687080] mt-0.5">
                                        /quiz/<?= e($f['slug']) ?> • <?= (int)$f['starts_count'] ?> starts • <?= (int)$f['completions_count'] ?> completed
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="<?= url("/admin/funnels/{$f['id']}/builder") ?>" class="px-2.5 py-1 text-xs font-medium border border-[#E4E7EC] rounded-lg hover:bg-white text-[#171A21]">
                                        Builder
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Submissions List -->
        <div class="bg-white border border-[#E4E7EC] rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-[#171A21]">Recent Leads & Submissions</h3>
                    <a href="<?= url('/admin/submissions') ?>" class="text-xs text-[#5B5BD6] font-medium hover:underline">View all submissions →</a>
                </div>

                <?php if (empty($metrics['recent_submissions'])): ?>
                    <div class="p-8 text-center">
                        <p class="text-sm text-[#687080]">No submissions recorded yet. Share your published funnel link to capture leads.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-[#E4E7EC]">
                        <?php foreach ($metrics['recent_submissions'] as $sub): ?>
                            <div class="p-4 flex items-center justify-between hover:bg-[#F7F8FA] transition-colors">
                                <div class="min-w-0 pr-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-[#171A21] truncate">
                                            <?= e($sub['lead_name'] ?: 'Anonymous Visitor') ?>
                                        </span>
                                        <span class="text-xs text-[#687080]">(<?= e($sub['lead_email'] ?: 'No email') ?>)</span>
                                    </div>
                                    <p class="text-xs text-[#687080] mt-0.5">
                                        Funnel: <?= e($sub['funnel_name']) ?> • <?= date('M d, Y H:i', strtotime($sub['created_at'])) ?>
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <?php if ($sub['status'] === 'completed'): ?>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200"><?= ucfirst($sub['status']) ?></span>
                                    <?php endif; ?>
                                    <a href="<?= url("/admin/submissions/{$sub['id']}") ?>" class="text-xs text-[#5B5BD6] font-semibold hover:underline">
                                        Inspect →
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>
