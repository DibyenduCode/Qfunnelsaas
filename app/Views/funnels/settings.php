<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="<?= url('/admin/funnels') ?>" class="text-xs text-[#687080] hover:text-[#171A21]">Funnels</a>
                <span class="text-xs text-[#687080]">/</span>
                <span class="text-xs font-semibold text-[#171A21]"><?= e($funnel['name']) ?></span>
            </div>
            <h2 class="text-lg font-bold text-[#171A21]">Funnel Settings</h2>
            <p class="text-xs text-[#687080]">Configure general parameters, visitor navigation, styling, and public access.</p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($funnel['status'] === 'published'): ?>
                <a href="<?= url("/quiz/{$funnel['slug']}") ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E4E7EC] bg-white text-[#171A21] text-xs font-semibold hover:bg-[#F7F8FA] transition-colors">
                    <svg class="w-3.5 h-3.5 text-[#687080]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View Live Quiz
                </a>
            <?php endif; ?>
            <a href="<?= url("/admin/funnels/{$funnel['id']}/builder") ?>" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#5B5BD6] text-white text-xs font-semibold hover:bg-[#4B4BC0] shadow-sm transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Open Visual Builder →
            </a>
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Settings Form (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            <form action="<?= url("/admin/funnels/{$funnel['id']}/settings") ?>" method="POST" class="space-y-6">
                <?= csrf_field() ?>

                <!-- General Configuration Card -->
                <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-sm space-y-4">
                    <div class="border-b border-[#E4E7EC] pb-3">
                        <h3 class="text-sm font-semibold text-[#171A21]">General Information</h3>
                        <p class="text-xs text-[#687080]">Basic identifiers and metadata for your funnel.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Funnel Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="<?= e($funnel['name']) ?>" required
                               class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] focus:ring-2 focus:ring-[#5B5BD6] focus:border-[#5B5BD6]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Description</label>
                        <textarea name="description" rows="3" placeholder="Briefly describe what this quiz is about..."
                                  class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] focus:ring-2 focus:ring-[#5B5BD6] focus:border-[#5B5BD6]"><?= e($funnel['description']) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Privacy Policy URL</label>
                        <input type="url" name="privacy_policy_url" value="<?= e($settings['privacy_policy_url'] ?? '') ?>" placeholder="https://yourdomain.com/privacy"
                               class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] focus:ring-2 focus:ring-[#5B5BD6] focus:border-[#5B5BD6]">
                        <p class="text-[11px] text-[#687080] mt-1">Displayed in the footer of your quiz and consent prompts.</p>
                    </div>
                </div>

                <!-- Visitor Experience & Navigation Card -->
                <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-sm space-y-4">
                    <div class="border-b border-[#E4E7EC] pb-3">
                        <h3 class="text-sm font-semibold text-[#171A21]">Visitor Experience & Navigation</h3>
                        <p class="text-xs text-[#687080]">Control how respondents navigate through quiz steps.</p>
                    </div>

                    <div class="space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border border-[#E4E7EC] hover:bg-[#F7F8FA] transition-colors">
                            <input type="checkbox" name="enable_back_button" value="1" <?= !empty($settings['enable_back_button']) ? 'checked' : '' ?> class="mt-0.5 rounded text-[#5B5BD6] focus:ring-[#5B5BD6]">
                            <div>
                                <span class="text-xs font-semibold text-[#171A21] block">Enable Back Navigation</span>
                                <span class="text-[11px] text-[#687080]">Allows visitors to go back and revisit previous questions or modify answers.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border border-[#E4E7EC] hover:bg-[#F7F8FA] transition-colors">
                            <input type="checkbox" name="show_progress_bar" value="1" <?= !empty($settings['show_progress_bar']) ? 'checked' : '' ?> class="mt-0.5 rounded text-[#5B5BD6] focus:ring-[#5B5BD6]">
                            <div>
                                <span class="text-xs font-semibold text-[#171A21] block">Show Progress Bar</span>
                                <span class="text-[11px] text-[#687080]">Displays a clean progress bar at the top of the quiz during visitor completion.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border border-[#E4E7EC] hover:bg-[#F7F8FA] transition-colors">
                            <input type="checkbox" name="auto_redirect" value="1" <?= !empty($settings['auto_redirect']) ? 'checked' : '' ?> class="mt-0.5 rounded text-[#5B5BD6] focus:ring-[#5B5BD6]">
                            <div>
                                <span class="text-xs font-semibold text-[#171A21] block">Auto-Redirect on Completion</span>
                                <span class="text-[11px] text-[#687080]">Automatically navigates the respondent to the final destination URL when reaching the outcome node.</span>
                            </div>
                        </label>
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Redirect Delay (Seconds)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="redirect_delay_seconds" min="0" max="15" value="<?= (int)($settings['redirect_delay_seconds'] ?? 3) ?>"
                                   class="w-32 px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] focus:ring-2 focus:ring-[#5B5BD6]">
                            <span class="text-xs text-[#687080]">seconds before redirecting to external destination</span>
                        </div>
                    </div>
                </div>

                <!-- Theme & White-Label Branding Card -->
                <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-sm space-y-4">
                    <div class="border-b border-[#E4E7EC] pb-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-[#171A21]">Branding & White-Labeling</h3>
                                <p class="text-xs text-[#687080]">Transform the public quiz into a fully branded, high-converting marketing asset.</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#5B5BD6]/10 text-[#5B5BD6] uppercase tracking-wide">Agency Grade</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Company / Brand Name</label>
                            <input type="text" name="company_name" value="<?= e($settings['company_name'] ?? '') ?>" placeholder="e.g. Apex Health Clinic"
                                   class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] focus:ring-2 focus:ring-[#5B5BD6]">
                            <p class="text-[11px] text-[#687080] mt-1">Displayed in the header and trust badges.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Company Logo URL</label>
                            <input type="url" name="logo_url" value="<?= e($settings['logo_url'] ?? '') ?>" placeholder="https://example.com/logo.png"
                                   class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] focus:ring-2 focus:ring-[#5B5BD6]">
                            <p class="text-[11px] text-[#687080] mt-1">Replaces the default icon in the top navigation bar.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Accent Brand Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="accent_color" id="accentColorPicker" value="<?= e($settings['accent_color'] ?? '#5B5BD6') ?>"
                                       oninput="document.getElementById('accentColorHex').value = this.value"
                                       class="h-9 w-12 rounded border border-[#E4E7EC] p-0.5 cursor-pointer bg-white">
                                <input type="text" id="accentColorHex" value="<?= e($settings['accent_color'] ?? '#5B5BD6') ?>"
                                       oninput="document.getElementById('accentColorPicker').value = this.value"
                                       class="w-28 px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs font-mono text-[#171A21]">
                            </div>
                            <p class="text-[11px] text-[#687080] mt-1">Powers buttons, active highlights, and progress bars.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Color Theme</label>
                            <select name="theme" class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] bg-white focus:ring-2 focus:ring-[#5B5BD6]">
                                <option value="light" <?= ($settings['theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>Light Clean (High Contrast)</option>
                                <option value="dark" <?= ($settings['theme'] ?? '') === 'dark' ? 'selected' : '' ?>>Dark Modern (Sleek Charcoal)</option>
                            </select>
                            <p class="text-[11px] text-[#687080] mt-1">Base page styling for your respondents.</p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[#E4E7EC] space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border border-[#E4E7EC] hover:bg-[#F7F8FA] transition-colors">
                            <input type="checkbox" name="white_label" value="1" <?= !empty($settings['white_label']) ? 'checked' : '' ?> class="mt-0.5 rounded text-[#5B5BD6] focus:ring-[#5B5BD6]">
                            <div>
                                <span class="text-xs font-semibold text-[#171A21] block">Enable Complete White-Labeling</span>
                                <span class="text-[11px] text-[#687080]">Removes all "Powered by QFunnel" badges and platform branding from the public quiz runner.</span>
                            </div>
                        </label>

                        <div>
                            <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Custom Social Proof / Trust Badge</label>
                            <input type="text" name="trust_badge_text" value="<?= e($settings['trust_badge_text'] ?? '🔒 256-Bit SSL Encrypted • 100% Privacy Guaranteed') ?>" placeholder="e.g. 🔒 256-Bit SSL Encrypted • 100% Privacy Guaranteed"
                                   class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] focus:ring-2 focus:ring-[#5B5BD6]">
                            <p class="text-[11px] text-[#687080] mt-1">Displayed directly under the submit buttons to maximize conversion rate.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Custom Footer Copyright / Notice</label>
                                <input type="text" name="footer_text" value="<?= e($settings['footer_text'] ?? '') ?>" placeholder="e.g. © 2026 Apex Health Clinic. All rights reserved."
                                       class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] focus:ring-2 focus:ring-[#5B5BD6]">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Terms of Service URL</label>
                                <input type="url" name="terms_url" value="<?= e($settings['terms_url'] ?? '') ?>" placeholder="https://example.com/terms"
                                       class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs text-[#171A21] focus:ring-2 focus:ring-[#5B5BD6]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#171A21] text-white text-xs font-semibold hover:bg-black shadow-sm transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Column: Flow Builder Integration & Info (4 cols) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Flow Builder Form Nodes Highlight Card -->
            <div class="bg-gradient-to-br from-[#F5F5FE] to-[#EEF0FD] border border-[#DCDDF9] rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-[#5B5BD6]/10 text-[#5B5BD6] border border-[#5B5BD6]/20">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Visual Flow Builder
                    </span>
                </div>
                <h3 class="text-sm font-bold text-[#171A21]">Lead Capture via Form Nodes</h3>
                <p class="text-xs text-[#525866] leading-relaxed">
                    Lead capture is designed directly on the visual canvas. You can drag and position <strong>Form nodes</strong> anywhere along the user journey:
                </p>
                <ul class="text-xs text-[#525866] space-y-1.5 pl-3 list-disc">
                    <li>Place at the beginning (Pre-Quiz capture)</li>
                    <li>Place before outcomes to gated results</li>
                    <li>Preset fields: Name, Email, Phone, Company, Consent</li>
                    <li>Custom inputs, placeholders & required validation</li>
                </ul>
                <div class="pt-2">
                    <a href="<?= url("/admin/funnels/{$funnel['id']}/builder") ?>" class="w-full inline-flex items-center justify-center gap-2 py-2 px-3 rounded-lg bg-[#5B5BD6] text-white text-xs font-semibold hover:bg-[#4B4BC0] transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                        Open Flow Builder
                    </a>
                </div>
            </div>

            <!-- Public Access & Live Link Card -->
            <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-sm space-y-4">
                <div class="border-b border-[#E4E7EC] pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-[#171A21]">Publication Status</h3>
                        <p class="text-xs text-[#687080]">Public access for respondents.</p>
                    </div>
                    <?php if ($funnel['status'] === 'published'): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ● Published (v<?= (int)$funnel['current_published_version_id'] ?>)
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            ● Draft Mode
                        </span>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-[#687080] uppercase mb-1">Public Quiz URL</label>
                    <div class="flex items-center gap-1.5">
                        <input type="text" readonly value="<?= url("/quiz/{$funnel['slug']}") ?>" id="publicQuizUrl"
                               class="w-full px-2.5 py-1.5 rounded-lg border border-[#E4E7EC] bg-[#F7F8FA] text-xs font-mono text-[#171A21]">
                        <button type="button" onclick="copyQuizUrl()" title="Copy Link"
                                class="px-2.5 py-1.5 rounded-lg border border-[#E4E7EC] hover:bg-slate-50 text-[#171A21] text-xs font-medium shrink-0 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Copy
                        </button>
                    </div>
                    <span id="copyToast" class="text-[11px] text-emerald-600 font-medium hidden mt-1 block">Link copied to clipboard!</span>
                </div>

                <?php if ($funnel['status'] === 'published'): ?>
                    <a href="<?= url("/quiz/{$funnel['slug']}") ?>" target="_blank"
                       class="w-full inline-flex items-center justify-center gap-1.5 py-2 rounded-lg border border-[#E4E7EC] text-xs font-semibold text-[#171A21] hover:bg-[#F7F8FA] transition-colors">
                        Launch Live Quiz in New Tab ↗
                    </a>
                <?php else: ?>
                    <p class="text-[11px] text-[#687080]">Publish this funnel inside the Visual Builder to generate version 1 and start collecting submissions.</p>
                <?php endif; ?>
            </div>

            <!-- Danger Zone / Quick Actions Card -->
            <div class="bg-white border border-[#E4E7EC] rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="text-xs font-semibold text-[#687080] uppercase tracking-wider">Quick Actions</h3>
                
                <div class="space-y-2 pt-1">
                    <form method="POST" action="<?= url("/admin/funnels/{$funnel['id']}/duplicate") ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="w-full py-2 px-3 rounded-lg border border-[#E4E7EC] hover:bg-[#F7F8FA] text-xs font-semibold text-[#171A21] flex items-center justify-center gap-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5 text-[#687080]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                            Duplicate Funnel
                        </button>
                    </form>

                    <form method="POST" action="<?= url("/admin/funnels/{$funnel['id']}/delete") ?>" onsubmit="return confirm('Are you sure you want to delete this funnel? This action cannot be undone.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="w-full py-2 px-3 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-xs font-semibold text-rose-700 flex items-center justify-center gap-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Delete Funnel
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
    function copyQuizUrl() {
        const fullUrl = window.location.origin + '<?= url("/quiz/{$funnel['slug']}") ?>';
        navigator.clipboard.writeText(fullUrl).then(() => {
            const toast = document.getElementById('copyToast');
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 2500);
        });
    }
</script>
