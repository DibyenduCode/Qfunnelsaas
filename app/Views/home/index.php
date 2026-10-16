<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'QFunnel — The Smarter Way to Build Interactive Quiz Funnels') ?></title>
    <meta name="description" content="Build visual branching quiz funnels, embed on WordPress & Shopify, capture high-intent leads, and automate webhooks seamlessly.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }
        .hero-gradient { background: radial-gradient(circle at 50% 0%, rgba(91, 91, 214, 0.08) 0%, rgba(255, 255, 255, 0) 70%); }
        .card-shadow { box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.06), 0 4px 12px -2px rgba(15, 23, 42, 0.03); }
        .card-shadow-hover { transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1); }
        .card-shadow-hover:hover { transform: translateY(-3px); box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.12); }
    </style>
</head>
<body class="bg-[#FAFAFC] text-[#171A21] antialiased selection:bg-[#5B5BD6] selection:text-white">

    <!-- ================= NAVBAR ================= -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Brand Logo -->
            <a href="<?= url('/') ?>" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-[#5B5BD6] text-white flex items-center justify-center font-extrabold text-lg shadow-sm group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div class="leading-tight">
                    <span class="text-xl font-extrabold text-[#171A21] tracking-tight flex items-center gap-1.5">
                        QFunnel
                        <span class="w-2 h-2 rounded-full bg-[#5B5BD6]"></span>
                    </span>
                    <span class="text-[10px] text-[#687080] font-medium block uppercase tracking-wider">Quiz Funnel SaaS</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-[#687080]">
                <a href="#how-it-works" class="hover:text-[#171A21] transition-colors nav-how">How it works</a>
                <a href="#features" class="hover:text-[#171A21] transition-colors nav-features">Features</a>
                <a href="#pricing" class="hover:text-[#171A21] transition-colors nav-pricing">Pricing</a>
                <a href="#faq" class="hover:text-[#171A21] transition-colors nav-faq">FAQ</a>
            </nav>

            <!-- Actions & Language Switcher -->
            <div class="flex items-center gap-4">
                
                <!-- Language Switcher Pill -->
                <div class="flex items-center bg-[#F1F3F6] p-1 rounded-full border border-[#E4E7EC] text-xs font-bold">
                    <button type="button" onclick="switchLang('en')" id="langEnBtn" class="px-2.5 py-1 rounded-full bg-[#171A21] text-white transition-all shadow-xs">ENG</button>
                    <button type="button" onclick="switchLang('bn')" id="langBnBtn" class="px-2.5 py-1 rounded-full text-[#687080] hover:text-[#171A21] transition-all">BN</button>
                </div>

                <?php if (!empty($currentUser)): ?>
                    <a href="<?= is_platform_admin() ? url('/admin/platform') : url('/admin/dashboard') ?>" 
                       class="px-5 py-2.5 rounded-full bg-[#171A21] text-white text-xs font-bold hover:bg-[#000] transition-colors shadow-sm">
                        Dashboard →
                    </a>
                <?php else: ?>
                    <a href="<?= url('/login') ?>" class="hidden sm:inline-block text-xs font-bold text-[#171A21] hover:text-[#5B5BD6] transition-colors px-2 nav-signin">
                        Sign in
                    </a>
                    <a href="<?= url('/register') ?>" class="px-5 py-2.5 rounded-full bg-[#171A21] text-white text-xs font-bold hover:bg-[#000] transition-colors shadow-sm nav-getstarted">
                        Get started
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- ================= HERO SECTION ================= -->
    <section class="hero-gradient pt-16 pb-20 lg:pt-24 lg:pb-32 overflow-hidden border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left Column: Copy & Value Proposition -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- Pill Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#EEF2FF] border border-[#C7D2FE] text-[#4F46E5] text-xs font-bold tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-[#4F46E5] animate-pulse"></span>
                        <span id="txtHeroBadge">Smart & Effortless Lead Qualification</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-[#171A21] tracking-tight leading-[1.12]">
                        <span id="txtHeroHead1">The better way to</span> <br>
                        <span id="txtHeroHead2" class="text-[#5B5BD6]">convert your visitors</span> <br>
                        <span id="txtHeroHead3">into customers</span>
                    </h1>

                    <!-- Subtitle -->
                    <p id="txtHeroSub" class="text-base sm:text-lg text-[#687080] max-w-xl leading-relaxed">
                        QFunnel simplifies appointment scheduling & lead generation. Share your custom quiz link, sync customer data, collect payments, and automate webhooks — seamlessly.
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <a href="<?= url('/register') ?>" class="px-7 py-3.5 rounded-full bg-[#171A21] hover:bg-black text-white text-sm font-bold shadow-lg hover:shadow-xl transition-all flex items-center gap-2 group">
                            <span id="txtHeroCta">Get started for free</span>
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                        <a href="<?= url('/embed-demo.html') ?>" class="px-6 py-3.5 rounded-full bg-white hover:bg-[#F8FAFC] text-[#171A21] border border-[#E4E7EC] text-sm font-bold shadow-xs hover:border-[#CBD5E1] transition-all">
                            <span id="txtHeroDemo">View live quiz demo</span>
                        </a>
                    </div>

                    <!-- Social Proof / Trust Indicators -->
                    <div class="pt-4 flex flex-wrap items-center gap-6 text-xs text-[#687080]">
                        <div class="flex items-center gap-1.5">
                            <span class="text-amber-500 font-extrabold text-sm">★★★★★</span>
                            <strong class="text-[#171A21]">4.9/5</strong>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span id="txtTrustFree">Free forever for individuals</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span id="txtTrustCard">No credit card required</span>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Interactive Hero Mockup Card (Inspired directly by reference) -->
                <div class="lg:col-span-5 relative">
                    <div class="relative bg-white rounded-3xl p-6 sm:p-7 border border-[#E4E7EC] card-shadow">
                        
                        <!-- Top Meta Header -->
                        <div class="flex items-center justify-between border-b border-[#F1F3F6] pb-4 mb-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#171A21] text-white flex items-center justify-center font-bold text-xs">
                                    AU
                                </div>
                                <div class="leading-tight">
                                    <h4 class="text-xs font-bold text-[#171A21]">Admin User</h4>
                                    <span class="text-[11px] text-[#687080]">/u/admin</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Active
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-base font-extrabold text-[#171A21]">30 Minute Strategy Session</h3>
                                <p class="text-xs text-[#687080] mt-0.5">Select a date and available time slot to schedule our meeting.</p>
                            </div>

                            <div class="flex items-center gap-4 text-[11px] font-semibold text-[#687080]">
                                <span class="flex items-center gap-1">⏱️ 30 Mins</span>
                                <span class="flex items-center gap-1">🌐 Google Meet</span>
                                <span class="flex items-center gap-1">🎁 Free</span>
                            </div>

                            <div class="pt-3 border-t border-[#F1F3F6]">
                                <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider block mb-2">Available Slots (Mon, Oct 24)</span>
                                
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" onclick="selectSlot(this)" class="slot-btn py-2.5 px-2 rounded-xl border border-[#E4E7EC] bg-white hover:border-[#171A21] text-xs font-bold text-[#171A21] transition-all text-center">
                                        09:00 AM
                                    </button>
                                    <button type="button" onclick="selectSlot(this)" class="slot-btn py-2.5 px-2 rounded-xl border border-[#171A21] bg-[#171A21] text-white text-xs font-bold transition-all text-center relative shadow-sm">
                                        11:30 AM
                                        <span class="absolute -top-2 -right-1 text-[8px] bg-[#5B5BD6] text-white px-1 rounded font-bold">Pick</span>
                                    </button>
                                    <button type="button" onclick="selectSlot(this)" class="slot-btn py-2.5 px-2 rounded-xl border border-[#E4E7EC] bg-white hover:border-[#171A21] text-xs font-bold text-[#171A21] transition-all text-center">
                                        02:00 PM
                                    </button>
                                </div>
                            </div>

                            <!-- Live Feedback Notice -->
                            <div class="p-3 rounded-xl bg-[#F8FAFC] border border-[#E4E7EC] flex items-center justify-between text-xs">
                                <span class="text-[#687080] font-medium">Automatic confirmation link</span>
                                <strong class="text-emerald-600 font-bold">Instant Sync ✓</strong>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ================= HOW IT WORKS SECTION ================= -->
    <section id="how-it-works" class="py-20 lg:py-28 bg-white border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-bold tracking-widest uppercase text-[#5B5BD6]">HOW IT WORKS</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#171A21] tracking-tight">
                    Schedule in 3 simple steps
                </h2>
                <p class="text-base text-[#687080]">
                    Everything you need to automate your appointment booking flow.
                </p>
            </div>

            <!-- 3 Step Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 mb-12">
                
                <!-- Step 1 -->
                <div class="p-8 rounded-3xl bg-[#FAFAFC] border border-[#E4E7EC] card-shadow-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-xl bg-[#171A21] text-white flex items-center justify-center font-extrabold text-sm">
                            1
                        </div>
                        <span class="text-xl">🔄</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#171A21]">Connect your calendar</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Sync with Google Calendar to automatically read busy times and prevent double bookings.
                    </p>
                </div>

                <!-- Step 2 (Highlighted Active) -->
                <div class="p-8 rounded-3xl bg-white border-2 border-[#5B5BD6] card-shadow-hover space-y-4 relative shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-xl bg-[#5B5BD6] text-white flex items-center justify-center font-extrabold text-sm shadow-sm">
                            2
                        </div>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#5B5BD6] bg-[#EEF2FF] px-2 py-0.5 rounded-full">
                            • Active
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-[#171A21]">Set your availability</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Define your weekly working hours, breaks, and buffer times in your dashboard.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="p-8 rounded-3xl bg-[#FAFAFC] border border-[#E4E7EC] card-shadow-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-xl bg-[#171A21] text-white flex items-center justify-center font-extrabold text-sm">
                            3
                        </div>
                        <span class="text-xl">🔗</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#171A21]">Share your link</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Share your custom booking link <code class="text-[#5B5BD6] font-mono font-semibold">/u/username</code> and let clients schedule instantly.
                    </p>
                </div>

            </div>

            <!-- Interactive Live Preview Box beneath steps (Matching DayCal reference) -->
            <div class="max-w-3xl mx-auto rounded-3xl border border-[#E4E7EC] bg-white p-6 sm:p-8 card-shadow">
                <div class="flex items-center justify-between border-b border-[#F1F3F6] pb-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-bold text-[#687080] ml-2">Live Preview — Step 2</span>
                    </div>
                    <span class="text-xs font-bold text-[#5B5BD6] bg-[#EEF2FF] px-2.5 py-1 rounded-full">Interactive</span>
                </div>

                <div class="space-y-4">
                    <span class="text-xs font-bold text-[#171A21] block">Weekly Working Hours & Buffer Times</span>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div class="p-3 rounded-xl bg-[#171A21] text-white text-center text-xs font-bold">
                            9am - 5pm
                        </div>
                        <div class="p-3 rounded-xl bg-[#171A21] text-white text-center text-xs font-bold">
                            9am - 5pm
                        </div>
                        <div class="p-3 rounded-xl bg-[#171A21] text-white text-center text-xs font-bold">
                            9am - 5pm
                        </div>
                        <div class="p-3 rounded-xl bg-[#F1F3F6] text-[#687080] text-center text-xs font-bold">
                            Off
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-[#687080] pt-2">
                        <span class="flex items-center gap-1.5">
                            ⏱️ 15-min buffer time added between meetings
                        </span>
                        <strong class="text-emerald-600 font-bold">Auto-calculated</strong>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ================= FEATURES SECTION ================= -->
    <section id="features" class="py-20 lg:py-28 bg-[#FAFAFC] border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-bold tracking-widest uppercase text-[#5B5BD6]">FEATURES</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#171A21] tracking-tight">
                    Everything you need to schedule bookings
                </h2>
                <p class="text-base text-[#687080]">
                    Packed with premium features built for individuals and developers.
                </p>
            </div>

            <!-- 6 Grid Feature Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 mb-16">
                
                <!-- Feature 1 -->
                <div class="p-7 rounded-3xl bg-white border border-[#E4E7EC] card-shadow-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                            🗓️
                        </div>
                        <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">AUTOMATED</span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">Smart Weekly Schedules</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Configure flexible working hours and breaks for each day of the week.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('/register') ?>" class="text-xs font-bold text-[#5B5BD6] hover:underline flex items-center gap-1">
                            Click to explore <span>→</span>
                        </a>
                    </div>
                </div>

                <!-- Feature 2 (Spotlight Active Card) -->
                <div class="p-7 rounded-3xl bg-white border-2 border-[#5B5BD6] card-shadow-hover space-y-4 relative shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                            💳
                        </div>
                        <span class="text-[10px] font-bold text-[#5B5BD6] bg-[#EEF2FF] px-2 py-0.5 rounded-full uppercase tracking-wider">
                            STRIPE & RAZORPAY
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">Free & Paid Bookings</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Collect payments upfront using secure Stripe or Razorpay integrations.
                    </p>
                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-emerald-600 flex items-center gap-1">• Live Spotlight Active</span>
                        <span class="text-[#5B5BD6] font-bold">→</span>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="p-7 rounded-3xl bg-white border border-[#E4E7EC] card-shadow-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                            📝
                        </div>
                        <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">CUSTOM FIELDS</span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">Custom Booking Forms</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Build custom questions for your attendees with our form field builder.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('/register') ?>" class="text-xs font-bold text-[#5B5BD6] hover:underline flex items-center gap-1">
                            Click to explore <span>→</span>
                        </a>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="p-7 rounded-3xl bg-white border border-[#E4E7EC] card-shadow-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold text-lg">
                            🔄
                        </div>
                        <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">GOOGLE CALENDAR</span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">Google Calendar Sync</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Two-way calendar synchronization. Automatic events and double-booking prevention.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('/register') ?>" class="text-xs font-bold text-[#5B5BD6] hover:underline flex items-center gap-1">
                            Click to explore <span>→</span>
                        </a>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="p-7 rounded-3xl bg-white border border-[#E4E7EC] card-shadow-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg">
                            🛡️
                        </div>
                        <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">SUPER ADMIN</span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">Super Admin Panel</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Manage users, custom domains, plans, and global SMTP mail settings.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('/register') ?>" class="text-xs font-bold text-[#5B5BD6] hover:underline flex items-center gap-1">
                            Click to explore <span>→</span>
                        </a>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="p-7 rounded-3xl bg-white border border-[#E4E7EC] card-shadow-hover space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                            &lt;/&gt;
                        </div>
                        <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">V1 REST API</span>
                    </div>
                    <h3 class="text-base font-bold text-[#171A21]">REST API (v1)</h3>
                    <p class="text-xs text-[#687080] leading-relaxed">
                        Secure bearer-token endpoints for mobile apps or external integrations.
                    </p>
                    <div class="pt-2">
                        <a href="<?= url('/register') ?>" class="text-xs font-bold text-[#5B5BD6] hover:underline flex items-center gap-1">
                            Click to explore <span>→</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Spotlight Feature Banner (Matching Reference Dark Banner) -->
            <div class="rounded-3xl bg-[#0D0F14] text-white p-6 sm:p-8 card-shadow">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5 mb-6">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded-lg bg-[#5B5BD6] text-white text-xs font-extrabold">#2</span>
                        <div>
                            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-400 block">SPOTLIGHT FEATURE</span>
                            <h4 class="text-base font-bold text-white">Free & Paid Bookings</h4>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                        Built for Scale
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">INTEGRATION</span>
                        <p class="font-bold text-white">Seamless & Fast</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">AVAILABILITY</span>
                        <p class="font-bold text-white">24/7 Automation</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">SECURITY</span>
                        <p class="font-bold text-white">OAuth 2.0 Encrypted</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ================= PRICING SECTION ================= -->
    <section id="pricing" class="py-20 lg:py-28 bg-white border-b border-[#E4E7EC]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <span class="text-xs font-bold tracking-widest uppercase text-[#5B5BD6]">FLEXIBLE TIERS</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#171A21] tracking-tight">
                    Pick the plan that fits your schedule
                </h2>
                <p class="text-base text-[#687080]">
                    Simple, transparent pricing for individuals, teams, and organizations.
                </p>
            </div>

            <!-- 4 Pricing Columns Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 items-stretch mb-12">
                
                <!-- 1. Free Tier -->
                <div class="flex flex-col justify-between p-7 rounded-3xl bg-[#FAFAFC] border border-[#E4E7EC] card-shadow-hover">
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
                            Everything you need for individual appointment scheduling.
                        </p>
                        
                        <div class="pt-4 border-t border-[#E4E7EC] space-y-2.5 text-xs text-[#687080]">
                            <span class="text-[10px] font-bold text-[#171A21] uppercase tracking-wider block mb-2">FEATURES:</span>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>1 user account & custom link (/u/username)</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>1 event type maximum</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Weekly working hours & availability manager</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Custom booking form builder</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Bilingual support (English & Bengali)</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <a href="<?= url('/register') ?>" class="w-full py-2.5 px-4 rounded-xl bg-[#171A21] hover:bg-black text-white text-xs font-bold transition-all text-center block shadow-sm">
                            Try for free →
                        </a>
                    </div>
                </div>

                <!-- 2. Growth Tier -->
                <div class="flex flex-col justify-between p-7 rounded-3xl bg-white border border-[#E4E7EC] card-shadow-hover relative shadow-sm">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#171A21]">Growth</h3>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">POPULAR</span>
                        </div>
                        <div>
                            <span class="text-3xl font-extrabold text-[#171A21]">₹749</span>
                            <span class="text-xs text-[#687080]"> / per month</span>
                        </div>
                        <p class="text-xs text-[#687080]">
                            For active hosts needing calendar sync and paid appointments.
                        </p>
                        
                        <div class="pt-4 border-t border-[#E4E7EC] space-y-2.5 text-xs text-[#687080]">
                            <span class="text-[10px] font-bold text-[#171A21] uppercase tracking-wider block mb-2">FEATURES:</span>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <strong>Includes all Free features</strong>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Google Calendar synchronization</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Accept paid bookings (Razorpay & Stripe)</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Custom booking form response logs</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Email & SMTP notification delivery</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Team of 5 is allowed</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <a href="<?= url('/register') ?>" class="w-full py-2.5 px-4 rounded-xl border border-[#171A21] hover:bg-[#171A21] hover:text-white text-[#171A21] text-xs font-bold transition-all text-center block">
                            Upgrade →
                        </a>
                    </div>
                </div>

                <!-- 3. Pro Tier (HIGHLIGHTED DARK CARD MATCHING REFERENCE) -->
                <div class="flex flex-col justify-between p-7 rounded-3xl bg-[#0D0F14] text-white border-2 border-[#5B5BD6] card-shadow-hover relative shadow-xl">
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
                            Full access to API endpoints and advanced scheduling controls.
                        </p>
                        
                        <div class="pt-4 border-t border-slate-800 space-y-2.5 text-xs text-slate-300">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">FEATURES:</span>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <strong class="text-white">Includes all Growth features</strong>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>REST API (v1) access with Bearer Token</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Mobile app integration support</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Priority appointment slot generator</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Detailed revenue & booking analytics</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Team of 10 is allowed</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <a href="<?= url('/register') ?>" class="w-full py-2.5 px-4 rounded-xl bg-white hover:bg-slate-100 text-[#0D0F14] text-xs font-bold transition-all text-center block shadow-md">
                            Upgrade →
                        </a>
                    </div>
                </div>

                <!-- 4. Super Admin Tier -->
                <div class="flex flex-col justify-between p-7 rounded-3xl bg-[#FAFAFC] border border-[#E4E7EC] card-shadow-hover">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#171A21]">Super Admin</h3>
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-900 text-white">FULL SYSTEM CONTROL</span>
                        </div>
                        <div>
                            <span class="text-2xl font-extrabold text-[#171A21]">Custom</span>
                            <span class="text-xs text-[#687080]"> / platform license</span>
                        </div>
                        <p class="text-xs text-[#687080]">
                            Complete system control and administration features.
                        </p>
                        
                        <div class="pt-4 border-t border-[#E4E7EC] space-y-2.5 text-xs text-[#687080]">
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Dedicated Super Admin Control Panel</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Global user account management & status controls</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Global appointments log across all hosts</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Custom site title & SMTP host configurations</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Full database & server control</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-500 font-bold">✓</span>
                                <span>Unlimited team members allowed</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <a href="<?= url('/login') ?>" class="w-full py-2.5 px-4 rounded-xl bg-[#171A21] hover:bg-black text-white text-xs font-bold transition-all text-center block shadow-sm">
                            Contact →
                        </a>
                    </div>
                </div>

            </div>

            <!-- Lifetime Deal Card (Matching Page 3 of Reference) -->
            <div class="max-w-md p-7 rounded-3xl bg-[#0D0F14] text-white border-2 border-[#5B5BD6] card-shadow-hover relative shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white">Lifetime</h3>
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold bg-[#5B5BD6] text-white uppercase">LIFETIME</span>
                </div>
                
                <div class="mb-2">
                    <span class="text-3xl font-extrabold text-white">$25</span>
                    <span class="text-xs text-slate-400"> / lifetime</span>
                </div>
                
                <p class="text-xs text-slate-300 mb-5">
                    One-time payment — lifetime access to every feature.
                </p>

                <div class="space-y-2.5 text-xs text-slate-300 border-t border-slate-800 pt-4 mb-6">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">FEATURES:</span>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <strong class="text-white">Everything in all plans</strong>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>Unlimited event types & bookings</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>Built-in video calls</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>All future updates</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold">✓</span>
                        <span>No subscription, ever</span>
                    </div>
                </div>

                <a href="<?= url('/register') ?>" class="w-full py-2.5 px-4 rounded-xl bg-white hover:bg-slate-100 text-[#0D0F14] text-xs font-bold transition-all text-center block shadow-md">
                    Get lifetime access →
                </a>
            </div>

        </div>
    </section>

    <!-- ================= FAQ SECTION ================= -->
    <section id="faq" class="py-20 lg:py-28 bg-[#FAFAFC] border-b border-[#E4E7EC]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Section Header -->
            <div class="text-center mb-16 space-y-3">
                <span class="text-xs font-bold tracking-widest uppercase text-[#5B5BD6]">FAQ</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#171A21] tracking-tight">
                    Frequently asked questions
                </h2>
            </div>

            <!-- Accordion List (Matching Reference FAQ design) -->
            <div class="space-y-3">
                
                <!-- FAQ Item 1 (Open by default) -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Is QFunnel completely free to use?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▲</span>
                    </button>
                    <div class="faq-body px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Yes! QFunnel Free tier includes 1 active funnel, 100 submissions, custom booking links, availability management, and custom form builders completely free.
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>How does Google Calendar synchronization work?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Two-way calendar sync allows QFunnel to automatically check your busy events on Google Calendar and book verified slots without double booking.
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Can I accept payments for appointments?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Yes, on Growth and Pro plans you can connect Stripe or Razorpay to collect payments upfront before an appointment slot is finalized.
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Which languages does QFunnel support?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        QFunnel comes with built-in bilingual support for English (ENG) and Bengali (BN), with more languages being added continually.
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Do I need to install any software to use QFunnel?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        No installation required! QFunnel is 100% cloud-based and runs in any web browser on desktop, tablet, and mobile.
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>What happens if an invitee needs to cancel or reschedule?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Every confirmation email contains secure 1-click links allowing invitees to reschedule or cancel appointments within the buffer window you configure.
                    </div>
                </div>

                <!-- FAQ Item 7 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Can I use QFunnel with my team?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Yes, Growth and Pro plans allow adding team members with granular permission roles (Member, Manager, or Admin).
                    </div>
                </div>

                <!-- FAQ Item 8 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Is it possible to customize the booking page?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        Yes! You can customize your brand logo, custom colors, welcome message, instructions, and trust badges under your workspace settings.
                    </div>
                </div>

                <!-- FAQ Item 9 -->
                <div class="rounded-2xl border border-[#E4E7EC] bg-white overflow-hidden card-shadow">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-bold text-sm text-[#171A21] hover:text-[#5B5BD6] transition-colors">
                        <span>Are my payments secure?</span>
                        <span class="faq-icon text-base text-[#687080] transition-transform">▼</span>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-[#687080] leading-relaxed border-t border-[#F1F3F6] pt-3">
                        All transactions are processed through Stripe or Razorpay with 256-bit SSL encryption. We never store credit card or banking information on our servers.
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ================= PRE-FOOTER CTA SECTION (Matching Page 4 of Reference) ================= -->
    <section class="py-24 bg-white text-center border-b border-[#E4E7EC]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-[#171A21] tracking-tight mb-3">
                Smarter, simpler scheduling
            </h2>
            <p class="text-sm sm:text-base text-[#687080] mb-8">
                Create your custom booking link in less than 2 minutes.
            </p>
            <a href="<?= url('/register') ?>" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-[#171A21] hover:bg-black text-white text-sm font-bold shadow-lg hover:shadow-xl transition-all">
                <span>Get started for free</span>
                <span>→</span>
            </a>
        </div>
    </section>

    <!-- ================= DARK FOOTER (Matching Page 4 of Reference) ================= -->
    <footer class="bg-[#0B0D12] text-slate-400 py-16 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
                
                <!-- Col 1: Brand & Status -->
                <div class="space-y-4 md:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#5B5BD6] text-white flex items-center justify-center font-bold text-base">
                            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                        <span class="text-base font-extrabold text-white tracking-tight">QFunnel</span>
                    </div>
                    
                    <p class="text-xs text-slate-400 leading-relaxed">
                        The modern, frictionless scheduling platform. Connect Google Calendar, collect payments, and automate booking flows effortlessly.
                    </p>

                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-[11px] font-semibold text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        All Systems Operational • 99.9% Uptime
                    </div>
                </div>

                <!-- Col 2: Product Links -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-3">PRODUCT</h5>
                    <div><a href="#how-it-works" class="hover:text-white transition-colors">How it works</a></div>
                    <div><a href="#features" class="hover:text-white transition-colors">Features & Tools</a></div>
                    <div><a href="#pricing" class="hover:text-white transition-colors">Pricing Plans</a></div>
                    <div><a href="#faq" class="hover:text-white transition-colors">FAQ & Help</a></div>
                    <div><a href="<?= url('/login') ?>" class="hover:text-white transition-colors">Sign in to Account</a></div>
                    <div><a href="<?= url('/register') ?>" class="text-[#5B5BD6] font-semibold hover:underline">Get started for free →</a></div>
                </div>

                <!-- Col 3: Trust & Legal -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-3">TRUST & LEGAL</h5>
                    <div><a href="#faq" class="hover:text-white transition-colors">Privacy Policy</a></div>
                    <div><a href="#faq" class="hover:text-white transition-colors">Terms of Service</a></div>
                    <div><a href="#faq" class="hover:text-white transition-colors">Cancellation & Refund</a></div>
                    <div><a href="#faq" class="hover:text-white transition-colors">Contact Us</a></div>
                    <div><a href="#faq" class="hover:text-white transition-colors">Google Limited Use</a></div>
                </div>

                <!-- Col 4: Official Contact -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-3">OFFICIAL CONTACT</h5>
                    <div class="flex items-start gap-2">
                        <span>👤</span>
                        <div>
                            <strong class="text-white block font-semibold">Dibyendu Kumar Khaskel</strong>
                            <span class="text-[10px] text-slate-500">Grievance & Support Officer</span>
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
                    Engineered with precision for global teams & creators.
                </div>
                <div class="flex items-center gap-2">
                    <span>Language:</span>
                    <div class="inline-flex bg-slate-900 border border-slate-800 rounded-full p-0.5 text-[11px] font-bold">
                        <button type="button" onclick="switchLang('en')" class="px-2 py-0.5 rounded-full bg-slate-800 text-white">ENG</button>
                        <button type="button" onclick="switchLang('bn')" class="px-2 py-0.5 rounded-full text-slate-400 hover:text-white">BN</button>
                    </div>
                </div>
            </div>

        </div>
    </footer>

    <!-- ================= JAVASCRIPT LOGIC ================= -->
    <script>
        // Slot selector demo interactivity
        function selectSlot(btn) {
            document.querySelectorAll('.slot-btn').forEach(b => {
                b.className = 'slot-btn py-2.5 px-2 rounded-xl border border-[#E4E7EC] bg-white hover:border-[#171A21] text-xs font-bold text-[#171A21] transition-all text-center';
                const badge = b.querySelector('span');
                if (badge) badge.remove();
            });
            btn.className = 'slot-btn py-2.5 px-2 rounded-xl border border-[#171A21] bg-[#171A21] text-white text-xs font-bold transition-all text-center relative shadow-sm';
            btn.innerHTML = btn.innerText + '<span class="absolute -top-2 -right-1 text-[8px] bg-[#5B5BD6] text-white px-1 rounded font-bold">Pick</span>';
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
        const i18n = {
            en: {
                heroBadge: 'Smart & Effortless Lead Qualification',
                heroHead1: 'The better way to',
                heroHead2: 'convert your visitors',
                heroHead3: 'into customers',
                heroSub: 'QFunnel simplifies appointment scheduling & lead generation. Share your custom quiz link, sync customer data, collect payments, and automate webhooks — seamlessly.',
                heroCta: 'Get started for free',
                heroDemo: 'View live quiz demo',
                trustFree: 'Free forever for individuals',
                trustCard: 'No credit card required'
            },
            bn: {
                heroBadge: 'স্মার্ট ও সহজ অ্যাপয়েন্টমেন্ট শিডিউলিং',
                heroHead1: 'আপনার ভিজিটরদের',
                heroHead2: 'গ্রাহকে রূপান্তর করার',
                heroHead3: 'সর্বোত্তম মাধ্যম',
                heroSub: 'QFunnel অ্যাপয়েন্টমেন্ট শিডিউলিং সহজ করে তোলে। আপনার কাস্টম কুইজ লিঙ্ক শেয়ার করুন, পেমেন্ট গ্রহণ করুন এবং ওয়েবহুক স্বয়ংক্রিয় করুন — কোনো ঝামেলা ছাড়াই।',
                heroCta: 'বিনামূল্যে শুরু করুন',
                heroDemo: 'লাইভ ডেমো দেখুন',
                trustFree: 'সবার জন্য আজীবন ফ্রি টিয়ার',
                trustCard: 'ক্রেডিট কার্ডের প্রয়োজন নেই'
            }
        };

        function switchLang(lang) {
            const data = i18n[lang] || i18n.en;
            document.getElementById('txtHeroBadge').textContent = data.heroBadge;
            document.getElementById('txtHeroHead1').textContent = data.heroHead1;
            document.getElementById('txtHeroHead2').textContent = data.heroHead2;
            document.getElementById('txtHeroHead3').textContent = data.heroHead3;
            document.getElementById('txtHeroSub').textContent = data.heroSub;
            document.getElementById('txtHeroCta').textContent = data.heroCta;
            document.getElementById('txtHeroDemo').textContent = data.heroDemo;
            document.getElementById('txtTrustFree').textContent = data.trustFree;
            document.getElementById('txtTrustCard').textContent = data.trustCard;

            if (lang === 'bn') {
                document.getElementById('langBnBtn').className = 'px-2.5 py-1 rounded-full bg-[#171A21] text-white transition-all shadow-xs';
                document.getElementById('langEnBtn').className = 'px-2.5 py-1 rounded-full text-[#687080] hover:text-[#171A21] transition-all';
            } else {
                document.getElementById('langEnBtn').className = 'px-2.5 py-1 rounded-full bg-[#171A21] text-white transition-all shadow-xs';
                document.getElementById('langBnBtn').className = 'px-2.5 py-1 rounded-full text-[#687080] hover:text-[#171A21] transition-all';
            }
        }
    </script>
</body>
</html>
