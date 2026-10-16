<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Visual Funnel Builder') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: #F7F8FA;
            color: #171A21;
            user-select: none;
            overflow: hidden;
        }
        .canvas-grid {
            background-image: radial-gradient(#D5D9E2 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .node-card {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px 0 rgba(0, 0, 0, 0.04);
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
            cursor: default !important;
        }
        .node-card * {
            cursor: default;
        }
        .node-card .node-header {
            cursor: move !important;
        }
        .node-card .port-handle {
            cursor: crosshair !important;
        }
        .node-card button, .node-card a {
            cursor: pointer !important;
        }
        .node-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .node-card.selected {
            border-color: #5B5BD6 !important;
            box-shadow: 0 0 0 2px rgba(91, 91, 214, 0.3), 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .wire-path {
            stroke: #8E95A5;
            stroke-width: 2.5;
            fill: none;
            transition: stroke 0.15s ease;
        }
        .wire-path:hover {
            stroke: #5B5BD6;
            stroke-width: 3.5;
            cursor: pointer;
        }
        .wire-path.selected {
            stroke: #5B5BD6;
            stroke-width: 3.5;
        }
        .port-handle {
            transition: transform 0.15s ease, background-color 0.15s ease;
        }
        .port-handle:hover {
            transform: scale(1.3);
            background-color: #5B5BD6;
        }
        .wire-path {
            stroke: #8E95A5;
            stroke-width: 2.5;
            fill: none;
            transition: stroke 0.15s ease, stroke-width 0.15s ease;
        }
        .wire-path:hover {
            stroke: #5B5BD6 !important;
            stroke-width: 4 !important;
            cursor: pointer;
            filter: drop-shadow(0 0 4px rgba(91, 91, 214, 0.4));
        }
        .wire-path.selected {
            stroke: #5B5BD6 !important;
            stroke-width: 4 !important;
            filter: drop-shadow(0 0 6px rgba(91, 91, 214, 0.5));
        }
        .wire-delete-btn {
            cursor: pointer;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }
        .wire-delete-btn:hover {
            transform: scale(1.2);
        }
        .palette-card {
            cursor: grab;
            transition: all 0.15s ease;
        }
        .palette-card:active {
            cursor: grabbing;
        }
        .palette-card:hover {
            transform: translateY(-1px);
        }
        .canvas-drag-over {
            background-color: rgba(91, 91, 214, 0.04) !important;
            outline: 2px dashed #5B5BD6;
            outline-offset: -4px;
        }
        #toastContainer {
            transition: opacity 0.25s ease, transform 0.25s ease;
        }
    </style>
</head>
<body class="h-screen w-screen flex flex-col bg-[#F7F8FA]">

    <!-- Top Workspace Header -->
    <header class="h-14 bg-white border-b border-[#E4E7EC] px-4 flex items-center justify-between z-20 shrink-0">
        <div class="flex items-center gap-3">
            <a href="<?= url('/admin/funnels') ?>" class="p-1.5 rounded-lg text-[#687080] hover:text-[#171A21] hover:bg-[#F1F3F6]" title="Back to Funnels">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div class="h-4 w-px bg-[#E4E7EC]"></div>
            <div>
                <h1 class="text-xs font-bold text-[#171A21] truncate max-w-xs"><?= e($funnel['name']) ?></h1>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-[#687080]">/quiz/<?= e($funnel['slug']) ?></span>
                    <button type="button" onclick="copyFunnelUrl()" class="text-[#687080] hover:text-[#5B5BD6] transition-colors p-0.5 rounded hover:bg-slate-100" title="Copy Public Funnel URL">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                    <?php if ($funnel['status'] === 'published'): ?>
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Published (v<?= (int)$funnel['current_published_version_id'] ?>)</span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Draft</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Center: Save Status & Actions -->
        <div class="flex items-center gap-4">
            <div id="saveStatusIndicator" class="flex items-center gap-1.5 text-xs text-[#687080]">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Saved</span>
            </div>

            <div class="flex items-center bg-[#F1F3F6] rounded-lg p-0.5 border border-[#E4E7EC]">
                <button type="button" onclick="zoomCanvas(0.85)" class="px-2 py-1 text-xs text-[#687080] hover:text-[#171A21] rounded" title="Zoom Out (−)">−</button>
                <button type="button" onclick="resetZoom()" class="px-2 py-1 text-xs text-[#687080] hover:text-[#171A21] rounded font-mono" id="zoomLevelText">100%</button>
                <button type="button" onclick="zoomCanvas(1.15)" class="px-2 py-1 text-xs text-[#687080] hover:text-[#171A21] rounded" title="Zoom In (+)">+</button>
                <button type="button" onclick="centerCanvas()" class="px-2 py-1 text-xs text-[#687080] hover:text-[#171A21] rounded ml-1" title="Center Canvas (Ctrl+0)">Fit</button>
                <button type="button" onclick="autoArrangeNodes()" class="px-2 py-1 text-xs text-[#5B5BD6] font-semibold hover:bg-indigo-50 rounded ml-1 flex items-center gap-1 transition-colors" title="Auto-arrange canvas tree layout (L)">
                    <span>✨</span>
                    <span>Auto Layout</span>
                </button>
            </div>
        </div>

        <!-- Right: Flow Validation, Preview, Publish -->
        <div class="flex items-center gap-2">
            <button type="button" onclick="openValidationDrawer()" id="validateBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E4E7EC] bg-white text-xs font-semibold text-[#171A21] hover:bg-[#F7F8FA] transition-colors" title="Shortcut: V">
                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Validate Flow
            </button>

            <button type="button" onclick="openPreviewModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E4E7EC] bg-white text-xs font-semibold text-[#171A21] hover:bg-[#F7F8FA] transition-colors" title="Shortcut: P">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Preview Quiz
            </button>

            <button type="button" onclick="saveGraphNow()" id="saveBtn" class="px-3 py-1.5 rounded-lg border border-[#E4E7EC] bg-white text-xs font-semibold text-[#171A21] hover:bg-[#F7F8FA]" title="Shortcut: Ctrl+S">
                Save
            </button>

            <button type="button" onclick="publishFunnel()" id="publishBtn" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-[#5B5BD6] text-white text-xs font-semibold shadow hover:bg-[#4B4BC0] transition-colors">
                Publish Version
            </button>
        </div>
    </header>

    <!-- 3-Column Main Workspace Layout -->
    <div class="flex-1 flex overflow-hidden relative">

        <!-- Left Node Palette (Zone 1) -->
        <aside id="leftSidebar" class="w-60 bg-white border-r border-[#E4E7EC] flex flex-col z-10 shrink-0 transition-all duration-200">
            <div class="p-3 border-b border-[#E4E7EC] flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#171A21]">Node Palette</h2>
                    <p class="text-[11px] text-[#687080] mt-0.5">Drag to canvas or click to add</p>
                </div>
                <button type="button" onclick="toggleLeftSidebar()" class="p-1 rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" title="Collapse Palette ([)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
            </div>

            <div class="p-3 space-y-2.5 overflow-y-auto flex-1">

                <!-- Question Node -->
                <div draggable="true" ondragstart="handlePaletteDragStart(event, 'question')" onclick="addNodeToCanvas('question')" class="palette-card w-full p-2.5 rounded-xl border border-[#E4E7EC] bg-white hover:border-indigo-400 hover:bg-indigo-50/40 text-left transition-all flex items-start gap-2.5 shadow-xs select-none group">
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-110 transition-transform">
                        ?
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="block text-xs font-semibold text-[#171A21]">Question Node</span>
                            <kbd class="text-[9px] font-mono font-bold bg-indigo-50 text-[#5B5BD6] px-1 rounded border border-indigo-100">Q</kbd>
                        </div>
                        <span class="block text-[10px] text-[#687080] leading-tight mt-0.5">Branching options & rules</span>
                    </div>
                </div>

                <!-- Form Node (Lead Capture) -->
                <div draggable="true" ondragstart="handlePaletteDragStart(event, 'form')" onclick="addNodeToCanvas('form')" class="palette-card w-full p-2.5 rounded-xl border border-[#E4E7EC] bg-white hover:border-amber-400 hover:bg-amber-50/40 text-left transition-all flex items-start gap-2.5 shadow-xs select-none group">
                    <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-110 transition-transform">
                        📋
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="block text-xs font-semibold text-[#171A21]">Form Node</span>
                            <kbd class="text-[9px] font-mono font-bold bg-amber-50 text-amber-700 px-1 rounded border border-amber-100">F</kbd>
                        </div>
                        <span class="block text-[10px] text-[#687080] leading-tight mt-0.5">Lead capture & custom data</span>
                    </div>
                </div>

                <!-- Destination Node -->
                <div draggable="true" ondragstart="handlePaletteDragStart(event, 'destination')" onclick="addNodeToCanvas('destination')" class="palette-card w-full p-2.5 rounded-xl border border-[#E4E7EC] bg-white hover:border-purple-400 hover:bg-purple-50/40 text-left transition-all flex items-start gap-2.5 shadow-xs select-none group">
                    <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-110 transition-transform">
                        ⚐
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="block text-xs font-semibold text-[#171A21]">Destination Node</span>
                            <kbd class="text-[9px] font-mono font-bold bg-purple-50 text-purple-700 px-1 rounded border border-purple-100">D</kbd>
                        </div>
                        <span class="block text-[10px] text-[#687080] leading-tight mt-0.5">Terminal landing page URL</span>
                    </div>
                </div>

                <!-- Quick Help / Workflow Tip -->
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 text-[11px] text-[#687080] space-y-1">
                    <div class="flex items-center gap-1 font-semibold text-[#171A21] text-[10px] uppercase tracking-wider">
                        <span>💡 Flow Strategy</span>
                    </div>
                    <p class="leading-relaxed text-[10px]">
                        Place a Form Node anywhere: at the start, mid-flow, or at the end to capture contact info.
                    </p>
                </div>
            </div>

            <!-- Collapsible Shortcuts / Hotkeys Accordion (Collapsed by default) -->
            <div class="border-t border-[#E4E7EC] bg-[#F7F8FA]">
                <button type="button" onclick="toggleHotkeysAccordion()" class="w-full px-3 py-2 flex items-center justify-between text-[11px] font-semibold text-[#171A21] hover:bg-slate-100 transition-colors">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#5B5BD6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span>Shortcuts Guide</span>
                    </div>
                    <svg id="hotkeysChevron" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200 transform rotate-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div id="hotkeysContent" class="hidden px-3 pb-3 space-y-1 text-[10px] text-[#485060]">
                    <div class="flex items-center justify-between"><span class="font-medium">Save</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">Ctrl+S</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Auto Layout</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">L</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Delete Wire/Node</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">Delete</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Move Node / Pan</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">↑ ↓ ← →</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Toggle Sidebars</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">[ / ]</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Deselect / Cancel</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">Esc</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Zoom In / Out</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">+ / -</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Add Question</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">Q</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Add Form Node</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">F</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Add Destination</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">D</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Live Preview</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">P</kbd></div>
                    <div class="flex items-center justify-between"><span class="font-medium">Validate Flow</span><kbd class="px-1.5 py-0.5 bg-white border border-[#D0D5DD] rounded text-[9px] font-mono shadow-xs">V</kbd></div>
                </div>
            </div>
        </aside>

        <!-- Central Infinite Pan/Zoom Canvas (Zone 2) -->
        <main id="canvasViewport" ondragover="handleCanvasDragOver(event)" ondragleave="handleCanvasDragLeave(event)" ondrop="handleCanvasDrop(event)" class="flex-1 relative overflow-hidden canvas-grid cursor-grab active:cursor-grabbing">
            <!-- Floating Expand Buttons when sidebars are collapsed -->
            <button id="leftSidebarExpandBtn" type="button" onclick="toggleLeftSidebar()" class="hidden absolute left-3 top-3 z-30 px-2.5 py-1.5 rounded-lg bg-white border border-[#E4E7EC] shadow-md hover:bg-[#F7F8FA] text-xs font-semibold text-[#171A21] flex items-center gap-1.5 transition-all cursor-pointer" title="Expand Node Palette ([)">
                <svg class="w-3.5 h-3.5 text-[#5B5BD6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Palette</span>
            </button>

            <button id="inspectorExpandBtn" type="button" onclick="toggleInspector()" class="hidden absolute right-3 top-3 z-30 px-2.5 py-1.5 rounded-lg bg-white border border-[#E4E7EC] shadow-md hover:bg-[#F7F8FA] text-xs font-semibold text-[#171A21] flex items-center gap-1.5 transition-all cursor-pointer" title="Expand Inspector (])">
                <span>Inspector</span>
                <svg class="w-3.5 h-3.5 text-[#5B5BD6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Floating Top Notification Toast -->
            <div id="toastContainer" class="pointer-events-none absolute top-4 left-1/2 transform -translate-x-1/2 z-40 opacity-0 -translate-y-2 transition-all duration-200">
                <div id="toastMessage" class="px-4 py-2 rounded-xl bg-slate-900/90 backdrop-blur-xs text-white text-xs font-medium shadow-lg flex items-center gap-2 border border-slate-700">
                    <span id="toastIcon">✨</span>
                    <span id="toastText">Action completed</span>
                </div>
            </div>

            <!-- Transform Container -->
            <div id="canvasSurface" class="absolute inset-0 origin-top-left" style="transform: translate(0px, 0px) scale(1);">
                <!-- SVG Wire Layer -->
                <svg id="svgLayer" class="absolute top-0 left-0 w-[5000px] h-[5000px] pointer-events-none" style="z-index: 1;">
                    <defs>
                        <marker id="arrow" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 10 5 L 0 9 z" fill="#8E95A5" />
                        </marker>
                        <marker id="arrow-selected" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 10 5 L 0 9 z" fill="#5B5BD6" />
                        </marker>
                    </defs>
                    <g id="wiresGroup"></g>
                    <!-- Temporary wire while dragging -->
                    <path id="tempWire" class="wire-path stroke-indigo-400 stroke-dashed hidden" stroke-dasharray="4" d="" />
                </svg>

                <!-- HTML Nodes Layer -->
                <div id="nodesContainer" class="absolute top-0 left-0 w-[5000px] h-[5000px] pointer-events-auto" style="z-index: 2;">
                    <!-- Rendered dynamically via JS -->
                </div>
            </div>

            <!-- Floating Interactive Mini-Map in bottom-right -->
            <div id="minimapContainer" class="absolute right-4 bottom-4 z-20 bg-white/95 backdrop-blur-sm border border-[#E4E7EC] rounded-xl shadow-lg overflow-hidden flex flex-col transition-all duration-200">
                <div class="px-2.5 py-1.5 border-b border-[#E4E7EC] bg-slate-50/80 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#5B5BD6]"></span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#687080]">Minimap</span>
                    </div>
                    <button type="button" onclick="toggleMinimap()" class="text-[10px] text-slate-400 hover:text-slate-700 p-0.5 leading-none" title="Hide Minimap">✕</button>
                </div>
                <div id="minimapViewport" class="w-44 h-28 relative bg-[#F7F8FA] overflow-hidden cursor-crosshair">
                    <canvas id="minimapCanvas" width="176" height="112" class="w-full h-full block"></canvas>
                    <div id="minimapViewBox" class="absolute border-2 border-[#5B5BD6] bg-indigo-500/10 pointer-events-none rounded-xs"></div>
                </div>
            </div>

            <!-- Mini-map Re-open button if minimized -->
            <button id="minimapOpenBtn" type="button" onclick="toggleMinimap()" class="hidden absolute right-4 bottom-4 z-20 px-2.5 py-1.5 rounded-lg bg-white border border-[#E4E7EC] shadow-md hover:bg-[#F7F8FA] text-[11px] font-semibold text-[#171A21] flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-[#5B5BD6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span>Minimap</span>
            </button>
        </main>

        <!-- Right Inspector Panel (Zone 3) -->
        <aside id="inspectorPanel" class="w-80 bg-white border-l border-[#E4E7EC] flex flex-col z-10 shrink-0 shadow-sm transition-all duration-200">
            <div class="p-3.5 border-b border-[#E4E7EC] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#171A21]">Node Inspector</h2>
                    <span id="inspectorNodeTypeBadge" class="text-[10px] font-mono px-2 py-0.5 rounded bg-indigo-50 text-[#5B5BD6] font-semibold uppercase">Overview</span>
                </div>
                <button type="button" onclick="toggleInspector()" class="p-1 rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" title="Collapse Inspector (Right Sidebar)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <div id="inspectorContent" class="p-4 space-y-4 overflow-y-auto flex-1 text-xs">
                <!-- Dynamically populated via JS: Funnel Overview when unselected, or Node/Wire properties when selected -->
            </div>
        </aside>

    </div>

    <!-- Validation Drawer -->
    <div id="validationDrawer" class="fixed inset-y-0 right-0 w-96 bg-white border-l border-[#E4E7EC] shadow-2xl z-50 transform translate-x-full transition-transform duration-200 flex flex-col">
        <div class="p-4 border-b border-[#E4E7EC] flex items-center justify-between bg-[#F7F8FA]">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 class="text-sm font-bold text-[#171A21]">Flow Validation</h3>
            </div>
            <button onclick="closeValidationDrawer()" class="text-slate-400 hover:text-slate-700 p-1">✕</button>
        </div>
        <div id="validationResults" class="p-4 overflow-y-auto flex-1 space-y-3 text-xs">
            <p class="text-[#687080]">Running validation checks...</p>
        </div>
    </div>

    <!-- Interactive Live Preview Modal -->
    <div id="previewModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl border border-[#E4E7EC] max-w-lg w-full shadow-2xl flex flex-col overflow-hidden max-h-[90vh]">
            <div class="px-5 py-4 border-b border-[#E4E7EC] flex items-center justify-between bg-[#F7F8FA]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-bold text-[#171A21]">Quiz Runner Live Preview</span>
                </div>
                <button onclick="closePreviewModal()" class="text-slate-400 hover:text-slate-700 font-bold p-1">✕</button>
            </div>
            <div id="previewModalBody" class="p-6 overflow-y-auto flex-1">
                <!-- Interactive quiz rendered here -->
            </div>
        </div>
    </div>

    <!-- Beautified Custom Confirmation & Alert Modal -->
    <div id="customDialogModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div id="customDialogCard" class="bg-white rounded-2xl border border-slate-200 max-w-sm w-full shadow-2xl overflow-hidden p-6 space-y-4 transform scale-95 transition-all duration-150">
            <div class="flex items-start gap-3.5">
                <div id="customDialogIconContainer" class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg id="customDialogDangerIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <svg id="customDialogWarningIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <svg id="customDialogSuccessIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg id="customDialogInfoIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1">
                    <h4 id="customDialogTitle" class="text-sm font-bold text-[#171A21]">Confirmation</h4>
                    <p id="customDialogMessage" class="text-xs text-[#687080] mt-1 leading-relaxed">Are you sure?</p>
                </div>
            </div>
            <div id="customDialogActions" class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" id="customDialogCancelBtn" class="px-3.5 py-2 rounded-xl border border-[#D0D5DD] bg-white text-xs font-semibold text-[#344054] hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
                <button type="button" id="customDialogConfirmBtn" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-xs shadow-rose-200 transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Global Application Config for JS -->
    <script>
        window.FUNNEL_ID = <?= (int)$funnel['id'] ?>;
        window.CSRF_TOKEN = '<?= csrf_token() ?>';
        window.API_BASE = '<?= url('') ?>';
    </script>

    <!-- Visual Builder Engine -->
    <script src="<?= asset('/js/builder.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
