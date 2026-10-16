<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Quiz Funnel') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php
        $accentColor = !empty($settings['accent_color']) ? $settings['accent_color'] : '#5B5BD6';
        $isDark = ($settings['theme'] ?? 'light') === 'dark';
        $companyName = !empty($settings['company_name']) ? $settings['company_name'] : ($funnel['tenant_name'] ?? $funnel['name']);
        $logoUrl = !empty($settings['logo_url']) ? $settings['logo_url'] : '';
        $isWhiteLabel = !empty($settings['white_label']);
        $trustBadge = !empty($settings['trust_badge_text']) ? $settings['trust_badge_text'] : '🔒 256-Bit SSL Encrypted • 100% Privacy Guaranteed';
        $footerText = !empty($settings['footer_text']) ? $settings['footer_text'] : ('© ' . date('Y') . ' ' . $companyName . '. All rights reserved.');
        $privacyUrl = !empty($settings['privacy_policy_url']) ? $settings['privacy_policy_url'] : '';
        $termsUrl = !empty($settings['terms_url']) ? $settings['terms_url'] : '';
        $showProgressBar = !isset($settings['show_progress_bar']) || !empty($settings['show_progress_bar']);
    ?>
    <style>
        :root {
            --brand-primary: <?= e($accentColor) ?>;
            --brand-primary-hover: <?= e($accentColor) ?>ee;
            --brand-primary-light: <?= e($accentColor) ?>18;
            --brand-primary-glow: <?= e($accentColor) ?>33;
        }
        body { 
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; 
            <?= $isDark ? 'background: #0D0F14; color: #F1F3F9;' : 'background: #F8FAFC; color: #0F172A;' ?>
        }
        .brand-bg { background-color: var(--brand-primary); }
        .brand-text { color: var(--brand-primary); }
        .brand-border { border-color: var(--brand-primary); }
        
        .quiz-card {
            box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .option-card {
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .option-card:hover {
            border-color: var(--brand-primary);
            background-color: var(--brand-primary-light);
            transform: translateY(-1px);
        }
        .option-card.selected {
            border-color: var(--brand-primary);
            background-color: var(--brand-primary-light);
            box-shadow: 0 0 0 1.5px var(--brand-primary), 0 4px 12px var(--brand-primary-glow);
        }
        .option-key-badge {
            transition: all 0.15s ease;
        }
        .option-card.selected .option-key-badge {
            background-color: var(--brand-primary);
            color: #ffffff;
            border-color: var(--brand-primary);
        }
        .custom-focus:focus {
            outline: none;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px var(--brand-primary-glow);
        }
        .btn-brand {
            background-color: var(--brand-primary);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-brand:hover {
            opacity: 0.94;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px var(--brand-primary-glow);
        }
        .btn-brand:active {
            transform: translateY(0);
        }
        .progress-bar-fill {
            transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-primary-hover));
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between p-4 sm:p-6 <?= $isDark ? 'bg-[#0D0F14] text-[#F1F3F9]' : 'bg-[#F8FAFC] text-[#0F172A]' ?>">

    <!-- Top Sticky / Elevated Header: 100% White-Labeled -->
    <header class="max-w-2xl mx-auto w-full py-4 px-2 sm:px-0">
        <div class="flex items-center justify-between gap-4">
            <!-- Brand Logo & Name -->
            <div class="flex items-center gap-3">
                <?php if (!empty($logoUrl)): ?>
                    <img src="<?= e($logoUrl) ?>" alt="<?= e($companyName) ?>" class="h-8 max-w-[150px] object-contain rounded" onerror="this.style.display='none'; document.getElementById('brandFallbackAvatar').style.display='flex';">
                    <span id="brandFallbackAvatar" class="w-8 h-8 rounded-lg brand-bg text-white font-extrabold text-sm flex items-center justify-center shadow-sm" style="display:none;">
                        <?= e(strtoupper(substr($companyName, 0, 2))) ?>
                    </span>
                <?php else: ?>
                    <span class="w-8 h-8 rounded-lg brand-bg text-white font-extrabold text-sm flex items-center justify-center shadow-sm tracking-wide">
                        <?= e(strtoupper(substr($companyName, 0, 2))) ?>
                    </span>
                <?php endif; ?>

                <div class="leading-tight">
                    <span class="text-xs font-bold uppercase tracking-wider <?= $isDark ? 'text-slate-400' : 'text-slate-500' ?> block">
                        <?= e($companyName) ?>
                    </span>
                    <h1 class="text-sm font-extrabold <?= $isDark ? 'text-white' : 'text-[#0F172A]' ?>">
                        <?= e($funnel['name']) ?>
                    </h1>
                </div>
            </div>

            <!-- Estimated Time / Security Badge -->
            <div class="flex items-center gap-2">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold <?= $isDark ? 'bg-slate-800 text-slate-300 border border-slate-700' : 'bg-white text-slate-600 border border-slate-200' ?> shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Verified Secure
                </span>
                <span id="stepProgressBadge" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold brand-bg text-white shadow-xs hidden">
                    Step <span id="currentStepNum">1</span>
                </span>
            </div>
        </div>

        <?php if ($showProgressBar): ?>
            <!-- Dynamic Funnel Progress Bar -->
            <div class="mt-3.5 w-full bg-slate-200/60 <?= $isDark ? 'bg-slate-800' : '' ?> h-1.5 rounded-full overflow-hidden">
                <div id="funnelProgressBar" class="progress-bar-fill h-full rounded-full" style="width: 15%;"></div>
            </div>
        <?php endif; ?>
    </header>

    <!-- Main Dynamic Container -->
    <main class="max-w-xl mx-auto w-full my-auto py-4 sm:py-6">
        <div id="quizAppCard" class="quiz-card <?= $isDark ? 'bg-[#151921] border-[#252B36]' : 'bg-white border-[#E2E8F0]' ?> border rounded-2xl p-6 sm:p-9">
            
            <!-- Loading state initially -->
            <div id="loadingState" class="text-center py-16">
                <div class="w-9 h-9 border-3 border-t-transparent rounded-full animate-spin mx-auto mb-3.5" style="border-color: var(--brand-primary); border-top-color: transparent;"></div>
                <p class="text-xs font-medium <?= $isDark ? 'text-slate-400' : 'text-slate-500' ?>">Preparing your tailored experience...</p>
            </div>

            <!-- Form Container -->
            <div id="formSection" class="hidden space-y-6">
                <div class="text-left">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wide brand-bg text-white mb-2">
                        Get Started
                    </span>
                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight <?= $isDark ? 'text-white' : 'text-[#0F172A]' ?>" id="formHeading">Before we begin</h2>
                    <p class="text-xs sm:text-sm <?= $isDark ? 'text-slate-400' : 'text-slate-500' ?> mt-1.5" id="formSubheading">Please provide your details so we can tailor the best recommendations for you.</p>
                </div>

                <form id="preQuizForm" onsubmit="handleFormSubmit(event)" class="space-y-4 pt-1">
                    <div id="formFieldsContainer" class="space-y-4"></div>
                    <div id="formErrorNotice" class="text-xs text-rose-500 hidden font-semibold p-2.5 rounded-lg bg-rose-50 border border-rose-200"></div>
                    
                    <div class="pt-3 flex flex-col gap-3">
                        <div class="flex items-center gap-3">
                            <button type="button" id="formBackBtn" onclick="handleBackNavigation()" class="px-4 py-3 rounded-xl border <?= $isDark ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-200 text-slate-700 hover:bg-slate-50' ?> text-xs font-bold hidden transition-colors">
                                ← Back
                            </button>
                            <button type="submit" id="formSubmitBtn" class="w-full py-3.5 rounded-xl btn-brand text-white text-sm font-bold shadow-md">
                                Continue to Questions →
                            </button>
                        </div>

                        <!-- Micro Trust Seal directly under submit -->
                        <div class="flex items-center justify-center gap-2 text-[11px] <?= $isDark ? 'text-slate-400' : 'text-slate-500' ?> pt-1">
                            <span><?= e($trustBadge) ?></span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Question Step Container -->
            <div id="questionSection" class="hidden space-y-6">
                <div>
                    <div class="flex items-center justify-between">
                        <span id="questionStepBadge" class="text-[11px] font-extrabold uppercase tracking-wider brand-text">Question</span>
                        <span class="text-[11px] font-medium <?= $isDark ? 'text-slate-500' : 'text-slate-400' ?>">Select one option</span>
                    </div>
                    <h2 id="questionText" class="text-xl sm:text-2xl font-extrabold <?= $isDark ? 'text-white' : 'text-[#0F172A]' ?> mt-1 tracking-tight leading-snug"></h2>
                    <p id="questionHelp" class="text-xs <?= $isDark ? 'text-slate-400' : 'text-slate-500' ?> mt-1.5 hidden"></p>
                </div>

                <div id="optionsContainer" class="space-y-3"></div>

                <div id="answerErrorNotice" class="text-xs text-rose-500 hidden font-semibold p-2.5 rounded-lg bg-rose-50 border border-rose-200"></div>

                <div class="pt-5 border-t <?= $isDark ? 'border-slate-800' : 'border-slate-100' ?> flex items-center justify-between">
                    <button type="button" id="backBtn" onclick="handleBackNavigation()" class="px-4 py-2.5 rounded-xl border <?= $isDark ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-200 text-slate-700 hover:bg-slate-50' ?> text-xs font-bold hidden transition-colors">
                        ← Back
                    </button>
                    <button type="button" id="continueBtn" onclick="handleAnswerSubmit()" class="ml-auto px-7 py-3 rounded-xl btn-brand text-white text-xs font-bold shadow-md">
                        Continue →
                    </button>
                </div>
            </div>

            <!-- Result / Destination Container -->
            <div id="resultSection" class="hidden space-y-6 text-center py-2">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 font-extrabold text-2xl flex items-center justify-center mx-auto border border-emerald-200 shadow-sm animate-bounce">
                    ✓
                </div>
                <div>
                    <h2 id="resultHeading" class="text-2xl font-extrabold tracking-tight <?= $isDark ? 'text-white' : 'text-[#0F172A]' ?>">Quiz Completed!</h2>
                    <p id="resultSubheading" class="text-xs sm:text-sm <?= $isDark ? 'text-slate-400' : 'text-slate-500' ?> mt-1.5">Thank you. Based on your personalized answers, here is your recommendation:</p>
                </div>

                <div id="resultDestCard" class="p-5 <?= $isDark ? 'bg-slate-900 border-slate-800' : 'bg-slate-50 border-slate-200' ?> border rounded-xl text-left space-y-3 shadow-2xs">
                    <div class="flex items-center justify-between pb-2.5 border-b <?= $isDark ? 'border-slate-800' : 'border-slate-200' ?>">
                        <span class="text-xs font-bold uppercase tracking-wider <?= $isDark ? 'text-slate-400' : 'text-slate-500' ?>">Recommended Outcome:</span>
                        <span id="resultDestName" class="text-xs font-extrabold brand-text"></span>
                    </div>

                    <div id="resultAnswersSummary" class="space-y-2 text-xs"></div>
                </div>

                <div id="redirectNotice" class="text-xs font-medium <?= $isDark ? 'text-slate-400' : 'text-slate-500' ?> hidden">
                    Redirecting you in <strong id="countdownSeconds" class="font-bold brand-text">3</strong> seconds...
                </div>

                <a id="destinationCtaBtn" href="#" class="inline-flex items-center justify-center w-full py-4 rounded-xl btn-brand text-white text-sm font-extrabold shadow-md">
                    Continue to Your Results →
                </a>
            </div>

        </div>
    </main>

    <!-- 100% White-Labeled Marketing Footer -->
    <footer class="max-w-xl mx-auto w-full py-6 text-center text-xs <?= $isDark ? 'text-slate-500' : 'text-slate-400' ?> space-y-2">
        <div class="flex items-center justify-center gap-4 text-[11px] font-medium">
            <?php if (!empty($privacyUrl)): ?>
                <a href="<?= e($privacyUrl) ?>" target="_blank" class="hover:underline">Privacy Policy</a>
                <span>•</span>
            <?php endif; ?>
            <?php if (!empty($termsUrl)): ?>
                <a href="<?= e($termsUrl) ?>" target="_blank" class="hover:underline">Terms of Service</a>
                <span>•</span>
            <?php endif; ?>
            <span><?= e($footerText) ?></span>
        </div>

        <?php if (!$isWhiteLabel): ?>
            <div class="text-[10px] text-slate-400 pt-1">
                Powered by <span class="font-semibold text-slate-600 <?= $isDark ? 'text-slate-400' : '' ?>">QFunnel Platform</span>
            </div>
        <?php endif; ?>
    </footer>

    <!-- Quiz Engine Runner Client Script -->
    <script>
        const SLUG = '<?= e($slug) ?>';
        const API_BASE = '<?= url('') ?>';
        let sessionToken = null;
        let currentStepData = null;
        let selectedOptionId = null;
        let currentStepNumber = 1;
        const totalEstimatedSteps = 4;

        // Initialize on page load
        window.addEventListener('DOMContentLoaded', async () => {
            try {
                const resp = await fetch(`${API_BASE}/api/quiz/${SLUG}/init`, {
                    method: 'POST'
                });
                const data = await resp.json();
                if (data.success) {
                    sessionToken = data.data.session_token;
                    if (data.data.has_form) {
                        updateProgressBar(1, 4);
                        showForm(data.data.form_fields);
                    } else {
                        loadCurrentStep();
                    }
                } else {
                    showError(data.error || 'Failed to initialize session.');
                }
            } catch (e) {
                showError('Network error starting quiz. Please refresh.');
            }
        });

        function updateProgressBar(current, total) {
            const bar = document.getElementById('funnelProgressBar');
            if (!bar) return;
            const pct = Math.min(100, Math.max(15, Math.round((current / (total || 4)) * 100)));
            bar.style.width = `${pct}%`;
        }

        function showForm(fields, formConfig = null) {
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('questionSection').classList.add('hidden');
            document.getElementById('resultSection').classList.add('hidden');
            document.getElementById('formSection').classList.remove('hidden');

            const heading = document.getElementById('formHeading');
            const subheading = document.getElementById('formSubheading');
            const submitBtn = document.getElementById('formSubmitBtn');
            const backBtn = document.getElementById('formBackBtn');

            if (formConfig) {
                heading.textContent = formConfig.title || 'Please Provide Your Details';
                subheading.textContent = 'Please fill out the form to proceed.';
                submitBtn.textContent = (formConfig.button_text || 'Submit Details') + ' →';
                if (formConfig.can_go_back) {
                    backBtn.classList.remove('hidden');
                } else {
                    backBtn.classList.add('hidden');
                }
            } else {
                heading.textContent = 'Before we begin';
                subheading.textContent = 'Please provide your details so we can tailor the best recommendations for you.';
                submitBtn.textContent = 'Continue to Questions →';
                backBtn.classList.add('hidden');
            }

            const container = document.getElementById('formFieldsContainer');
            container.innerHTML = '';

            fields.forEach(f => {
                const div = document.createElement('div');
                div.className = 'space-y-1.5 text-xs text-left';

                if (f.field_type === 'consent') {
                    div.innerHTML = `
                        <label class="flex items-start gap-3 cursor-pointer pt-2">
                            <input type="checkbox" name="${f.field_key}" value="1" ${f.is_required ? 'required' : ''} class="mt-0.5 rounded text-[var(--brand-primary)]">
                            <span class="text-xs leading-relaxed <?= $isDark ? 'text-slate-400' : 'text-slate-600' ?>">${escapeHtml(f.label)}${f.is_required ? ' *' : ''}</span>
                        </label>
                    `;
                } else if (f.field_type === 'dropdown') {
                    div.innerHTML = `
                        <label class="block font-bold <?= $isDark ? 'text-slate-200' : 'text-slate-800' ?>">${escapeHtml(f.label)}${f.is_required ? ' *' : ''}</label>
                        <select name="${f.field_key}" ${f.is_required ? 'required' : ''} class="w-full px-3.5 py-3 rounded-xl border <?= $isDark ? 'bg-slate-900 border-slate-700 text-white' : 'bg-white border-slate-200 text-slate-800' ?> text-sm custom-focus">
                            <option value="">${escapeHtml(f.placeholder || 'Select an option')}</option>
                            ${(f.options || []).map(opt => `<option value="${escapeHtml(opt.value || opt.label)}">${escapeHtml(opt.label)}</option>`).join('')}
                        </select>
                    `;
                } else {
                    const inputType = f.field_type === 'email' ? 'email' : (f.field_type === 'number' ? 'number' : 'text');
                    div.innerHTML = `
                        <label class="block font-bold <?= $isDark ? 'text-slate-200' : 'text-slate-800' ?>">${escapeHtml(f.label)}${f.is_required ? ' *' : ''}</label>
                        <input type="${inputType}" name="${f.field_key}" placeholder="${escapeHtml(f.placeholder || '')}" ${f.is_required ? 'required' : ''}
                               class="w-full px-3.5 py-3 rounded-xl border <?= $isDark ? 'bg-slate-900 border-slate-700 text-white' : 'bg-white border-slate-200 text-slate-800' ?> text-sm custom-focus">
                    `;
                }

                container.appendChild(div);
            });
        }

        async function handleFormSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('formSubmitBtn');
            const errBox = document.getElementById('formErrorNotice');
            btn.disabled = true;
            btn.textContent = 'Saving...';
            errBox.classList.add('hidden');

            const form = document.getElementById('preQuizForm');
            const formData = new FormData(form);
            const payload = {};
            formData.forEach((value, key) => { payload[key] = value; });

            try {
                let resp;
                if (currentStepData && currentStepData.node_type === 'form') {
                    resp = await fetch(`${API_BASE}/api/quiz/answer`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Quiz-Session': sessionToken
                        },
                        body: JSON.stringify({
                            node_id: currentStepData.node_id,
                            answer: { form_data: payload }
                        })
                    });
                } else {
                    resp = await fetch(`${API_BASE}/api/quiz/form`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Quiz-Session': sessionToken
                        },
                        body: JSON.stringify({ form_data: payload })
                    });
                }

                const data = await resp.json();
                if (data.success) {
                    document.getElementById('formSection').classList.add('hidden');
                    renderStep(data.data);
                } else {
                    errBox.textContent = data.error || 'Please fill in all required fields.';
                    errBox.classList.remove('hidden');
                }
            } catch (err) {
                errBox.textContent = 'Network error saving form.';
                errBox.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = currentStepData?.button_text ? (currentStepData.button_text + ' →') : 'Continue →';
            }
        }

        async function loadCurrentStep() {
            try {
                const resp = await fetch(`${API_BASE}/api/quiz/step`, {
                    headers: { 'X-Quiz-Session': sessionToken }
                });
                const data = await resp.json();
                if (data.success) {
                    renderStep(data.data);
                }
            } catch (e) {
                showError('Failed to load step.');
            }
        }

        function renderStep(stepResponse) {
            document.getElementById('loadingState').classList.add('hidden');

            if (stepResponse.status === 'completed') {
                updateProgressBar(4, 4);
                renderResult(stepResponse.result);
                return;
            }

            if (stepResponse.status === 'form_step') {
                document.getElementById('questionSection').classList.add('hidden');
                document.getElementById('resultSection').classList.add('hidden');
                document.getElementById('stepProgressBadge').classList.remove('hidden');
                currentStepNumber = stepResponse.step.step_number || 1;
                document.getElementById('currentStepNum').textContent = currentStepNumber;
                updateProgressBar(currentStepNumber, 4);
                currentStepData = stepResponse.step;
                showForm(stepResponse.step.form_fields, stepResponse.step);
                return;
            }

            document.getElementById('formSection').classList.add('hidden');
            document.getElementById('resultSection').classList.add('hidden');

            const step = stepResponse.step;
            currentStepData = step;
            selectedOptionId = null;

            document.getElementById('questionSection').classList.remove('hidden');
            document.getElementById('stepProgressBadge').classList.remove('hidden');
            currentStepNumber = step.step_number || 2;
            document.getElementById('currentStepNum').textContent = currentStepNumber;
            updateProgressBar(currentStepNumber, 4);

            document.getElementById('questionText').textContent = step.question_text;
            const help = document.getElementById('questionHelp');
            if (step.help_text) {
                help.textContent = step.help_text;
                help.classList.remove('hidden');
            } else {
                help.classList.add('hidden');
            }

            // Back button visibility
            const backBtn = document.getElementById('backBtn');
            if (step.can_go_back) {
                backBtn.classList.remove('hidden');
            } else {
                backBtn.classList.add('hidden');
            }

            // Options
            const container = document.getElementById('optionsContainer');
            container.innerHTML = '';

            const keyLetters = ['A', 'B', 'C', 'D', 'E', 'F'];

            (step.options || []).forEach((opt, index) => {
                const isPreselected = step.selected_option_ids && step.selected_option_ids.includes(opt.id);
                if (isPreselected) selectedOptionId = opt.id;

                const letter = keyLetters[index] || (index + 1);
                const card = document.createElement('div');
                card.className = `option-card p-4 rounded-xl border <?= $isDark ? 'border-slate-800 bg-[#1A1F2B]' : 'border-slate-200 bg-white' ?> cursor-pointer flex items-center justify-between text-sm font-semibold <?= $isDark ? 'text-white' : 'text-[#0F172A]' ?> ${isPreselected ? 'selected' : ''}`;
                card.innerHTML = `
                    <div class="flex items-center gap-3">
                        <span class="option-key-badge w-6 h-6 rounded-md border <?= $isDark ? 'border-slate-700 bg-slate-800 text-slate-300' : 'border-slate-200 bg-slate-50 text-slate-600' ?> text-xs font-bold flex items-center justify-center">
                            ${letter}
                        </span>
                        <span>${escapeHtml(opt.label)}</span>
                    </div>
                    <span class="w-5 h-5 rounded-full border border-slate-300 flex items-center justify-center check-dot">
                        ${isPreselected ? '<span class="w-2.5 h-2.5 rounded-full brand-bg"></span>' : ''}
                    </span>
                `;

                card.addEventListener('click', () => {
                    document.querySelectorAll('.option-card').forEach(c => {
                        c.classList.remove('selected');
                        const dot = c.querySelector('.check-dot');
                        if (dot) dot.innerHTML = '';
                    });
                    card.classList.add('selected');
                    card.querySelector('.check-dot').innerHTML = '<span class="w-2.5 h-2.5 rounded-full brand-bg"></span>';
                    selectedOptionId = opt.id;
                    document.getElementById('answerErrorNotice').classList.add('hidden');
                });

                container.appendChild(card);
            });
        }

        async function handleAnswerSubmit() {
            if (!selectedOptionId) {
                const errBox = document.getElementById('answerErrorNotice');
                errBox.textContent = 'Please choose an option to continue.';
                errBox.classList.remove('hidden');
                return;
            }

            const btn = document.getElementById('continueBtn');
            btn.disabled = true;
            btn.textContent = 'Processing...';

            try {
                const resp = await fetch(`${API_BASE}/api/quiz/answer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Quiz-Session': sessionToken
                    },
                    body: JSON.stringify({
                        node_id: currentStepData.node_id,
                        answer: { option_id: selectedOptionId }
                    })
                });

                const data = await resp.json();
                if (data.success) {
                    renderStep(data.data);
                } else {
                    document.getElementById('answerErrorNotice').textContent = data.error || 'Failed to submit answer.';
                    document.getElementById('answerErrorNotice').classList.remove('hidden');
                }
            } catch (e) {
                document.getElementById('answerErrorNotice').textContent = 'Network error.';
                document.getElementById('answerErrorNotice').classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Continue →';
            }
        }

        async function handleBackNavigation() {
            const btn = document.getElementById('backBtn');
            if (btn) btn.disabled = true;
            const formBtn = document.getElementById('formBackBtn');
            if (formBtn) formBtn.disabled = true;

            try {
                const resp = await fetch(`${API_BASE}/api/quiz/back`, {
                    method: 'POST',
                    headers: { 'X-Quiz-Session': sessionToken }
                });
                const data = await resp.json();
                if (data.success) {
                    renderStep(data.data);
                }
            } catch (e) {
                alert('Back navigation failed.');
            } finally {
                if (btn) btn.disabled = false;
                if (formBtn) formBtn.disabled = false;
            }
        }

        function renderResult(result) {
            document.getElementById('questionSection').classList.add('hidden');
            document.getElementById('formSection').classList.add('hidden');
            document.getElementById('stepProgressBadge').classList.add('hidden');
            document.getElementById('resultSection').classList.remove('hidden');

            const resultDestCard = document.getElementById('resultDestCard');
            const cta = document.getElementById('destinationCtaBtn');
            const notice = document.getElementById('redirectNotice');

            if (result.completion_type === 'message') {
                document.getElementById('resultHeading').textContent = result.success_heading || 'Thank You!';
                document.getElementById('resultSubheading').textContent = result.success_message || 'Your submission has been received.';
                if (resultDestCard) resultDestCard.classList.add('hidden');
                if (cta) cta.classList.add('hidden');
                if (notice) notice.classList.add('hidden');
                return;
            }

            document.getElementById('resultHeading').textContent = 'Quiz Completed!';
            document.getElementById('resultSubheading').textContent = 'Thank you. Based on your answers, here is your recommendation:';
            if (resultDestCard) resultDestCard.classList.remove('hidden');
            if (cta) cta.classList.remove('hidden');

            document.getElementById('resultDestName').textContent = result.destination_name || 'Recommendation';
            cta.href = result.destination_url || '#';

            const summaryContainer = document.getElementById('resultAnswersSummary');
            summaryContainer.innerHTML = '';

            const answers = (result.summary && result.summary.answers) || [];
            answers.forEach(a => {
                summaryContainer.innerHTML += `
                    <div class="flex items-center justify-between text-[11px] py-1 border-b <?= $isDark ? 'border-slate-800' : 'border-slate-200/60' ?> last:border-0">
                        <span class="<?= $isDark ? 'text-slate-400' : 'text-slate-500' ?> truncate max-w-[280px]">${escapeHtml(a.question || a.question_text_snapshot || '')}</span>
                        <strong class="<?= $isDark ? 'text-slate-200' : 'text-slate-900' ?> font-semibold">${escapeHtml(a.answer || a.answer_value_text || '')}</strong>
                    </div>
                `;
            });

            if (result.auto_redirect && result.destination_url) {
                notice.classList.remove('hidden');
                let count = result.redirect_delay_seconds || 3;
                document.getElementById('countdownSeconds').textContent = count;

                const timer = setInterval(() => {
                    count--;
                    document.getElementById('countdownSeconds').textContent = count;
                    if (count <= 0) {
                        clearInterval(timer);
                        window.location.href = result.destination_url;
                    }
                }, 1000);
            }
        }

        function showError(msg) {
            document.getElementById('loadingState').innerHTML = `
                <div class="text-center py-8 text-rose-500 space-y-2">
                    <p class="font-bold text-sm">Unable to load quiz</p>
                    <p class="text-xs <?= $isDark ? 'text-slate-400' : 'text-slate-500' ?>">${escapeHtml(msg)}</p>
                </div>
            `;
        }

        function escapeHtml(str) {
            return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
    </script>
</body>
</html>
