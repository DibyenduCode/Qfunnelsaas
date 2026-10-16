<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'QFunnel — Interactive Quiz Funnel & Lead Qualification SaaS') ?></title>
    <meta name="description" content="Build visual branching quiz funnels, embed on WordPress & Shopify, capture high-intent leads, and automate webhooks & Google Sheets seamlessly.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }
        .hero-glow { background: radial-gradient(circle at 50% -10%, rgba(91, 91, 214, 0.12) 0%, rgba(250, 250, 252, 0) 70%); }
        .card-shadow { box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.06), 0 4px 12px -2px rgba(15, 23, 42, 0.03); }
        .card-hover { transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1); }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.12); }
        .brand-gradient { background: linear-gradient(135deg, #5B5BD6 0%, #3B38A6 100%); }
        .text-gradient { background: linear-gradient(135deg, #171A21 0%, #5B5BD6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-[#FAFAFC] text-[#171A21] antialiased selection:bg-[#5B5BD6] selection:text-white">

    <!-- ================= TOP NOTIFICATION BAR ================= -->
    <div class="bg-[#171A21] text-white text-xs py-2 px-4 text-center font-medium">
        <span class="inline-flex items-center gap-2">
            <span class="px-2 py-0.5 rounded-full bg-[#5B5BD6] text-[10px] font-extrabold uppercase">New</span>
            <span>Version 2.0 Released: Universal Embeds for WordPress & Shopify + Google Sheets Sync!</span>
            <a href="#features" class="underline hover:text-indigo-300 font-bold ml-1">Explore Features →</a>
        </span>
    </div>

    <!-- ================= NAVBAR ================= -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="<?= url('/') ?>" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl brand-gradient text-white flex items-center justify-center font-extrabold text-lg shadow-sm group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
                <div class="leading-tight">
                    <span class="text-xl font-extrabold text-[#171A21] tracking-tight flex items-center gap-1.5">
                        QFunnel
                        <span class="w-2 h-2 rounded-full bg-[#5B5BD6]"></span>
                    </span>
                    <span class="text-[10px] text-[#687080] font-semibold block uppercase tracking-wider">Quiz Funnel SaaS</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-[#687080]">
                <a href="#how-it-works" class="hover:text-[#171A21] transition-colors">How It Works</a>
                <a href="#builder-preview" class="hover:text-[#171A21] transition-colors">Visual Builder</a>
                <a href="#features" class="hover:text-[#171A21] transition-colors">Features</a>
                <a href="#pricing" class="hover:text-[#171A21] transition-colors">Pricing</a>
                <a href="#faq" class="hover:text-[#171A21] transition-colors">FAQ</a>
            </nav>

            <!-- Actions & Language Switcher -->
            <div class="flex items-center gap-4">
                
                <!-- Language Switcher Pill -->
                <div class="flex items-center bg-[#F1F3F6] p-1 rounded-full border border-[#E4E7EC] text-xs font-bold">
                    <button type="button" onclick="switchLanguage('en')" id="btnLangEn" class="px-2.5 py-1 rounded-full bg-[#171A21] text-white transition-all shadow-xs">ENG</button>
                    <button type="button" onclick="switchLanguage('bn')" id="btnLangBn" class="px-2.5 py-1 rounded-full text-[#687080] hover:text-[#171A21] transition-all">BN</button>
                </div>

                <?php if (!empty($currentUser)): ?>
                    <a href="<?= is_platform_admin() ? url('/admin/platform') : url('/admin/dashboard') ?>" 
                       class="px-5 py-2.5 rounded-full bg-[#171A21] text-white text-xs font-bold hover:bg-black transition-colors shadow-sm">
                        Go to Dashboard →
                    </a>
                <?php else: ?>
                    <a href="<?= url('/login') ?>" class="hidden sm:inline-block text-xs font-bold text-[#171A21] hover:text-[#5B5BD6] transition-colors px-2">
                        Sign in
                    </a>
                    <a href="<?= url('/register') ?>" class="px-5 py-2.5 rounded-full bg-[#171A21] text-white text-xs font-bold hover:bg-black transition-colors shadow-sm">
                        Get Started Free
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- ================= HERO SECTION ================= -->
    <section class="hero-glow pt-14 pb-20 lg:pt-20 lg:pb-28 overflow-hidden border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Hero Left: Value Proposition -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- Pill Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#EEF2FF] border border-[#C7D2FE] text-[#4F46E5] text-xs font-bold tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-[#4F46E5] animate-ping"></span>
                        <span id="heroBadgeText">🚀 3x Higher Conversion Than Static Web Forms</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-[#171A21] tracking-tight leading-[1.12]">
                        <span id="heroHead1">The smarter way to</span> <br>
                        <span id="heroHead2" class="text-[#5B5BD6]">qualify & convert leads</span> <br>
                        <span id="heroHead3">with interactive quizzes</span>
                    </h1>

                    <!-- Subtitle -->
                    <p id="heroSubText" class="text-base sm:text-lg text-[#687080] max-w-xl leading-relaxed">
                        Replace boring, low-converting static forms. Build visual branching quizzes that engage visitors, diagnose their exact pain points, capture high-intent leads, and instantly dispatch payloads to Google Sheets & your CRM.
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <a href="<?= url('/register') ?>" class="px-7 py-3.5 rounded-full bg-[#171A21] hover:bg-black text-white text-sm font-bold shadow-lg hover:shadow-xl transition-all flex items-center gap-2 group">
                            <span id="heroCtaText">Build Your First Funnel Free</span>
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                        <a href="<?= url('/embed-demo.html') ?>" class="px-6 py-3.5 rounded-full bg-white hover:bg-[#F8FAFC] text-[#171A21] border border-[#E4E7EC] text-sm font-bold shadow-xs hover:border-[#CBD5E1] transition-all flex items-center gap-2">
                            <span>✨ Test Live Embed Demo</span>
                        </a>
                    </div>

                    <!-- Trust Metrics -->
                    <div class="pt-4 flex flex-wrap items-center gap-6 text-xs text-[#687080]">
                        <div class="flex items-center gap-1.5">
                            <span class="text-amber-500 font-extrabold text-sm">★★★★★</span>
                            <strong class="text-[#171A21]">4.9/5 Rating</strong>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Free plan available</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>No coding required</span>
                        </div>
                    </div>

                </div>

                <!-- Hero Right: Interactive Live Quiz Funnel Node Simulator (100% QFunnel) -->
                <div class="lg:col-span-5 relative">
                    <div class="relative bg-white rounded-3xl p-6 sm:p-7 border border-[#E4E7EC] card-shadow space-y-5">
                        
                        <!-- Top Meta Header -->
                        <div class="flex items-center justify-between border-b border-[#F1F3F6] pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#5B5BD6] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                    AP
                                </div>
                                <div class="leading-tight">
                                    <h4 class="text-xs font-bold text-[#171A21]">Doctor Practice Referral Funnel</h4>
                                    <span class="text-[11px] text-[#687080]">Universal Widget • 3-Node Logic</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Published v1.2
                            </span>
                        </div>

                        <!-- Interactive Simulator Card Body -->
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center justify-between text-[11px] font-semibold text-[#687080] mb-1">
                                    <span>Question 1 of 3</span>
                                    <span class="text-[#5B5BD6] font-bold">Step Progress: 33%</span>
                                </div>
                                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-[#5B5BD6] h-full rounded-full" style="width: 33%;"></div>
                                </div>
                            </div>

                            <div class="pt-1">
                                <span class="text-[10px] font-bold text-[#5B5BD6] bg-[#EEF2FF] px-2 py-0.5 rounded uppercase tracking-wider">Branching Question</span>
                                <h3 class="text-sm font-bold text-[#171A21] mt-2">What is your primary patient care inquiry today?</h3>
                            </div>

                            <!-- Interactive Option Selector -->
                            <div class="space-y-2">
                                <button type="button" onclick="selectDemoAnswer(this, 'General Wellness Routine')" class="demo-option-btn w-full p-3 rounded-xl border border-[#E4E7EC] bg-white hover:border-[#5B5BD6] text-xs font-semibold text-[#171A21] flex items-center justify-between transition-all text-left">
                                    <span>A) General Preventive Wellness</span>
                                    <span class="text-[10px] text-[#687080] font-normal">Score +10</span>
                                </button>
                                
                                <button type="button" onclick="selectDemoAnswer(this, 'Specialized Chronic Care')" class="demo-option-btn w-full p-3 rounded-xl border-2 border-[#5B5BD6] bg-[#EEF2FF] text-xs font-bold text-[#5B5BD6] flex items-center justify-between transition-all text-left shadow-xs">
                                    <span>B) Specialized Chronic Care</span>
                                    <span class="text-[10px] bg-[#5B5BD6] text-white px-1.5 py-0.5 rounded font-bold">Selected ✓</span>
                                </button>

                                <button type="button" onclick="selectDemoAnswer(this, 'Urgent Referral Request')" class="demo-option-btn w-full p-3 rounded-xl border border-[#E4E7EC] bg-white hover:border-[#5B5BD6] text-xs font-semibold text-[#171A21] flex items-center justify-between transition-all text-left">
                                    <span>C) Urgent Specialist Referral</span>
                                    <span class="text-[10px] text-[#687080] font-normal">Score +25</span>
                                </button>
                            </div>

                            <!-- Live Branching Result Feedback -->
                            <div class="p-3.5 rounded-xl bg-[#0F172A] text-white space-y-1.5 text-xs">
                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                    <span>Automated Dynamic Route:</span>
                                    <span class="text-emerald-400 font-bold">Active Branch</span>
                                </div>
                                <div class="font-bold text-slate-100 flex items-center gap-1.5">
                                    <span>👉</span>
                                    <span id="demoBranchText">Routes to: High-Priority Referral Hub (Score: 85+)</span>
                                </div>
                            </div>

                        </div>

                        <!-- Footer Pill -->
                        <div class="flex items-center justify-between text-[11px] text-[#687080] pt-2 border-t border-[#F1F3F6]">
                            <span>Universal Embed Active</span>
                            <span class="text-emerald-600 font-bold">HMAC Webhook Verified ✓</span>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ================= METRICS / STATS SECTION ================= -->
    <section class="py-12 bg-white border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-extrabold text-[#5B5BD6] block">310%</span>
                    <p class="text-xs text-[#687080] font-medium">Average Lead Conversion Boost</p>
                </div>
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-extrabold text-[#171A21] block">&lt; 60s</span>
                    <p class="text-xs text-[#687080] font-medium">Quiz Completion Time</p>
                </div>
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-extrabold text-[#5B5BD6] block">99.8%</span>
                    <p class="text-xs text-[#687080] font-medium">Webhook Delivery Reliability</p>
                </div>
                <div class="space-y-1">
                    <span class="text-3xl sm:text-4xl font-extrabold text-[#171A21] block">1-Click</span>
                    <p class="text-xs text-[#687080] font-medium">WordPress & Shopify Embed</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= HOW IT WORKS SECTION (QFunnel 3-Step Architecture) ================= -->
    <section id="how-it-works" class="py-20 lg:py-28 bg-[#FAFAFC] border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-bold tracking-widest uppercase text-[#5B5BD6]">HOW IT WORKS</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#171A21] tracking-tight">
                    From idea to live lead engine in 3 simple steps
                </h2>
                <p class="text-base text-[#687080]">
                    Everything you need to automate your interactive lead qualification and segmentation flow.
                </p>
            </div>

            <!-- 3 Step Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 mb-12">
                
                <!-- Step 1 -->
                <div class="p-8 rounded-3xl bg-white border border-[#E4E7EC] card-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-xl bg-[#171A21] text-white flex items-center justify-center font-extrabold text-sm">
                            1
                        </div>
                        <span class="text-xl">🎨</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#171A21]">Design visual logic flow</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Drag and drop question cards, conditional branches, score thresholds, and outcome destinations on an infinite visual canvas.
                    </p>
                </div>

                <!-- Step 2 (Highlighted Active) -->
                <div class="p-8 rounded-3xl bg-white border-2 border-[#5B5BD6] card-hover space-y-4 relative shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-xl bg-[#5B5BD6] text-white flex items-center justify-center font-extrabold text-sm shadow-sm">
                            2
                        </div>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#5B5BD6] bg-[#EEF2FF] px-2 py-0.5 rounded-full">
                            • Universal Embed
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-[#171A21]">Embed on any website</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Paste 1 clean snippet into WordPress, Shopify, Webflow, or custom landing pages. Choose Inline, Lightbox Modal, or Floating Corner Widget.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="p-8 rounded-3xl bg-white border border-[#E4E7EC] card-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-xl bg-[#171A21] text-white flex items-center justify-center font-extrabold text-sm">
                            3
                        </div>
                        <span class="text-xl">⚡</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#171A21]">Automate leads & webhooks</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Instantly sync visitor contact details, quiz answers, and computed scores to Google Sheets, CRM, or custom webhook endpoints with HMAC security.
                    </p>
                </div>

            </div>

            <!-- Visual Flow Architecture Banner (Underneath Steps) -->
            <div id="builder-preview" class="max-w-4xl mx-auto rounded-3xl border border-[#E4E7EC] bg-white p-6 sm:p-8 card-shadow space-y-6">
                <div class="flex items-center justify-between border-b border-[#F1F3F6] pb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-bold text-[#687080] ml-2">Visual Graph Engine • Live Architecture</span>
                    </div>
                    <span class="text-xs font-bold text-[#5B5BD6] bg-[#EEF2FF] px-2.5 py-1 rounded-full">Automated Pipeline</span>
                </div>

                <!-- Pipeline Diagram -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-center">
                    <div class="p-4 rounded-2xl bg-[#FAFAFC] border border-[#E4E7EC] space-y-1">
                        <span class="text-xs font-bold text-[#5B5BD6] block">STEP 1</span>
                        <h4 class="text-xs font-bold text-[#171A21]">Visitor Arrives</h4>
                        <p class="text-[11px] text-[#687080]">Pre-quiz form collects Name, Email & Phone</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#EEF2FF] border border-[#C7D2FE] space-y-1">
                        <span class="text-xs font-bold text-[#5B5BD6] block">STEP 2</span>
                        <h4 class="text-xs font-bold text-[#171A21]">Interactive Branching</h4>
                        <p class="text-[11px] text-[#687080]">Conditional nodes route user based on answers</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#FAFAFC] border border-[#E4E7EC] space-y-1">
                        <span class="text-xs font-bold text-[#5B5BD6] block">STEP 3</span>
                        <h4 class="text-xs font-bold text-[#171A21]">Outcome Delivery</h4>
                        <p class="text-[11px] text-[#687080]">Tailored recommendation & CTA displayed</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#0F172A] text-white space-y-1">
                        <span class="text-xs font-bold text-emerald-400 block">STEP 4</span>
                        <h4 class="text-xs font-bold text-white">Instant Webhook</h4>
                        <p class="text-[11px] text-slate-300">Payload fired to Google Sheets / CRM</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between text-xs text-[#687080] pt-2">
                    <span class="flex items-center gap-1.5">
                        🔒 HMAC-SHA256 Payload Verification • Atomic Graph Rollback
                    </span>
                    <strong class="text-emerald-600 font-bold">Zero Data Loss Guarantee ✓</strong>
                </div>
            </div>

        </div>
    </section>

    <!-- ================= FEATURES SECTION ================= -->
    <section id="features" class="py-20 lg:py-28 bg-white border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-bold tracking-widest uppercase text-[#5B5BD6]">FEATURES</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#171A21] tracking-tight">
                    Everything you need to convert & qualify leads
                </h2>
                <p class="text-base text-[#687080]">
                    Packed with enterprise-grade features built for agencies, SaaS founders, and growth marketing teams.
                </p>
            </div>

            <!-- 6 Grid Feature Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 mb-16">
                
                <!-- Feature 1: Canvas Builder -->
                <div class="p-7 rounded-3xl bg-[#FAFAFC] border border-[#E4E7EC] card-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                            🎨
                        </div>
                        <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">VISUAL CANVAS</span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">Drag & Drop Funnel Builder</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Design question flow, conditional branches, score calculations, and destination routes with real-time graph validation.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('/register') ?>" class="text-xs font-bold text-[#5B5BD6] hover:underline flex items-center gap-1">
                            Explore Visual Builder <span>→</span>
                        </a>
                    </div>
                </div>

                <!-- Feature 2: Universal Embeds -->
                <div class="p-7 rounded-3xl bg-white border-2 border-[#5B5BD6] card-hover space-y-4 relative shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                            🌐
                        </div>
                        <span class="text-[10px] font-bold text-[#5B5BD6] bg-[#EEF2FF] px-2 py-0.5 rounded-full uppercase tracking-wider">
                            3-IN-1 EMBEDS
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">Universal Website Embeds</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Inline embedded quiz, popup lightbox modal, or floating corner bubble widget for WordPress, Shopify, and Webflow.
                    </p>
                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-emerald-600 flex items-center gap-1">• Live Widget Active</span>
                        <a href="<?= url('/embed-demo.html') ?>" class="text-[#5B5BD6] font-bold text-xs hover:underline">Demo →</a>
                    </div>
                </div>

                <!-- Feature 3: Webhooks & Google Sheets -->
                <div class="p-7 rounded-3xl bg-[#FAFAFC] border border-[#E4E7EC] card-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                            ⚡
                        </div>
                        <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">AUTOMATION</span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">Webhooks & Google Sheets</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Automatic real-time payload delivery with auto-retry logic, event delivery timelines, and HMAC signature protection.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('/register') ?>" class="text-xs font-bold text-[#5B5BD6] hover:underline flex items-center gap-1">
                            Explore Webhook Engine <span>→</span>
                        </a>
                    </div>
                </div>

                <!-- Feature 4: Lead Capture & Pre-Quiz Forms -->
                <div class="p-7 rounded-3xl bg-[#FAFAFC] border border-[#E4E7EC] card-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                            📝
                        </div>
                        <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">LEAD CAPTURE</span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">Custom Pre-Quiz Form Fields</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Collect Name, Email, Phone/WhatsApp, and custom intent criteria before questions begin to prevent drop-off loss.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('/register') ?>" class="text-xs font-bold text-[#5B5BD6] hover:underline flex items-center gap-1">
                            Explore Forms <span>→</span>
                        </a>
                    </div>
                </div>

                <!-- Feature 5: Agency White-Labeling -->
                <div class="p-7 rounded-3xl bg-[#FAFAFC] border border-[#E4E7EC] card-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg">
                            🏷️
                        </div>
                        <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">AGENCY</span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">100% White-Label Branding</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Upload custom logos, define brand accent colors, set trust badges, remove SaaS watermarks, and use custom domains.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('/register') ?>" class="text-xs font-bold text-[#5B5BD6] hover:underline flex items-center gap-1">
                            Explore White-Label <span>→</span>
                        </a>
                    </div>
                </div>

                <!-- Feature 6: Developer REST API -->
                <div class="p-7 rounded-3xl bg-[#FAFAFC] border border-[#E4E7EC] card-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold text-lg">
                            &lt;/&gt;
                        </div>
                        <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">V1 REST API</span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">Developer REST API (v1)</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Bearer-token authenticated API endpoints to programmatically trigger funnels, query submissions, and sync analytics.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('/register') ?>" class="text-xs font-bold text-[#5B5BD6] hover:underline flex items-center gap-1">
                            Explore API Docs <span>→</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Spotlight Feature Banner (Modern Dark Navy Container) -->
            <div class="rounded-3xl bg-[#0D0F14] text-white p-6 sm:p-8 card-shadow">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5 mb-6">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded-lg bg-[#5B5BD6] text-white text-xs font-extrabold">#1</span>
                        <div>
                            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-400 block">SPOTLIGHT ARCHITECTURE</span>
                            <h4 class="text-base font-bold text-white">Universal Embed Engine & Instant Webhooks</h4>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                        Built for Scale
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">CMS INTEGRATION</span>
                        <p class="font-bold text-white">WordPress, Shopify & Webflow</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">WEBHOOK ENGINE</span>
                        <p class="font-bold text-white">Google Sheets & CRM Sync</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">CRYPTOGRAPHIC SECURITY</span>
                        <p class="font-bold text-white">HMAC-SHA256 Signature Verification</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ================= PRICING SECTION ================= -->
    <section id="pricing" class="py-20 lg:py-28 bg-[#FAFAFC] border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-bold tracking-widest uppercase text-[#5B5BD6]">TRANSPARENT TIERS</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#171A21] tracking-tight">
                    Pick the plan that fits your growth
                </h2>
                <p class="text-base text-[#687080]">
                    Simple, transparent pricing for solo creators, growing marketing teams, and high-volume agencies.
                </p>
            </div>

            <!-- 4 Pricing Columns Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 items-stretch mb-12">
                
                <!-- 1. Free Forever Tier -->
                <div class="flex flex-col justify-between p-7 rounded-3xl bg-white border border-[#E4E7EC] card-hover">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#171A21]">Free</h3>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">FREE FOREVER</span>
                        </div>
                        <div>
                            <span class="text-3xl font-extrabold text-[#171A21]">₹0</span>
                            <span class="text-xs text-[#687080]"> / forever</span>
                        </div>
                        <p class="text-xs text-[#687080]">
                            Essential single-funnel plan for testing interactive quizzes and lead capture.
                        </p>
                        
                        <div class="pt-4 border-t border-[#E4E7EC] space-y-2.5 text-xs text-[#687080]">
                            <span class="text-[10px] font-bold text-[#171A21] uppercase tracking-wider block mb-2">INCLUDED:</span>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>1 Active Funnel</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>100 Submissions / month</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Visual Canvas Builder</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Standard Universal Embeds</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Bilingual Support (English & Bengali)</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <a href="<?= url('/register') ?>" class="w-full py-2.5 px-4 rounded-xl bg-[#171A21] hover:bg-black text-white text-xs font-bold transition-all text-center block shadow-sm">
                            Try for Free →
                        </a>
                    </div>
                </div>

                <!-- 2. Starter Tier -->
                <div class="flex flex-col justify-between p-7 rounded-3xl bg-white border border-[#E4E7EC] card-hover relative shadow-sm">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#171A21]">Starter</h3>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">GROWTH</span>
                        </div>
                        <div>
                            <span class="text-3xl font-extrabold text-[#171A21]">₹749</span>
                            <span class="text-xs text-[#687080]"> / per month</span>
                        </div>
                        <p class="text-xs text-[#687080]">
                            Ideal for emerging creators and growing lead funnels needing custom branding.
                        </p>
                        
                        <div class="pt-4 border-t border-[#E4E7EC] space-y-2.5 text-xs text-[#687080]">
                            <span class="text-[10px] font-bold text-[#171A21] uppercase tracking-wider block mb-2">INCLUDED:</span>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <strong>5 Active Funnels</strong>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>1,000 Submissions / month</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>CSV Lead Data Export</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Custom Brand Logo & Colors</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>3 Team Members Workspace</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <a href="<?= url('/register') ?>" class="w-full py-2.5 px-4 rounded-xl border border-[#171A21] hover:bg-[#171A21] hover:text-white text-[#171A21] text-xs font-bold transition-all text-center block">
                            Choose Starter →
                        </a>
                    </div>
                </div>

                <!-- 3. Pro Tier (HIGHLIGHTED DARK CARD) -->
                <div class="flex flex-col justify-between p-7 rounded-3xl bg-[#0D0F14] text-white border-2 border-[#5B5BD6] card-hover relative shadow-xl">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-white">Pro</h3>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold bg-[#5B5BD6] text-white">MOST POPULAR</span>
                        </div>
                        <div>
                            <span class="text-3xl font-extrabold text-white">₹1,499</span>
                            <span class="text-xs text-slate-400"> / per month</span>
                        </div>
                        <p class="text-xs text-slate-300">
                            Unlimited funnels and submissions for high-velocity teams and agencies.
                        </p>
                        
                        <div class="pt-4 border-t border-slate-800 space-y-2.5 text-xs text-slate-300">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">INCLUDED:</span>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <strong class="text-white">Unlimited Active Funnels</strong>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Unlimited Lead Submissions</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Webhooks & Google Sheets Sync</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>REST API (v1) with Bearer Token</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>100% White-Label (No SaaS branding)</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>10 Team Members Workspace</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <a href="<?= url('/register') ?>" class="w-full py-2.5 px-4 rounded-xl bg-white hover:bg-slate-100 text-[#0D0F14] text-xs font-bold transition-all text-center block shadow-md">
                            Get Pro Access →
                        </a>
                    </div>
                </div>

                <!-- 4. Super Admin / Agency Tier -->
                <div class="flex flex-col justify-between p-7 rounded-3xl bg-white border border-[#E4E7EC] card-hover">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#171A21]">Super Admin</h3>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-900 text-white">PLATFORM LICENSE</span>
                        </div>
                        <div>
                            <span class="text-2xl font-extrabold text-[#171A21]">Custom</span>
                            <span class="text-xs text-[#687080]"> / platform license</span>
                        </div>
                        <p class="text-xs text-[#687080]">
                            Complete platform control, multi-tenant workspace isolation, and custom domains.
                        </p>
                        
                        <div class="pt-4 border-t border-[#E4E7EC] space-y-2.5 text-xs text-[#687080]">
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Dedicated Super Admin Control Panel</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Multi-Tenant Workspaces & User Management</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Custom SMTP & System Health Monitors</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Full Database & Server Ownership</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Unlimited Team Members Allowed</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <a href="<?= url('/login') ?>" class="w-full py-2.5 px-4 rounded-xl bg-[#171A21] hover:bg-black text-white text-xs font-bold transition-all text-center block shadow-sm">
                            Contact Platform Sales →
                        </a>
                    </div>
                </div>

            </div>

            <!-- Lifetime Deal Card -->
            <div class="max-w-md p-7 rounded-3xl bg-[#0D0F14] text-white border-2 border-[#5B5BD6] card-hover relative shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white">Lifetime Deal</h3>
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold bg-[#5B5BD6] text-white uppercase">ONE-TIME ACCESS</span>
                </div>
                
                <div class="mb-2">
                    <span class="text-3xl font-extrabold text-white">$49</span>
                    <span class="text-xs text-slate-400"> / lifetime access</span>
                </div>
                
                <p class="text-xs text-slate-300 mb-5">
                    Pay once, use forever. No recurring monthly subscriptions.
                </p>

                <div class="space-y-2.5 text-xs text-slate-300 border-t border-slate-800 pt-4 mb-6">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">LIFETIME PERKS:</span>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <strong class="text-white">Everything included in Pro Tier</strong>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>Unlimited Funnels & Lead Submissions</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>Webhooks, REST API & Google Sheets</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>All Future Platform Updates Included</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>Zero Subscription, Forever</span>
                    </div>
                </div>

                <a href="<?= url('/register') ?>" class="w-full py-2.5 px-4 rounded-xl bg-white hover:bg-slate-100 text-[#0D0F14] text-xs font-bold transition-all text-center block shadow-md">
                    Claim Lifetime Access →
                </a>
            </div>

        </div>
    </section>

    <!-- ================= FAQ SECTION (Real QFunnel Q&As) ================= -->
    <section id="faq" class="py-20 lg:py-28 bg-white border-b border-[#E4E7EC]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="text-center mb-16 space-y-3">
                <span class="text-xs font-bold tracking-widest uppercase text-[#5B5BD6]">FAQ</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#171A21] tracking-tight">
                    Frequently asked questions
                </h2>
                <p class="text-base text-[#687080]">
                    Everything you need to know about building, embedding, and automating quiz funnels.
                </p>
            </div>

            <!-- Accordion List -->
            <div class="space-y-3">
                
                <!-- FAQ Item 1 (Open by default) -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Why are interactive quiz funnels better than traditional forms?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▲</span>
                    </button>
                    <div class="faq-body px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Traditional static forms ask for contact info upfront with no immediate value, causing high drop-off rates (averaging under 3% conversion). In contrast, QFunnel interactive quizzes provide instant diagnosis, personalized recommendations, and engaging micro-commitments, typically increasing lead conversions to 12% - 25%.
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>How does embedding work on WordPress, Shopify, and Webflow?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        QFunnel generates clean, 1-line embed snippets. You simply paste the code into a Custom HTML block in WordPress (Elementor/Gutenberg), a Custom Liquid section in Shopify, or an HTML embed element in Webflow. You can choose between an <strong>Inline Embed</strong>, a <strong>Popup Modal Button</strong>, or a <strong>Floating Corner Widget</strong>.
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Can I sync leads directly into Google Sheets and external Webhooks?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Yes! Whenever a respondent completes your quiz, QFunnel automatically fires an instant JSON payload to your configured Webhook URL (e.g. Google Apps Script, Zapier, Make, or custom CRM). Every webhook payload includes a cryptographic HMAC-SHA256 signature for complete security and automatic retry logs.
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Can I customize the branding and remove the QFunnel badge?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Yes! With White-Label enabled on Pro and Agency plans, you can upload your custom company logo, adjust logo position/height, configure brand accent colors, define custom trust badge text, and completely hide SaaS watermarks.
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>How does branching logic work if a user changes an answer?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        QFunnel includes an atomic backtracking engine. If a respondent clicks the "Back" button and chooses a different answer, all downstream answers that depended on the old answer are automatically invalidated and the route is smoothly recalculated.
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Can I invite team members to collaborate on my funnels?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Yes! Growth and Pro plans support multi-user workspaces. You can invite team members and assign granular permissions: <em>Member</em> (view/edit), <em>Manager</em> (publish and manage webhooks), or <em>Admin</em> (full workspace settings control).
                    </div>
                </div>

                <!-- FAQ Item 7 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Do you offer a REST API for developers?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Yes! QFunnel provides a documented v1 REST API authenticated with Bearer tokens. You can query published funnels, fetch submission events, initialize remote quiz sessions, and sync customer analytics into custom applications.
                    </div>
                </div>

                <!-- FAQ Item 8 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Are lead submissions and customer data secure?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Absolutely. Every workspace has isolated tenant boundaries to prevent cross-account data leakage (strict IDOR protection). All form transmissions use 256-bit SSL encryption, CSRF protection, and sanitized database queries.
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ================= PRE-FOOTER CTA SECTION ================= -->
    <section class="py-24 bg-[#FAFAFC] text-center border-b border-[#E4E7EC]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-[#171A21] tracking-tight mb-3">
                Ready to 3x your lead conversion?
            </h2>
            <p class="text-sm sm:text-base text-[#687080] mb-8">
                Build your first interactive quiz funnel in less than 2 minutes. Free forever, no credit card required.
            </p>
            <a href="<?= url('/register') ?>" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-[#171A21] hover:bg-black text-white text-sm font-bold shadow-lg hover:shadow-xl transition-all">
                <span>Start Building Free</span>
                <span>→</span>
            </a>
        </div>
    </section>

    <!-- ================= DARK FOOTER ================= -->
    <footer class="bg-[#0B0D12] text-slate-400 py-16 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
                
                <!-- Col 1: Brand & Status -->
                <div class="space-y-4 md:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg brand-gradient text-white flex items-center justify-center font-bold text-base">
                            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                <polyline points="2 17 12 22 22 17"></polyline>
                                <polyline points="2 12 12 17 22 12"></polyline>
                            </svg>
                        </div>
                        <span class="text-base font-extrabold text-white tracking-tight">QFunnel</span>
                    </div>
                    
                    <p class="text-xs text-slate-400 leading-relaxed">
                        The interactive quiz funnel & lead qualification platform. Build visual branching logic, embed anywhere, and automate workflows effortlessly.
                    </p>

                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-[11px] font-semibold text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        All Systems Operational • 99.9% Uptime
                    </div>
                </div>

                <!-- Col 2: Product Links -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-3">PRODUCT</h5>
                    <div><a href="#how-it-works" class="hover:text-white transition-colors">How It Works</a></div>
                    <div><a href="#builder-preview" class="hover:text-white transition-colors">Visual Canvas Builder</a></div>
                    <div><a href="#features" class="hover:text-white transition-colors">Universal Embeds (WordPress & Shopify)</a></div>
                    <div><a href="<?= url('/embed-demo.html') ?>" class="hover:text-white transition-colors">Live Interactive Demo</a></div>
                    <div><a href="#pricing" class="hover:text-white transition-colors">Pricing & Plans</a></div>
                    <div><a href="#faq" class="hover:text-white transition-colors">FAQ & Knowledgebase</a></div>
                    <div><a href="<?= url('/register') ?>" class="text-[#5B5BD6] font-semibold hover:underline">Create Free Workspace →</a></div>
                </div>

                <!-- Col 3: Integrations & Trust -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-3">INTEGRATIONS & TRUST</h5>
                    <div><a href="#features" class="hover:text-white transition-colors">Google Sheets Webhook Sync</a></div>
                    <div><a href="#features" class="hover:text-white transition-colors">WordPress Embed Guide</a></div>
                    <div><a href="#features" class="hover:text-white transition-colors">Shopify Liquid Section</a></div>
                    <div><a href="#features" class="hover:text-white transition-colors">HMAC-SHA256 Verification</a></div>
                    <div><a href="#faq" class="hover:text-white transition-colors">Privacy Policy & GDPR</a></div>
                    <div><a href="#faq" class="hover:text-white transition-colors">Terms of Service</a></div>
                </div>

                <!-- Col 4: Official Contact -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-3">OFFICIAL CONTACT</h5>
                    <div class="flex items-start gap-2">
                        <span>👤</span>
                        <div>
                            <strong class="text-white block font-semibold">Dibyendu Kumar Khaskel</strong>
                            <span class="text-[10px] text-slate-500">Platform Founder & Lead Engineer</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>✉️</span>
                        <a href="mailto:support@qfunnel.com" class="hover:text-white transition-colors">support@qfunnel.com</a>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>📞</span>
                        <span>+91 6294629735</span>
                    </div>
                    <div class="text-[11px] text-slate-500 pl-6">
                        Mon–Sat: 10 AM – 7 PM IST
                    </div>
                    <div class="flex items-start gap-2 pt-1">
                        <span>📍</span>
                        <span class="text-[11px]">Helencha Road, Duttapulia, Baikola, West Bengal 743270, India</span>
                    </div>
                </div>

            </div>

            <!-- Footer Bottom Bar -->
            <div class="pt-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs text-slate-500">
                <div>
                    © <?= date('Y') ?> QFunnel Platform Inc. All rights reserved.
                </div>
                <div>
                    Engineered with precision for marketing teams & high-converting businesses.
                </div>
                <div class="flex items-center gap-2">
                    <span>Language:</span>
                    <div class="inline-flex bg-slate-900 border border-slate-800 rounded-full p-0.5 text-[11px] font-bold">
                        <button type="button" onclick="switchLanguage('en')" class="px-2 py-0.5 rounded-full bg-slate-800 text-white">ENG</button>
                        <button type="button" onclick="switchLanguage('bn')" class="px-2 py-0.5 rounded-full text-slate-400 hover:text-white">BN</button>
                    </div>
                </div>
            </div>

        </div>
    </footer>

    <!-- ================= JAVASCRIPT LOGIC ================= -->
    <script>
        // Interactive Hero Simulator logic
        function selectDemoAnswer(btn, choice) {
            document.querySelectorAll('.demo-option-btn').forEach(b => {
                b.className = 'demo-option-btn w-full p-3 rounded-xl border border-[#E4E7EC] bg-white hover:border-[#5B5BD6] text-xs font-semibold text-[#171A21] flex items-center justify-between transition-all text-left';
                const badge = b.querySelector('span:last-child');
                if (badge && badge.textContent.includes('Selected')) {
                    badge.outerHTML = '<span class="text-[10px] text-[#687080] font-normal">Score +15</span>';
                }
            });

            btn.className = 'demo-option-btn w-full p-3 rounded-xl border-2 border-[#5B5BD6] bg-[#EEF2FF] text-xs font-bold text-[#5B5BD6] flex items-center justify-between transition-all text-left shadow-xs';
            const rightBadge = btn.querySelector('span:last-child');
            if (rightBadge) {
                rightBadge.outerHTML = '<span class="text-[10px] bg-[#5B5BD6] text-white px-1.5 py-0.5 rounded font-bold">Selected ✓</span>';
            }

            const branchEl = document.getElementById('demoBranchText');
            if (choice === 'General Wellness Routine') {
                branchEl.textContent = 'Routes to: General Care Advice & Standard Hub';
            } else if (choice === 'Specialized Chronic Care') {
                branchEl.textContent = 'Routes to: High-Priority Referral Hub (Score: 85+)';
            } else if (choice === 'Urgent Referral Request') {
                branchEl.textContent = 'Routes to: Emergency Callback & Priority Dispatch (Score: 100)';
            }
        }

        // FAQ Accordion toggler
        function toggleFaq(btn) {
            const body = btn.nextElementSibling;
            const icon = btn.querySelector('.faq-icon');
            const isOpen = !body.classList.contains('hidden');
            
            // Close all
            document.querySelectorAll('.faq-body').forEach(b => b.classList.add('hidden'));
            document.querySelectorAll('.faq-icon').forEach(i => i.textContent = '▼');

            if (!isOpen) {
                body.classList.remove('hidden');
                icon.textContent = '▲';
            }
        }

        // Language Switcher (English / Bengali)
        const translations = {
            en: {
                heroBadge: '🚀 3x Higher Conversion Than Static Web Forms',
                head1: 'The smarter way to',
                head2: 'qualify & convert leads',
                head3: 'with interactive quizzes',
                sub: 'Replace boring, low-converting static forms. Build visual branching quizzes that engage visitors, diagnose their exact pain points, capture high-intent leads, and instantly dispatch payloads to Google Sheets & your CRM.',
                cta: 'Build Your First Funnel Free'
            },
            bn: {
                heroBadge: '🚀 সাধারণ ওয়েব ফর্মের চেয়ে ৩ গুণ বেশি লিড কনভার্সন',
                head1: 'ইন্টারেক্টিভ কুইজের মাধ্যমে',
                head2: 'লিড সংগ্রহ ও বাছাই করার',
                head3: 'সবচেয়ে সহজ ও স্মার্ট প্ল্যাটফর্ম',
                sub: 'বোরিং স্ট্যাটিক ফর্ম ব্যবহার বন্ধ করুন। কুইজ তৈরি করে গ্রাহকদের সম্পৃক্ত করুন, তাদের সঠিক চাহিদা জানুন এবং সংগৃহীত লিড স্বয়ংক্রিয়ভাবে গুগল শিট ও সিআরএমে পাঠান।',
                cta: 'বিনামূল্যে প্রথম ফানেল তৈরি করুন'
            }
        };

        function switchLanguage(lang) {
            const t = translations[lang] || translations.en;
            document.getElementById('heroBadgeText').textContent = t.heroBadge;
            document.getElementById('heroHead1').textContent = t.head1;
            document.getElementById('heroHead2').textContent = t.head2;
            document.getElementById('heroHead3').textContent = t.head3;
            document.getElementById('heroSubText').textContent = t.sub;
            document.getElementById('heroCtaText').textContent = t.cta;

            if (lang === 'bn') {
                document.getElementById('btnLangBn').className = 'px-2.5 py-1 rounded-full bg-[#171A21] text-white transition-all shadow-xs';
                document.getElementById('btnLangEn').className = 'px-2.5 py-1 rounded-full text-[#687080] hover:text-[#171A21] transition-all';
            } else {
                document.getElementById('btnLangEn').className = 'px-2.5 py-1 rounded-full bg-[#171A21] text-white transition-all shadow-xs';
                document.getElementById('btnLangBn').className = 'px-2.5 py-1 rounded-full text-[#687080] hover:text-[#171A21] transition-all';
            }
        }
    </script>
</body>
</html>
