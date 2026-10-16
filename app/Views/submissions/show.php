<div class="space-y-6">

    <!-- Top Bar Navigation & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="<?= url('/admin/submissions') ?>" class="text-xs text-[#687080] hover:text-[#171A21]">← Submissions</a>
                <span class="text-xs text-[#687080]">/</span>
                <span class="text-xs font-semibold text-[#171A21]">Session #<?= (int)$detail['session']['id'] ?></span>
            </div>
            <h2 class="text-lg font-bold text-[#171A21]">Visitor Journey Inspector</h2>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($detail['session']['status'] === 'completed'): ?>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    ✓ Completed Journey
                </span>
            <?php else: ?>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <?= ucfirst(str_replace('_', ' ', $detail['session']['status'])) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Overview Meta Banner -->
    <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-sm grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-[#687080] block text-[11px] mb-0.5">Funnel</span>
            <span class="font-bold text-[#171A21]"><?= e($detail['session']['funnel_name']) ?></span>
            <span class="text-[10px] text-[#687080] block">Version <?= (int)$detail['session']['version_number'] ?></span>
        </div>
        <div>
            <span class="text-[#687080] block text-[11px] mb-0.5">Session Initialized</span>
            <span class="font-bold text-[#171A21]"><?= date('M d, Y H:i:s', strtotime($detail['session']['created_at'])) ?></span>
        </div>
        <div>
            <span class="text-[#687080] block text-[11px] mb-0.5">Journey Completed</span>
            <span class="font-bold text-[#171A21]">
                <?= $detail['session']['completed_at'] ? date('M d, Y H:i:s', strtotime($detail['session']['completed_at'])) : 'In Progress' ?>
            </span>
        </div>
        <div>
            <span class="text-[#687080] block text-[11px] mb-0.5">Final Destination</span>
            <span class="font-bold text-[#5B5BD6] truncate block">
                <?= e($detail['session']['destination_name'] ?: 'Pending completion') ?>
            </span>
            <?php if ($detail['session']['destination_url']): ?>
                <a href="<?= e($detail['session']['destination_url']) ?>" target="_blank" class="text-[10px] text-slate-400 hover:underline truncate block">
                    <?= e($detail['session']['destination_url']) ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content: Two Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Column 1: Captured Lead Profile & Answer Trail (7 cols) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Lead Form Card -->
            <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#E4E7EC]">
                    <h3 class="text-sm font-semibold text-[#171A21] flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#5B5BD6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Pre-Quiz Lead Profile
                    </h3>
                    <span class="text-[11px] text-[#687080]"><?= count($detail['form_values']) ?> fields captured</span>
                </div>

                <?php if (empty($detail['form_values'])): ?>
                    <p class="text-xs text-[#687080] italic">No pre-quiz form data for this submission.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <?php foreach ($detail['form_values'] as $fv): ?>
                            <div class="p-3 bg-[#F7F8FA] rounded-lg border border-[#E4E7EC]">
                                <span class="text-[11px] font-semibold text-[#687080] block mb-0.5">
                                    <?= e($fv['field_label_snapshot']) ?>
                                </span>
                                <span class="font-semibold text-[#171A21] break-words">
                                    <?php if ($fv['field_type_snapshot'] === 'consent'): ?>
                                        <?= $fv['value_text'] === '1' ? '✓ Consent Acknowledged' : 'Not Granted' ?>
                                    <?php else: ?>
                                        <?= e($fv['value_text'] ?: '—') ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Visited Questions & Selected Answers Trail -->
            <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#E4E7EC]">
                    <h3 class="text-sm font-semibold text-[#171A21] flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Sequential Question Answers Path
                    </h3>
                    <span class="text-[11px] text-[#687080]"><?= count($detail['answers']) ?> steps completed</span>
                </div>

                <?php if (empty($detail['answers'])): ?>
                    <p class="text-xs text-[#687080] italic">No question answers recorded.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($detail['answers'] as $idx => $ans): ?>
                            <div class="p-4 rounded-xl border border-[#E4E7EC] bg-white hover:border-[#5B5BD6] transition-colors text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-indigo-50 text-[#5B5BD6] font-bold text-[10px] flex items-center justify-center">
                                            <?= $idx + 1 ?>
                                        </span>
                                        <h4 class="font-bold text-[#171A21]"><?= e($ans['question_text_snapshot']) ?></h4>
                                    </div>
                                    <span class="text-[10px] text-[#687080]"><?= date('H:i:s', strtotime($ans['answered_at'])) ?></span>
                                </div>
                                <div class="pl-7">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-800 font-semibold border border-emerald-200">
                                        <span>Choice:</span>
                                        <span><?= e($ans['answer_value_text']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Column 2: Chronological Journey Event Timeline (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-[#171A21] mb-4 pb-3 border-b border-[#E4E7EC] flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Journey Event Timeline
                </h3>

                <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#E4E7EC]">
                    <?php foreach ($detail['events'] as $evt): ?>
                        <div class="relative text-xs">
                            <!-- Bullet node -->
                            <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-[#5B5BD6] ring-4 ring-white"></div>
                            <div>
                                <span class="font-bold text-[#171A21] capitalize">
                                    <?= str_replace('_', ' ', $evt['event_type']) ?>
                                </span>
                                <span class="text-[10px] text-[#687080] ml-2">
                                    <?= date('H:i:s', strtotime($evt['occurred_at'])) ?>
                                </span>

                                <?php if ($evt['node_title']): ?>
                                    <p class="text-[11px] text-[#687080] mt-0.5">
                                        Node: <strong class="text-[#171A21]"><?= e($evt['node_title']) ?></strong>
                                    </p>
                                <?php endif; ?>

                                <?php if ($evt['option_label']): ?>
                                    <p class="text-[11px] text-emerald-700 mt-0.5">
                                        Selected: <strong><?= e($evt['option_label']) ?></strong>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

</div>
