/**
 * Quiz Funnel SaaS — Visual Branching Logic Graph Builder
 * Vanilla JavaScript & SVG Interactive Engine
 */

(function () {
    'use strict';

    // State
    let graphData = {
        nodes: [],
        edges: []
    };
    let selectedNodeId = null;
    let selectedEdgeId = null;

    // Viewport transform
    let scale = 1.0;
    let panX = 40;
    let panY = 40;
    let isPanning = false;
    let startPanX = 0;
    let startPanY = 0;

    // Node dragging
    let draggedNode = null;
    let dragStartX = 0;
    let dragStartY = 0;
    let nodeStartX = 0;
    let nodeStartY = 0;

    // Wire connection dragging
    let connectingSource = null; // { nodeId, optionId, x, y }

    let isDirty = false;
    let autoSaveTimer = null;

    // DOM Elements
    const viewport = document.getElementById('canvasViewport');
    const surface = document.getElementById('canvasSurface');
    const nodesContainer = document.getElementById('nodesContainer');
    const svgLayer = document.getElementById('svgLayer');
    const wiresGroup = document.getElementById('wiresGroup');
    const tempWire = document.getElementById('tempWire');
    const inspectorContent = document.getElementById('inspectorContent');
    const inspectorBadge = document.getElementById('inspectorNodeTypeBadge');
    const zoomText = document.getElementById('zoomLevelText');
    const saveIndicator = document.getElementById('saveStatusIndicator');

    // Safe node lookup helper by ID or key (handles string vs number casting)
    function findNode(idOrKey) {
        if (idOrKey === null || idOrKey === undefined || idOrKey === '') return null;
        if (!graphData || !graphData.nodes) return null;
        return graphData.nodes.find(n => n.id == idOrKey || n.node_key === String(idOrKey)) || null;
    }

    // Init
    window.addEventListener('DOMContentLoaded', init);

    async function init() {
        setupCanvasEvents();
        setupKeyboardEvents();
        setupMinimapEvents();
        await loadGraph();
        centerCanvas();
        deselectAll();
    }

    async function loadGraph() {
        setSaveStatus('loading', 'Loading graph...');
        try {
            const resp = await fetch(`${window.API_BASE}/api/funnels/${window.FUNNEL_ID}/graph`);
            const json = await resp.json();
            if (json.success) {
                graphData = json.data;

                // Automatic migration for legacy 'start' nodes if any exist
                const legacyStart = (graphData.nodes || []).find(n => n.node_type === 'start');
                if (legacyStart) {
                    const outEdge = (graphData.edges || []).find(e => e.source_node_id == legacyStart.id);
                    if (outEdge) {
                        const tgtNode = (graphData.nodes || []).find(n => n.id == outEdge.target_node_id);
                        if (tgtNode) tgtNode.is_start = true;
                    }
                    graphData.nodes = graphData.nodes.filter(n => n.id != legacyStart.id);
                    graphData.edges = (graphData.edges || []).filter(e => e.source_node_id != legacyStart.id && e.target_node_id != legacyStart.id);
                    markDirty();
                }

                // Ensure at least one starting step
                if (!graphData.nodes.some(n => n.is_start)) {
                    const firstNonDest = graphData.nodes.find(n => n.node_type !== 'destination');
                    if (firstNonDest) firstNonDest.is_start = true;
                }

                renderCanvas();
                deselectAll();
                setSaveStatus('saved', 'Saved');
                updateMinimap();
            } else {
                setSaveStatus('error', 'Failed to load');
            }
        } catch (e) {
            console.error('Failed to load graph:', e);
            setSaveStatus('error', 'Connection error');
        }
    }

    function setupCanvasEvents() {
        // Pan with mouse drag on background
        viewport.addEventListener('mousedown', (e) => {
            if (e.target === viewport || e.target === surface || e.target.id === 'nodesContainer' || e.target.id === 'svgLayer') {
                isPanning = true;
                startPanX = e.clientX - panX;
                startPanY = e.clientY - panY;
                deselectAll();
            }
        });

        window.addEventListener('mousemove', (e) => {
            if (isPanning) {
                panX = e.clientX - startPanX;
                panY = e.clientY - startPanY;
                applyTransform();
            } else if (draggedNode) {
                const dx = (e.clientX - dragStartX) / scale;
                const dy = (e.clientY - dragStartY) / scale;
                draggedNode.position_x = Math.max(20, nodeStartX + dx);
                draggedNode.position_y = Math.max(20, nodeStartY + dy);
                
                const nodeEl = document.getElementById(`node-${draggedNode.id}`);
                if (nodeEl) {
                    nodeEl.style.left = `${draggedNode.position_x}px`;
                    nodeEl.style.top = `${draggedNode.position_y}px`;
                }
                renderWires();
                updateMinimap();
                markDirty();
            } else if (connectingSource) {
                const rect = surface.getBoundingClientRect();
                const mouseX = (e.clientX - rect.left) / scale;
                const mouseY = (e.clientY - rect.top) / scale;
                drawBezier(tempWire, connectingSource.x, connectingSource.y, mouseX, mouseY);
            }
        });

        window.addEventListener('mouseup', () => {
            if (isPanning) isPanning = false;
            if (draggedNode) draggedNode = null;
            if (connectingSource) {
                connectingSource = null;
                tempWire.classList.add('hidden');
                document.querySelectorAll('.node-card').forEach(c => c.classList.remove('ring-2', 'ring-[#5B5BD6]'));
            }
        });

        // Zoom with wheel
        viewport.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomFactor = e.deltaY < 0 ? 1.08 : 0.92;
            const newScale = Math.min(Math.max(scale * zoomFactor, 0.4), 2.2);

            const rect = viewport.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            panX = mouseX - (mouseX - panX) * (newScale / scale);
            panY = mouseY - (mouseY - panY) * (newScale / scale);
            scale = newScale;

            applyTransform();
        }, { passive: false });
    }

    function setupKeyboardEvents() {
        window.addEventListener('keydown', (e) => {
            const isInputFocused = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName) || document.activeElement?.isContentEditable;

            // 1. Ctrl+S or Cmd+S -> Save funnel immediately anywhere
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                saveGraphNow();
                return;
            }

            // 2. Escape -> Deselect, Cancel Drag, Close Modals/Drawers
            if (e.key === 'Escape') {
                e.preventDefault();
                if (isInputFocused) {
                    document.activeElement.blur();
                }
                const previewModal = document.getElementById('previewModal');
                if (previewModal && !previewModal.classList.contains('hidden')) {
                    closePreviewModal();
                    return;
                }
                const valDrawer = document.getElementById('validationDrawer');
                if (valDrawer && !valDrawer.classList.contains('translate-x-full')) {
                    closeValidationDrawer();
                    return;
                }
                if (connectingSource) {
                    connectingSource = null;
                    tempWire.classList.add('hidden');
                    document.querySelectorAll('.node-card').forEach(c => c.classList.remove('ring-2', 'ring-[#5B5BD6]'));
                    return;
                }
                deselectAll();
                return;
            }

            // Do not trigger canvas action shortcuts if user is typing in text fields
            if (isInputFocused) return;

            // 3. Delete key only (NOT Backspace) -> Delete selected wire or selected node
            if (e.key === 'Delete') {
                if (selectedEdgeId) {
                    e.preventDefault();
                    deleteSelectedEdge();
                } else if (selectedNodeId) {
                    e.preventDefault();
                    deleteNode(selectedNodeId);
                }
                return;
            }

            // 4. Arrow Keys -> Nudge Selected Node OR Pan Canvas
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
                const step = e.shiftKey ? 50 : 10;
                if (selectedNodeId) {
                    const node = graphData.nodes.find(n => n.id == selectedNodeId);
                    if (node) {
                        if (e.key === 'ArrowUp') node.position_y = Math.max(20, node.position_y - step);
                        if (e.key === 'ArrowDown') node.position_y += step;
                        if (e.key === 'ArrowLeft') node.position_x = Math.max(20, node.position_x - step);
                        if (e.key === 'ArrowRight') node.position_x += step;

                        const nodeEl = document.getElementById(`node-${node.id}`);
                        if (nodeEl) {
                            nodeEl.style.left = `${node.position_x}px`;
                            nodeEl.style.top = `${node.position_y}px`;
                        }
                        renderWires();
                        markDirty();
                    }
                } else {
                    // Pan canvas with keyboard
                    if (e.key === 'ArrowUp') panY += 40;
                    if (e.key === 'ArrowDown') panY -= 40;
                    if (e.key === 'ArrowLeft') panX += 40;
                    if (e.key === 'ArrowRight') panX -= 40;
                    applyTransform();
                }
                return;
            }

            // 5. Zoom (+ / - / Ctrl+0)
            if (e.key === '+' || e.key === '=') {
                e.preventDefault();
                zoomCanvas(1.15);
                return;
            }
            if (e.key === '-' || e.key === '_') {
                e.preventDefault();
                zoomCanvas(0.85);
                return;
            }
            if (e.key === '0' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                resetZoom();
                centerCanvas();
                return;
            }

            // 6. Quick Add Node: 'Q' for Question, 'F' for Form, 'D' for Destination
            if (e.key.toLowerCase() === 'q' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                addNodeToCanvas('question');
                return;
            }
            if (e.key.toLowerCase() === 'f' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                addNodeToCanvas('form');
                return;
            }
            if (e.key.toLowerCase() === 'd' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                addNodeToCanvas('destination');
                return;
            }
            if (e.key.toLowerCase() === 'l' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                autoArrangeNodes();
                return;
            }

            // 7. Toggle Sidebars: '[' for Left Sidebar, ']' for Right Inspector
            if (e.key === '[' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                toggleLeftSidebar();
                return;
            }
            if (e.key === ']' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                toggleInspector();
                return;
            }

            // 8. Modals: 'V' for Validate Flow, 'P' for Live Preview
            if (e.key.toLowerCase() === 'v' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                const drawer = document.getElementById('validationDrawer');
                if (drawer && !drawer.classList.contains('translate-x-full')) {
                    closeValidationDrawer();
                } else {
                    openValidationDrawer();
                }
                return;
            }
            if (e.key.toLowerCase() === 'p' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                const modal = document.getElementById('previewModal');
                if (modal && !modal.classList.contains('hidden')) {
                    closePreviewModal();
                } else {
                    openPreviewModal();
                }
                return;
            }
        });
    }

    function applyTransform() {
        surface.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
        zoomText.textContent = `${Math.round(scale * 100)}%`;
        updateMinimap();
    }

    window.zoomCanvas = function (deltaFactor) {
        scale = Math.min(Math.max(scale * deltaFactor, 0.4), 2.2);
        applyTransform();
    };

    window.resetZoom = function () {
        scale = 1.0;
        applyTransform();
    };

    window.centerCanvas = function () {
        if (!graphData.nodes || graphData.nodes.length === 0) {
            panX = 60;
            panY = 60;
            scale = 1.0;
            applyTransform();
            return;
        }

        let minX = Infinity, minY = Infinity;
        graphData.nodes.forEach(n => {
            if (n.position_x < minX) minX = n.position_x;
            if (n.position_y < minY) minY = n.position_y;
        });

        panX = Math.max(40, 100 - minX * scale);
        panY = Math.max(40, 100 - minY * scale);
        applyTransform();
    };

    // Rendering Canvas Elements
    function renderCanvas() {
        nodesContainer.innerHTML = '';
        graphData.nodes.forEach(renderNodeElement);
        renderWires();
        updateMinimap();
    }

    function renderNodeElement(node) {
        const div = document.createElement('div');
        div.id = `node-${node.id}`;
        div.className = `absolute node-card w-64 rounded-xl bg-white border border-[#E4E7EC] text-xs select-none cursor-default ${selectedNodeId === node.id ? 'selected' : ''}`;
        div.style.left = `${node.position_x}px`;
        div.style.top = `${node.position_y}px`;

        let headerColor = 'bg-indigo-50 border-indigo-100 text-indigo-700';
        let badgeText = 'Question';
        let icon = '?';

        if (node.node_type === 'start') {
            headerColor = 'bg-emerald-50 border-emerald-100 text-emerald-700';
            badgeText = 'Start';
            icon = '▶';
        } else if (node.node_type === 'destination') {
            headerColor = 'bg-purple-50 border-purple-100 text-purple-700';
            badgeText = 'Destination';
            icon = '⚐';
        } else if (node.node_type === 'form') {
            headerColor = 'bg-sky-50 border-sky-100 text-sky-700';
            badgeText = 'Form';
            icon = '📋';
        }

        let bodyHtml = '';
        let statusBadgeHtml = '';

        if (node.node_type === 'start') {
            const hasStartEdge = (graphData.edges || []).some(e => e.source_node_id == node.id);
            statusBadgeHtml = hasStartEdge
                ? `<span class="text-[9px] font-semibold text-emerald-700 bg-emerald-100/90 px-1.5 py-0.5 rounded border border-emerald-200">✓ Linked</span>`
                : `<span class="text-[9px] font-semibold text-amber-700 bg-amber-100/90 px-1.5 py-0.5 rounded border border-amber-200">⚠️ Needs Route</span>`;

            bodyHtml = `
                <div class="p-3 text-[11px] text-[#687080] leading-relaxed">
                    Public Entry Point. Connects visitors to the first question in the funnel.
                </div>
                <div class="px-3 py-2.5 bg-[#F7F8FA] border-t border-[#E4E7EC] flex items-center justify-between text-[11px] rounded-b-xl relative">
                    <span class="font-medium text-[#171A21] flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Proceed to flow</span>
                    </span>
                    <div class="port-handle w-3.5 h-3.5 rounded-full ${hasStartEdge ? 'bg-emerald-500' : 'bg-amber-400'} border-2 border-white shadow cursor-crosshair absolute -right-1.5 top-3"
                         data-port-type="output" data-node-id="${node.id}" data-option-id="null" title="${hasStartEdge ? 'Connected to first step' : 'Drag to connect first question'}"></div>
                </div>
            `;
        } else if (node.node_type === 'question') {
            const q = node.question || { question_text: 'Untitled Question', options: [] };
            const options = q.options || [];

            const unmappedCount = options.filter(opt => !(graphData.edges || []).some(e => e.source_node_id == node.id && e.source_option_id == opt.id)).length;
            if (unmappedCount > 0) {
                statusBadgeHtml = `<span class="text-[9px] font-semibold text-amber-700 bg-amber-100/90 px-1.5 py-0.5 rounded border border-amber-200" title="${unmappedCount} option(s) missing route">⚠️ ${unmappedCount} unmapped</span>`;
            } else if (options.length > 0) {
                statusBadgeHtml = `<span class="text-[9px] font-semibold text-emerald-700 bg-emerald-100/90 px-1.5 py-0.5 rounded border border-emerald-200">✓ Mapped</span>`;
            }

            bodyHtml = `
                <div class="p-3">
                    <p class="font-semibold text-[#171A21] line-clamp-2 leading-snug">${escapeHtml(q.question_text || 'Untitled Question')}</p>
                    <span class="text-[10px] text-[#687080] mt-1 block">${q.question_type || 'single_choice'} • ${options.length} options</span>
                </div>
                <div class="p-2 border-t border-[#E4E7EC] bg-[#F7F8FA] rounded-b-xl space-y-1.5">
            `;

            if (options.length === 0) {
                bodyHtml += `<div class="p-2 text-center text-[10px] text-rose-500 font-medium bg-rose-50 rounded-lg">No options configured!</div>`;
            } else {
                options.forEach(opt => {
                    const edge = (graphData.edges || []).find(e => e.source_node_id == node.id && e.source_option_id == opt.id);
                    const targetNode = edge ? graphData.nodes.find(n => n.id == edge.target_node_id) : null;
                    const targetLabel = targetNode ? `→ ${targetNode.title || targetNode.node_type}` : null;

                    bodyHtml += `
                        <div class="px-2.5 py-1.5 rounded-lg border ${edge ? 'border-slate-200 bg-white' : 'border-amber-200 bg-amber-50/40'} flex items-center justify-between text-[11px] relative group hover:border-[#5B5BD6] transition-all">
                            <div class="truncate max-w-[195px]">
                                <span class="block truncate font-medium text-[#171A21] leading-tight">${escapeHtml(opt.label)}</span>
                                ${targetLabel ? `<span class="block text-[9px] font-semibold text-indigo-600 truncate mt-0.5">${escapeHtml(targetLabel)}</span>` : `<span class="block text-[9px] font-semibold text-amber-600 mt-0.5">⚠️ Unlinked route</span>`}
                            </div>
                            <div class="port-handle w-3.5 h-3.5 rounded-full ${edge ? 'bg-[#5B5BD6]' : 'bg-amber-400'} border-2 border-white shadow cursor-crosshair absolute -right-1.5 top-3"
                                 data-port-type="output" data-node-id="${node.id}" data-option-id="${opt.id}" title="${edge ? `Connected: ${escapeHtml(targetLabel)}` : 'Missing route! Drag to target step'}"></div>
                        </div>
                    `;
                });
            }

            bodyHtml += `</div>`;
        } else if (node.node_type === 'destination') {
            const dest = node.destination || { name: 'Landing Page', url: 'https://example.com' };
            const incomingCount = (graphData.edges || []).filter(e => e.target_node_id == node.id).length;
            statusBadgeHtml = incomingCount > 0
                ? `<span class="text-[9px] font-semibold text-purple-700 bg-purple-100/90 px-1.5 py-0.5 rounded border border-purple-200">${incomingCount} routes in</span>`
                : `<span class="text-[9px] font-semibold text-amber-700 bg-amber-100/90 px-1.5 py-0.5 rounded border border-amber-200">Unreached</span>`;

            bodyHtml = `
                <div class="p-3">
                    <p class="font-semibold text-[#171A21] truncate">${escapeHtml(dest.name)}</p>
                    <p class="text-[10px] text-[#687080] truncate mt-0.5 font-mono">${escapeHtml(dest.url || 'No URL configured')}</p>
                </div>
                <div class="px-3 py-2 bg-[#F7F8FA] border-t border-[#E4E7EC] text-[10px] text-[#687080] rounded-b-xl flex items-center justify-between">
                    <span class="flex items-center gap-1 font-medium">🏁 Terminal Step</span>
                    <span class="text-purple-600 font-semibold">Redirect</span>
                </div>
            `;
        } else if (node.node_type === 'form') {
            const form = node.form || {
                fields: [],
                submit_action: 'redirect',
                button_text: 'Submit Details',
                success_heading: 'Thank You!',
                success_message: 'Your submission has been received.'
            };
            const fields = form.fields || [];
            const submitAction = form.submit_action || 'redirect';
            const edge = (graphData.edges || []).find(e => e.source_node_id == node.id);
            const targetNode = edge ? graphData.nodes.find(n => n.id == edge.target_node_id) : null;
            const targetLabel = targetNode ? `→ ${targetNode.title || targetNode.node_type}` : null;

            if (submitAction === 'message') {
                statusBadgeHtml = `<span class="text-[9px] font-semibold text-emerald-700 bg-emerald-100/90 px-1.5 py-0.5 rounded border border-emerald-200">🏁 Message (End)</span>`;
            } else if (edge) {
                statusBadgeHtml = `<span class="text-[9px] font-semibold text-sky-700 bg-sky-100/90 px-1.5 py-0.5 rounded border border-sky-200">✓ Linked</span>`;
            } else {
                statusBadgeHtml = `<span class="text-[9px] font-semibold text-amber-700 bg-amber-100/90 px-1.5 py-0.5 rounded border border-amber-200">⚠️ Needs Route</span>`;
            }

            bodyHtml = `
                <div class="p-3">
                    <p class="font-semibold text-[#171A21] line-clamp-1">${escapeHtml(node.title || 'Lead Capture Form')}</p>
                    <span class="text-[10px] text-[#687080] mt-0.5 block">${fields.length} field${fields.length === 1 ? '' : 's'} • ${submitAction === 'message' ? 'No redirect' : 'Redirects on submit'}</span>
                    
                    <div class="mt-2 space-y-1">
                        ${fields.slice(0, 6).map(f => `
                            <div class="px-2 py-1 rounded bg-slate-50 border border-slate-200/60 text-[10px] text-[#344054] flex items-center justify-between">
                                <span class="truncate max-w-[150px]">${escapeHtml(f.label || f.field_key)}</span>
                                <span class="text-[9px] text-slate-400 font-mono">${escapeHtml(f.field_type || 'text')}${f.is_required ? '*' : ''}</span>
                            </div>
                        `).join('')}
                        ${fields.length > 6 ? `<div class="text-[9px] text-[#687080] text-center font-medium">+${fields.length - 6} more field${fields.length - 6 === 1 ? '' : 's'}</div>` : ''}
                        ${fields.length === 0 ? `<div class="text-[10px] text-amber-600 bg-amber-50 p-1.5 rounded text-center">No fields configured yet</div>` : ''}
                    </div>
                </div>
                <div class="px-3 py-2.5 bg-[#F7F8FA] border-t border-[#E4E7EC] flex items-center justify-between text-[11px] rounded-b-xl relative">
                    <span class="font-medium text-[#171A21] flex items-center gap-1.5 truncate max-w-[190px]">
                        ${submitAction === 'message' 
                            ? `<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span><span class="text-[10px] text-emerald-800 font-semibold truncate">Show success message</span>`
                            : `<span class="w-1.5 h-1.5 rounded-full ${edge ? 'bg-sky-500' : 'bg-amber-400'} shrink-0"></span><span class="text-[10px] text-[#171A21] truncate">${targetLabel || 'Route on submit'}</span>`
                        }
                    </span>
                    ${submitAction === 'redirect' ? `
                        <div class="port-handle w-3.5 h-3.5 rounded-full ${edge ? 'bg-sky-500' : 'bg-amber-400'} border-2 border-white shadow cursor-crosshair absolute -right-1.5 top-3"
                             data-port-type="output" data-node-id="${node.id}" data-option-id="null" title="${edge ? `Connected: ${escapeHtml(targetLabel)}` : 'Drag to connect next step'}"></div>
                    ` : ''}
                </div>
            `;
        }

        // Left input port handle (for question and destination nodes)
        let inputPortHtml = '';
        if (node.node_type !== 'start') {
            inputPortHtml = `
                <div class="port-handle w-3.5 h-3.5 rounded-full bg-slate-400 border-2 border-white shadow cursor-pointer absolute -left-1.5 top-4 hover:bg-[#5B5BD6]"
                     data-port-type="input" data-node-id="${node.id}" title="Input connection port"></div>
            `;
        }

        div.innerHTML = `
            ${inputPortHtml}
            <div class="node-header px-3 py-2 border-b border-[#E4E7EC] ${headerColor} rounded-t-xl flex items-center justify-between cursor-move">
                <div class="flex items-center gap-1.5 truncate">
                    <span class="font-bold text-xs">${icon}</span>
                    <span class="font-semibold truncate">${escapeHtml(node.title || badgeText)}</span>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    ${node.is_start ? '<span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-600 text-white shadow-xs tracking-wide">★ START</span>' : ''}
                    ${statusBadgeHtml}
                    <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded bg-white/70">${badgeText}</span>
                </div>
            </div>
            ${bodyHtml}
        `;

        // Node click & drag event handlers
        div.addEventListener('click', (e) => {
            e.stopPropagation();
            selectNode(node.id);
        });

        const header = div.querySelector('.node-header');
        header.addEventListener('mousedown', (e) => {
            e.stopPropagation();
            draggedNode = node;
            dragStartX = e.clientX;
            dragStartY = e.clientY;
            nodeStartX = node.position_x;
            nodeStartY = node.position_y;
            selectNode(node.id);
        });

        // Enable dropping wire anywhere on valid target node card (not start node)
        if (node.node_type !== 'start') {
            div.addEventListener('mouseup', (e) => {
                if (connectingSource && connectingSource.nodeId !== node.id) {
                    e.stopPropagation();
                    createEdge(connectingSource.nodeId, connectingSource.optionId, node.id);
                    connectingSource = null;
                    tempWire.classList.add('hidden');
                    document.querySelectorAll('.node-card').forEach(c => c.classList.remove('ring-2', 'ring-[#5B5BD6]'));
                }
            });

            div.addEventListener('mouseenter', () => {
                if (connectingSource && connectingSource.nodeId !== node.id) {
                    div.classList.add('ring-2', 'ring-[#5B5BD6]');
                }
            });

            div.addEventListener('mouseleave', () => {
                div.classList.remove('ring-2', 'ring-[#5B5BD6]');
            });
        }

        // Port handle events for wire dragging
        const ports = div.querySelectorAll('.port-handle');
        ports.forEach(p => {
            if (p.dataset.portType === 'output') {
                p.addEventListener('mousedown', (e) => {
                    e.stopPropagation();
                    const portRect = p.getBoundingClientRect();
                    const surfaceRect = surface.getBoundingClientRect();
                    const x = (portRect.left + portRect.width / 2 - surfaceRect.left) / scale;
                    const y = (portRect.top + portRect.height / 2 - surfaceRect.top) / scale;

                    connectingSource = {
                        nodeId: parseInt(p.dataset.nodeId),
                        optionId: p.dataset.optionId === 'null' ? null : parseInt(p.dataset.optionId),
                        x: x,
                        y: y
                    };

                    tempWire.classList.remove('hidden');
                    drawBezier(tempWire, x, y, x, y);
                });
            } else if (p.dataset.portType === 'input') {
                p.addEventListener('mouseup', (e) => {
                    e.stopPropagation();
                    if (connectingSource && connectingSource.nodeId !== node.id) {
                        createEdge(connectingSource.nodeId, connectingSource.optionId, node.id);
                        connectingSource = null;
                        tempWire.classList.add('hidden');
                        document.querySelectorAll('.node-card').forEach(c => c.classList.remove('ring-2', 'ring-[#5B5BD6]'));
                    }
                });
            }
        });

        nodesContainer.appendChild(div);
    }

    // Wires & Bezier Curve Calculations
    function renderWires() {
        wiresGroup.innerHTML = '';
        const surfaceRect = surface.getBoundingClientRect();

        (graphData.edges || []).forEach(edge => {
            const srcNode = graphData.nodes.find(n => n.id == edge.source_node_id);
            const tgtNode = graphData.nodes.find(n => n.id == edge.target_node_id);
            if (!srcNode || !tgtNode) return;

            // Default fallback port handle coordinates
            let srcX = srcNode.position_x + 256;
            let srcY = srcNode.position_y + 40;
            if (srcNode.node_type === 'start') {
                srcY = srcNode.position_y + 55;
            } else if (srcNode.node_type === 'form') {
                srcY = srcNode.position_y + 115;
            } else if (srcNode.node_type === 'question') {
                const q = srcNode.question || {};
                const options = q.options || [];
                const optIndex = options.findIndex(o => o.id == edge.source_option_id);
                if (optIndex >= 0) {
                    srcY = srcNode.position_y + 70 + (optIndex * 34) + 14;
                }
            }

            let tgtX = tgtNode.position_x;
            let tgtY = tgtNode.position_y + 20;

            // Compute exact pixel coordinates directly from DOM port handles
            const srcEl = document.getElementById(`node-${srcNode.id}`);
            if (srcEl && surfaceRect && surfaceRect.width > 0) {
                let portEl = null;
                if (edge.source_option_id) {
                    portEl = srcEl.querySelector(`.port-handle[data-port-type="output"][data-option-id="${edge.source_option_id}"]`);
                } else {
                    portEl = srcEl.querySelector('.port-handle[data-port-type="output"]');
                }
                if (portEl) {
                    const pRect = portEl.getBoundingClientRect();
                    srcX = (pRect.left + pRect.width / 2 - surfaceRect.left) / scale;
                    srcY = (pRect.top + pRect.height / 2 - surfaceRect.top) / scale;
                }
            }

            const tgtEl = document.getElementById(`node-${tgtNode.id}`);
            if (tgtEl && surfaceRect && surfaceRect.width > 0) {
                const inPort = tgtEl.querySelector('.port-handle[data-port-type="input"]');
                if (inPort) {
                    const inRect = inPort.getBoundingClientRect();
                    tgtX = (inRect.left + inRect.width / 2 - surfaceRect.left) / scale;
                    tgtY = (inRect.top + inRect.height / 2 - surfaceRect.top) / scale;
                }
            }

            const isStart = srcNode.node_type === 'start';
            const isForm = srcNode.node_type === 'form';
            const wireColor = selectedEdgeId === edge.id ? '#5B5BD6' : (isStart ? '#10B981' : (isForm ? '#0EA5E9' : '#6366F1'));

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.id = `wire-${edge.id || `${edge.source_node_id}-${edge.target_node_id}`}`;
            path.setAttribute('class', `wire-path pointer-events-auto ${selectedEdgeId === edge.id ? 'selected' : ''}`);
            path.style.stroke = wireColor;
            path.setAttribute('marker-end', selectedEdgeId === edge.id ? 'url(#arrow-selected)' : 'url(#arrow)');
            drawBezier(path, srcX, srcY, tgtX, tgtY);

            path.addEventListener('click', (e) => {
                e.stopPropagation();
                selectEdge(edge);
            });

            // Highlight connected node cards on wire hover
            path.addEventListener('mouseenter', () => {
                const sEl = document.getElementById(`node-${srcNode.id}`);
                const tEl = document.getElementById(`node-${tgtNode.id}`);
                if (sEl) sEl.classList.add('ring-2', 'ring-indigo-300');
                if (tEl) tEl.classList.add('ring-2', 'ring-indigo-300');
            });
            path.addEventListener('mouseleave', () => {
                const sEl = document.getElementById(`node-${srcNode.id}`);
                const tEl = document.getElementById(`node-${tgtNode.id}`);
                if (sEl && selectedNodeId !== srcNode.id) sEl.classList.remove('ring-2', 'ring-indigo-300');
                if (tEl && selectedNodeId !== tgtNode.id) tEl.classList.remove('ring-2', 'ring-indigo-300');
            });

            wiresGroup.appendChild(path);
        });

        updateMinimap();
    }

    function drawBezier(pathEl, x1, y1, x2, y2) {
        const dx = Math.max(Math.abs(x2 - x1) * 0.45, 40);
        const d = `M ${x1} ${y1} C ${x1 + dx} ${y1}, ${x2 - dx} ${y2}, ${x2} ${y2}`;
        pathEl.setAttribute('d', d);
    }

    // Collapsible Sidebars
    window.toggleLeftSidebar = function (forceOpen) {
        const sidebar = document.getElementById('leftSidebar');
        const expandBtn = document.getElementById('leftSidebarExpandBtn');
        if (!sidebar) return;

        const shouldOpen = forceOpen !== undefined ? forceOpen : sidebar.classList.contains('hidden');

        if (shouldOpen) {
            sidebar.classList.remove('hidden');
            if (expandBtn) expandBtn.classList.add('hidden');
        } else {
            sidebar.classList.add('hidden');
            if (expandBtn) expandBtn.classList.remove('hidden');
        }
        setTimeout(renderWires, 60);
    };

    window.toggleInspector = function (forceOpen) {
        const panel = document.getElementById('inspectorPanel');
        const expandBtn = document.getElementById('inspectorExpandBtn');
        if (!panel) return;

        const shouldOpen = forceOpen !== undefined ? forceOpen : panel.classList.contains('hidden');

        if (shouldOpen) {
            panel.classList.remove('hidden');
            if (expandBtn) expandBtn.classList.add('hidden');
        } else {
            panel.classList.add('hidden');
            if (expandBtn) expandBtn.classList.remove('hidden');
        }
        setTimeout(renderWires, 60);
    };

    window.openInspector = function () {
        toggleInspector(true);
    };

    // Node & Edge Selection
    function selectNode(nodeId) {
        const found = findNode(nodeId);
        selectedNodeId = found ? found.id : nodeId;
        selectedEdgeId = null;

        document.querySelectorAll('.node-card').forEach(el => el.classList.remove('selected'));
        const el = document.getElementById(`node-${selectedNodeId}`);
        if (el) el.classList.add('selected');

        openInspector();
        renderWires();
        renderInspector();
    }

    function selectEdge(edge) {
        selectedEdgeId = edge.id;
        selectedNodeId = null;
        document.querySelectorAll('.node-card').forEach(el => el.classList.remove('selected'));
        openInspector();
        renderWires();

        inspectorBadge.textContent = 'Active Wire';
        inspectorBadge.className = 'text-[10px] font-mono px-2 py-0.5 rounded bg-purple-50 text-purple-700 font-semibold uppercase';
        
        const srcNode = graphData.nodes.find(n => n.id == edge.source_node_id);
        const tgtNode = graphData.nodes.find(n => n.id == edge.target_node_id);
        
        let optLabel = '';
        if (edge.source_option_id && srcNode && srcNode.question && srcNode.question.options) {
            const opt = srcNode.question.options.find(o => o.id == edge.source_option_id);
            if (opt) optLabel = opt.label;
        }

        inspectorContent.innerHTML = `
            <div class="space-y-4">
                <div class="p-3 bg-indigo-50/70 border border-indigo-100 rounded-xl space-y-2">
                    <div class="flex items-center gap-1.5 font-bold text-xs text-[#171A21]">
                        <span class="w-2 h-2 rounded-full bg-[#5B5BD6]"></span>
                        <span>Connection Wire Route</span>
                    </div>
                    <div class="space-y-1.5 text-[11px] pt-1">
                        <div class="p-2 bg-white rounded-lg border border-slate-200">
                            <span class="text-[10px] text-[#687080] uppercase tracking-wider block font-bold">Source Step</span>
                            <span class="font-semibold text-[#171A21]">${escapeHtml(srcNode?.title || 'Start')}</span>
                            ${optLabel ? `<span class="block text-[10px] text-indigo-600 mt-0.5 font-medium">Option: "${escapeHtml(optLabel)}"</span>` : ''}
                        </div>
                        <div class="text-center text-slate-400 font-bold text-xs">↓</div>
                        <div class="p-2 bg-white rounded-lg border border-slate-200">
                            <span class="text-[10px] text-[#687080] uppercase tracking-wider block font-bold">Destination Step</span>
                            <span class="font-semibold text-[#171A21]">${escapeHtml(tgtNode?.title || 'Next Node')}</span>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="deleteSelectedEdge()" class="w-full py-2.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 font-semibold text-xs hover:bg-rose-100 flex items-center justify-center gap-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Delete Connection (Delete)</span>
                </button>
            </div>
        `;
    }

    function deselectAll() {
        selectedNodeId = null;
        selectedEdgeId = null;
        document.querySelectorAll('.node-card').forEach(el => el.classList.remove('selected'));
        renderWires();
        renderFunnelOverviewInspector();
    }

    function renderFunnelOverviewInspector() {
        inspectorBadge.textContent = 'Overview';
        inspectorBadge.className = 'text-[10px] font-mono px-2 py-0.5 rounded bg-indigo-50 text-[#5B5BD6] font-semibold uppercase';

        const funnel = graphData.funnel || { name: 'My Funnel', slug: 'funnel', status: 'draft' };
        const totalNodes = graphData.nodes ? graphData.nodes.length : 0;
        const questions = (graphData.nodes || []).filter(n => n.node_type === 'question');
        const forms = (graphData.nodes || []).filter(n => n.node_type === 'form');
        const destinations = (graphData.nodes || []).filter(n => n.node_type === 'destination');
        const edges = graphData.edges || [];

        // Check unmapped options & unlinked redirect forms
        const unmappedList = [];
        questions.forEach(qNode => {
            const q = qNode.question || {};
            const options = q.options || [];
            options.forEach(opt => {
                const isMapped = edges.some(e => e.source_node_id == qNode.id && e.source_option_id == opt.id);
                if (!isMapped) {
                    unmappedList.push({
                        nodeId: qNode.id,
                        nodeTitle: qNode.title || 'Question',
                        optionLabel: opt.label
                    });
                }
            });
        });

        forms.forEach(fNode => {
            const form = fNode.form || {};
            if ((form.submit_action || 'redirect') === 'redirect') {
                const isMapped = edges.some(e => e.source_node_id == fNode.id);
                if (!isMapped) {
                    unmappedList.push({
                        nodeId: fNode.id,
                        nodeTitle: fNode.title || 'Form',
                        optionLabel: 'Submit Route'
                    });
                }
            }
        });

        const startNode = (graphData.nodes || []).find(n => n.is_start) || (graphData.nodes || []).find(n => n.node_type !== 'destination');
        const hasStart = !!startNode;

        let healthHtml = '';
        if (unmappedList.length === 0 && hasStart) {
            healthHtml = `
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-xs">
                        <span>✓</span>
                        <span>Flow is 100% Connected</span>
                    </div>
                    <p class="text-[11px] text-emerald-700 leading-relaxed">
                        Starting step is configured and all branching routes lead to valid destinations.
                    </p>
                </div>
            `;
        } else {
            let issuesText = [];
            if (!hasStart) issuesText.push('Please designate a Starting Step for your funnel.');
            if (unmappedList.length > 0) issuesText.push(`${unmappedList.length} step route(s) still unlinked.`);

            healthHtml = `
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs flex items-center gap-1">⚠️ <span>Unfinished Routes (${unmappedList.length + (!hasStart ? 1 : 0)})</span></span>
                        <button type="button" onclick="openValidationDrawer()" class="text-[10px] text-amber-700 underline font-semibold">Inspect</button>
                    </div>
                    <p class="text-[11px] text-amber-800 leading-snug">
                        ${issuesText.join(' ')}
                    </p>
                    ${unmappedList.slice(0, 3).map(item => `
                        <div class="flex items-center justify-between p-1.5 bg-white/70 rounded border border-amber-200/60 text-[10px]">
                            <span class="truncate max-w-[170px]"><strong>${escapeHtml(item.nodeTitle)}</strong>: "${escapeHtml(item.optionLabel)}"</span>
                            <button type="button" onclick="focusNode(${item.nodeId})" class="text-[#5B5BD6] font-semibold hover:underline">Fix →</button>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        inspectorContent.innerHTML = `
            <div class="space-y-4">
                <!-- Funnel Title & Public Link Card -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-bold text-[#687080] uppercase tracking-wider">Active Funnel</span>
                            <h3 class="font-bold text-sm text-[#171A21] truncate max-w-[200px]">${escapeHtml(funnel.name)}</h3>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded ${funnel.status === 'published' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-amber-100 text-amber-700 border border-amber-200'}">
                            ${escapeHtml(funnel.status || 'draft')}
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-1.5 bg-white rounded-lg border border-slate-200 text-[11px]">
                        <span class="font-mono text-[#687080] truncate text-[10px]">/quiz/${escapeHtml(funnel.slug)}</span>
                        <button type="button" onclick="copyFunnelUrl()" class="px-2 py-0.5 bg-indigo-50 text-[#5B5BD6] rounded font-semibold text-[10px] hover:bg-indigo-100 transition-colors shrink-0">
                            Copy Link
                        </button>
                    </div>
                </div>

                <!-- 4-Stat Metrics Grid -->
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#687080] mb-2">Structure Metrics</span>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-2.5 rounded-xl border border-slate-200 bg-white shadow-2xs">
                            <span class="block text-[10px] text-[#687080]">Total Steps</span>
                            <span class="block text-base font-bold text-[#171A21] mt-0.5">${totalNodes}</span>
                        </div>
                        <div class="p-2.5 rounded-xl border border-slate-200 bg-white shadow-2xs">
                            <span class="block text-[10px] text-[#687080]">Questions</span>
                            <span class="block text-base font-bold text-indigo-600 mt-0.5">${questions.length}</span>
                        </div>
                        <div class="p-2.5 rounded-xl border border-slate-200 bg-white shadow-2xs">
                            <span class="block text-[10px] text-[#687080]">Forms</span>
                            <span class="block text-base font-bold text-sky-600 mt-0.5">${forms.length}</span>
                        </div>
                        <div class="p-2.5 rounded-xl border border-slate-200 bg-white shadow-2xs">
                            <span class="block text-[10px] text-[#687080]">Destinations</span>
                            <span class="block text-base font-bold text-purple-600 mt-0.5">${destinations.length}</span>
                        </div>
                    </div>
                </div>

                <!-- Health Diagnostic -->
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#687080] mb-2">Flow Health</span>
                    ${healthHtml}
                </div>

                <!-- Quick Canvas Actions -->
                <div class="space-y-2 pt-2 border-t border-slate-200">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#687080] mb-1">Canvas Actions</span>
                    
                    <button type="button" onclick="autoArrangeNodes()" class="w-full py-2 px-3 rounded-xl border border-indigo-200 bg-indigo-50/60 text-[#5B5BD6] font-semibold text-xs hover:bg-indigo-100 flex items-center justify-between transition-colors">
                        <span class="flex items-center gap-1.5">
                            <span>✨</span>
                            <span>Auto-Arrange Flow (Tree Layout)</span>
                        </span>
                        <kbd class="font-mono text-[9px] bg-white px-1.5 py-0.5 rounded border border-indigo-200">L</kbd>
                    </button>

                    <button type="button" onclick="centerCanvas()" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-white text-[#171A21] font-semibold text-xs hover:bg-slate-50 flex items-center justify-between transition-colors">
                        <span class="flex items-center gap-1.5">
                            <span>🎯</span>
                            <span>Center & Fit View</span>
                        </span>
                        <kbd class="font-mono text-[9px] bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">Fit</kbd>
                    </button>

                    <button type="button" onclick="openPreviewModal()" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-white text-[#171A21] font-semibold text-xs hover:bg-slate-50 flex items-center justify-between transition-colors">
                        <span class="flex items-center gap-1.5">
                            <span>👁️</span>
                            <span>Test Run Live Quiz</span>
                        </span>
                        <kbd class="font-mono text-[9px] bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">P</kbd>
                    </button>
                </div>

                <!-- Pro-Tip Banner -->
                <div class="p-2.5 rounded-xl bg-slate-100/70 border border-slate-200/80 text-[11px] text-[#687080]">
                    <span class="font-semibold text-[#171A21] block mb-0.5">💡 Canvas Tip</span>
                    Click any node to edit questions & answer routes, or drag handles to connect steps.
                </div>
            </div>
        `;
    }

    function getStartStepWidgetHtml(node) {
        if (node.node_type === 'destination') return '';
        return `
            <div class="p-2.5 rounded-xl border ${node.is_start ? 'border-emerald-300 bg-emerald-50/70 ring-1 ring-emerald-200' : 'border-slate-200 bg-slate-50/70'} flex items-center justify-between transition-all">
                <div>
                    <div class="text-xs font-bold ${node.is_start ? 'text-emerald-900' : 'text-slate-800'} flex items-center gap-1.5">
                        ${node.is_start ? '<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>' : ''}
                        <span>Funnel Starting Step</span>
                    </div>
                    <div class="text-[10px] ${node.is_start ? 'text-emerald-700' : 'text-slate-500'}">
                        ${node.is_start ? 'Visitors begin their funnel journey here' : 'Not the current starting step'}
                    </div>
                </div>
                ${node.is_start 
                    ? '<span class="px-2 py-0.5 rounded-lg bg-emerald-600 text-white font-bold text-[10px] shadow-xs">★ START</span>'
                    : `<button type="button" onclick="setAsStartNode(${node.id})" class="px-2.5 py-1 rounded-lg bg-white border border-slate-300 hover:border-emerald-500 hover:text-emerald-700 text-slate-700 font-semibold text-[10px] shadow-xs transition-colors">Set as Start</button>`
                }
            </div>
        `;
    }

    window.setAsStartNode = function (nodeId) {
        graphData.nodes.forEach(n => {
            n.is_start = (n.id == nodeId);
        });
        renderCanvas();
        renderInspector();
        markDirty();
        showToast('Starting step updated', 'success');
    };

    // Inspector Panel Rendering
    function renderInspector(preserveScroll = false) {
        const scrollPos = (preserveScroll && inspectorContent) ? inspectorContent.scrollTop : 0;
        const node = findNode(selectedNodeId);
        if (!node) return deselectAll();

        inspectorBadge.textContent = node.node_type;

        if (node.node_type === 'start') {
            const outgoingEdge = (graphData.edges || []).find(e => e.source_node_id == node.id);
            inspectorContent.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-[#171A21] uppercase text-[10px] mb-1">Node Title</label>
                        <input type="text" value="${escapeHtml(node.title || '')}" oninput="updateNodeTitle(${node.id}, this.value)"
                               class="w-full px-3 py-1.5 rounded-lg border border-[#E4E7EC] text-xs">
                    </div>

                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-[11px]">
                        <strong>Funnel Start:</strong> Visitors enter here after submitting the pre-quiz form. Connect the output handle to your first branching question.
                    </div>

                    <div>
                        <label class="block font-semibold text-[#171A21] uppercase text-[10px] mb-1">Connected Next Node</label>
                        <select onchange="updateEdgeTarget(${node.id}, null, this.value)" class="w-full px-2.5 py-1.5 rounded border border-[#E4E7EC] bg-white text-xs">
                            <option value="">-- Disconnected --</option>
                            ${graphData.nodes.filter(n => n.id !== node.id).map(n => `
                                <option value="${n.id}" ${outgoingEdge && outgoingEdge.target_node_id == n.id ? 'selected' : ''}>${escapeHtml(n.title)} (${n.node_type})</option>
                            `).join('')}
                        </select>
                    </div>
                </div>
            `;
        } else if (node.node_type === 'question') {
            const q = node.question || { question_text: '', help_text: '', question_type: 'single_choice', is_required: 1, options: [] };
            const options = q.options || [];

            let optionsHtml = '';
            options.forEach((opt, idx) => {
                const currentEdge = (graphData.edges || []).find(e => e.source_node_id == node.id && e.source_option_id == opt.id);
                optionsHtml += `
                    <div class="p-2.5 border border-[#E4E7EC] rounded-lg bg-[#F7F8FA] space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[10px] text-[#687080]">Option ${idx + 1}</span>
                            <button type="button" onclick="deleteOption(${node.id}, ${opt.id})" class="text-rose-500 hover:text-rose-700 font-bold text-xs" title="Remove Option">✕</button>
                        </div>
                        <div>
                            <input type="text" value="${escapeHtml(opt.label)}" placeholder="Option text" oninput="updateOptionLabel(${node.id}, ${opt.id}, this.value)"
                                   class="w-full px-2 py-1 rounded border border-[#E4E7EC] bg-white text-xs font-medium">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-[#687080] mb-0.5">Routes to next node:</label>
                            <select onchange="updateEdgeTarget(${node.id}, ${opt.id}, this.value)" class="w-full px-2 py-1 rounded border border-[#E4E7EC] bg-white text-xs font-semibold ${currentEdge ? 'text-[#5B5BD6]' : 'text-amber-600'}">
                                <option value="">⚠️ No route (Pick target)</option>
                                ${graphData.nodes.filter(n => n.id !== node.id).map(n => `
                                    <option value="${n.id}" ${currentEdge && currentEdge.target_node_id == n.id ? 'selected' : ''}>→ ${escapeHtml(n.title)} (${n.node_type})</option>
                                `).join('')}
                            </select>
                        </div>
                    </div>
                `;
            });

            inspectorContent.innerHTML = `
                <div class="space-y-4">
                    ${getStartStepWidgetHtml(node)}

                    <div>
                        <label class="block font-semibold text-[#171A21] uppercase text-[10px] mb-1">Step Title / Identifier</label>
                        <input type="text" value="${escapeHtml(node.title || '')}" oninput="updateNodeTitle(${node.id}, this.value)"
                               class="w-full px-3 py-1.5 rounded-lg border border-[#E4E7EC] text-xs">
                    </div>

                    <div>
                        <label class="block font-semibold text-[#171A21] uppercase text-[10px] mb-1">Question Text *</label>
                        <textarea rows="2" oninput="updateQuestionProperty(${node.id}, 'question_text', this.value)"
                                  class="w-full px-3 py-1.5 rounded-lg border border-[#E4E7EC] text-xs">${escapeHtml(q.question_text || '')}</textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#171A21] uppercase text-[10px] mb-1">Help / Subtitle Text</label>
                        <input type="text" value="${escapeHtml(q.help_text || '')}" oninput="updateQuestionProperty(${node.id}, 'help_text', this.value)"
                               class="w-full px-3 py-1.5 rounded-lg border border-[#E4E7EC] text-xs" placeholder="Optional helper note">
                    </div>

                    <div class="pt-2 border-t border-[#E4E7EC]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-[#171A21] text-xs">Branching Options (${options.length})</span>
                            <button type="button" onclick="addOption(${node.id})" class="text-xs text-[#5B5BD6] font-semibold hover:underline">
                                + Add Option
                            </button>
                        </div>
                        ${options.length > 1 ? `
                            <div class="mb-3 p-2 bg-indigo-50/60 rounded-lg border border-indigo-100 flex items-center justify-between gap-2">
                                <span class="text-[10px] font-semibold text-indigo-900 shrink-0">Set all options to:</span>
                                <select onchange="if(this.value) setAllOptionsRoute(${node.id}, this.value)" class="text-[11px] px-2 py-1 rounded border border-indigo-200 bg-white font-medium text-[#171A21] w-full">
                                    <option value="">-- Choose common target --</option>
                                    ${graphData.nodes.filter(n => n.id !== node.id).map(n => `
                                        <option value="${n.id}">→ ${escapeHtml(n.title)} (${n.node_type})</option>
                                    `).join('')}
                                </select>
                            </div>
                        ` : ''}
                        <div class="space-y-2">
                            ${optionsHtml}
                        </div>
                    </div>

                    <div class="pt-4 border-t border-[#E4E7EC]">
                        <button type="button" onclick="deleteNode(${node.id})" class="w-full py-2 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-semibold hover:bg-rose-100">
                            Delete Question Node
                        </button>
                    </div>
                </div>
            `;
        } else if (node.node_type === 'destination') {
            const dest = node.destination || { name: 'Landing Page', url: 'https://example.com' };

            inspectorContent.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-[#171A21] uppercase text-[10px] mb-1">Destination Name</label>
                        <input type="text" value="${escapeHtml(dest.name || '')}" oninput="updateDestinationProperty(${node.id}, 'name', this.value)"
                               class="w-full px-3 py-1.5 rounded-lg border border-[#E4E7EC] text-xs">
                    </div>

                    <div>
                        <label class="block font-semibold text-[#171A21] uppercase text-[10px] mb-1">Landing Page URL *</label>
                        <input type="url" value="${escapeHtml(dest.url || '')}" oninput="updateDestinationProperty(${node.id}, 'url', this.value)"
                               placeholder="https://yourdomain.com/page" class="w-full px-3 py-1.5 rounded-lg border border-[#E4E7EC] text-xs font-mono">
                    </div>

                    ${dest.url ? `
                        <div>
                            <a href="${dest.url}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-[#5B5BD6] hover:underline font-semibold">
                                Preview URL in New Tab ↗
                            </a>
                        </div>
                    ` : ''}

                    <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg text-purple-800 text-[11px]">
                        <strong>Terminal Node:</strong> When a visitor reaches this step, their quiz session is saved as completed and redirected to this URL.
                    </div>

                    <div class="pt-4 border-t border-[#E4E7EC]">
                        <button type="button" onclick="deleteNode(${node.id})" class="w-full py-2 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-semibold hover:bg-rose-100">
                            Delete Destination Node
                        </button>
                    </div>
                </div>
            `;
        } else if (node.node_type === 'form') {
            if (!node.form) {
                node.form = {
                    fields: [],
                    submit_action: 'redirect',
                    button_text: 'Submit Details',
                    success_heading: 'Thank You!',
                    success_message: 'Your submission has been received.'
                };
            }
            if (!node.form.fields) node.form.fields = [];

            const form = node.form;
            const fields = form.fields;
            // Ensure every field has a reliable ID
            fields.forEach((f, idx) => {
                if (!f.id) f.id = Date.now() + idx + 1;
            });

            const submitAction = form.submit_action || 'redirect';
            const outgoingEdge = (graphData.edges || []).find(e => e.source_node_id == node.id);

            let fieldsHtml = '';
            fields.forEach((f, idx) => {
                fieldsHtml += `
                    <div class="p-2.5 border border-[#E4E7EC] rounded-xl bg-[#F7F8FA] space-y-2 relative group">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold flex items-center justify-center">${idx + 1}</span>
                                <span class="font-bold text-[10px] text-[#171A21] truncate max-w-[140px]">${escapeHtml(f.label || 'Untitled Field')}</span>
                                ${f.is_required ? '<span class="text-[9px] font-bold text-rose-600 bg-rose-50 px-1 rounded">Required</span>' : ''}
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="moveFormField('${node.id}', '${f.id}', 'up')" ${idx === 0 ? 'disabled' : ''} class="p-1 text-slate-400 hover:text-slate-700 disabled:opacity-30 disabled:hover:text-slate-400" title="Move Up">↑</button>
                                <button type="button" onclick="moveFormField('${node.id}', '${f.id}', 'down')" ${idx === fields.length - 1 ? 'disabled' : ''} class="p-1 text-slate-400 hover:text-slate-700 disabled:opacity-30 disabled:hover:text-slate-400" title="Move Down">↓</button>
                                <button type="button" onclick="deleteFormField('${node.id}', '${f.id}')" class="p-1 text-rose-500 hover:text-rose-700 font-bold ml-1" title="Delete Field">✕</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[9px] font-semibold text-[#687080] mb-0.5">Label</label>
                                <input type="text" value="${escapeHtml(f.label || '')}" placeholder="Field Label"
                                       oninput="updateFormField('${node.id}', '${f.id}', 'label', this.value)"
                                       class="w-full px-2 py-1 rounded border border-[#E4E7EC] bg-white text-xs">
                            </div>
                            <div>
                                <label class="block text-[9px] font-semibold text-[#687080] mb-0.5">Field Type</label>
                                <select onchange="updateFormField('${node.id}', '${f.id}', 'field_type', this.value)"
                                        class="w-full px-2 py-1 rounded border border-[#E4E7EC] bg-white text-xs">
                                    <option value="text" ${f.field_type === 'text' ? 'selected' : ''}>Text Input</option>
                                    <option value="email" ${f.field_type === 'email' ? 'selected' : ''}>Email Address</option>
                                    <option value="phone" ${f.field_type === 'phone' ? 'selected' : ''}>Phone Number</option>
                                    <option value="number" ${f.field_type === 'number' ? 'selected' : ''}>Number</option>
                                    <option value="dropdown" ${f.field_type === 'dropdown' ? 'selected' : ''}>Dropdown Select</option>
                                    <option value="consent" ${f.field_type === 'consent' ? 'selected' : ''}>Consent Checkbox</option>
                                </select>
                            </div>
                        </div>

                        ${f.field_type !== 'consent' ? `
                            <div>
                                <label class="block text-[9px] font-semibold text-[#687080] mb-0.5">Placeholder</label>
                                <input type="text" value="${escapeHtml(f.placeholder || '')}" placeholder="Placeholder text"
                                       oninput="updateFormField('${node.id}', '${f.id}', 'placeholder', this.value)"
                                       class="w-full px-2 py-1 rounded border border-[#E4E7EC] bg-white text-xs">
                            </div>
                        ` : ''}

                        <div class="pt-1 flex items-center justify-between text-[10px]">
                            <label class="flex items-center gap-1.5 cursor-pointer text-[#344054]">
                                <input type="checkbox" ${f.is_required ? 'checked' : ''}
                                       onchange="updateFormField('${node.id}', '${f.id}', 'is_required', this.checked ? 1 : 0)"
                                       class="rounded text-[#5B5BD6]">
                                <span>Required field</span>
                            </label>
                            <span class="text-slate-400 font-mono text-[9px]">key: ${escapeHtml(f.field_key || '')}</span>
                        </div>
                    </div>
                `;
            });

            inspectorContent.innerHTML = `
                <div class="space-y-4">
                    ${getStartStepWidgetHtml(node)}

                    <div>
                        <label class="block font-semibold text-[#171A21] uppercase text-[10px] mb-1">Step Title / Identifier</label>
                        <input type="text" value="${escapeHtml(node.title || '')}" oninput="updateNodeTitle('${node.id}', this.value)"
                               class="w-full px-3 py-1.5 rounded-lg border border-[#E4E7EC] text-xs">
                    </div>

                    <!-- Post-Submission Decision: Redirect vs No Redirection -->
                    <div class="p-3 bg-sky-50 border border-sky-200/80 rounded-xl space-y-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-sky-600"></span>
                            <label class="block font-bold text-sky-950 uppercase text-[10px] tracking-wider">After Form Submission *</label>
                        </div>
                        <p class="text-[11px] text-sky-800 leading-snug">Choose what happens when visitors submit this form:</p>

                        <div class="space-y-1.5 pt-1">
                            <label class="flex items-start gap-2.5 p-2 rounded-xl bg-white border ${submitAction === 'redirect' ? 'border-sky-500 ring-1 ring-sky-300' : 'border-slate-200'} cursor-pointer hover:border-sky-400 transition-all">
                                <input type="radio" name="form_submit_action_${node.id}" value="redirect" ${submitAction === 'redirect' ? 'checked' : ''}
                                       onchange="updateFormProperty('${node.id}', 'submit_action', 'redirect')" class="mt-0.5 text-sky-600">
                                <div>
                                    <span class="block font-bold text-xs text-[#171A21]">Route / Redirect to Next Step</span>
                                    <span class="block text-[10px] text-[#687080] mt-0.5 leading-tight">Proceeds to connected question or destination landing page.</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 p-2 rounded-xl bg-white border ${submitAction === 'message' ? 'border-emerald-500 ring-1 ring-emerald-300' : 'border-slate-200'} cursor-pointer hover:border-emerald-400 transition-all">
                                <input type="radio" name="form_submit_action_${node.id}" value="message" ${submitAction === 'message' ? 'checked' : ''}
                                       onchange="updateFormProperty('${node.id}', 'submit_action', 'message')" class="mt-0.5 text-emerald-600">
                                <div>
                                    <span class="block font-bold text-xs text-[#171A21]">Show Thank You Message (No Redirection)</span>
                                    <span class="block text-[10px] text-[#687080] mt-0.5 leading-tight">Acts as final step; displays success confirmation without leaving.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    ${submitAction === 'redirect' ? `
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                            <label class="block font-semibold text-[#171A21] uppercase text-[10px]">Routes to Next Step</label>
                            <select onchange="updateEdgeTarget('${node.id}', null, this.value)" class="w-full px-2.5 py-1.5 rounded-lg border border-[#E4E7EC] bg-white text-xs font-semibold ${outgoingEdge ? 'text-sky-600' : 'text-amber-600'}">
                                <option value="">⚠️ No route (Drag wire or pick target)</option>
                                ${graphData.nodes.filter(n => n.id != node.id).map(n => `
                                    <option value="${n.id}" ${outgoingEdge && outgoingEdge.target_node_id == n.id ? 'selected' : ''}>→ ${escapeHtml(n.title)} (${n.node_type})</option>
                                `).join('')}
                            </select>
                        </div>
                    ` : `
                        <div class="p-3 bg-emerald-50/60 border border-emerald-200 rounded-xl space-y-2.5 text-xs">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Thank You Screen Settings</span>
                            <div>
                                <label class="block font-semibold text-[#171A21] text-[10px] mb-1">Confirmation Heading</label>
                                <input type="text" value="${escapeHtml(form.success_heading || 'Thank You!')}"
                                       oninput="updateFormProperty('${node.id}', 'success_heading', this.value)"
                                       placeholder="Thank You!" class="w-full px-2.5 py-1 rounded border border-[#E4E7EC] bg-white text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-[#171A21] text-[10px] mb-1">Confirmation Message</label>
                                <textarea rows="2" oninput="updateFormProperty('${node.id}', 'success_message', this.value)"
                                          placeholder="Your submission has been received."
                                          class="w-full px-2.5 py-1 rounded border border-[#E4E7EC] bg-white text-xs">${escapeHtml(form.success_message || 'Your submission has been received.')}</textarea>
                            </div>
                        </div>
                    `}

                    <div>
                        <label class="block font-semibold text-[#171A21] uppercase text-[10px] mb-1">Submit Button Text</label>
                        <input type="text" value="${escapeHtml(form.button_text || 'Submit Details')}"
                               oninput="updateFormProperty('${node.id}', 'button_text', this.value)"
                               placeholder="Submit Details" class="w-full px-3 py-1.5 rounded-lg border border-[#E4E7EC] text-xs">
                    </div>

                    <!-- Fields Section -->
                    <div class="pt-2 border-t border-[#E4E7EC] space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#171A21] text-xs">Form Fields (${fields.length})</span>
                            <button type="button" onclick="addFormField('${node.id}', 'text')" class="text-xs text-[#5B5BD6] font-semibold hover:underline">
                                + Custom Field
                            </button>
                        </div>

                        <!-- Quick Presets -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] text-[#687080] font-semibold">Presets:</span>
                            <button type="button" onclick="addFormField('${node.id}', 'name')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-[10px] font-medium text-[#171A21]">+ Name</button>
                            <button type="button" onclick="addFormField('${node.id}', 'email')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-[10px] font-medium text-[#171A21]">+ Email</button>
                            <button type="button" onclick="addFormField('${node.id}', 'phone')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-[10px] font-medium text-[#171A21]">+ Phone</button>
                            <button type="button" onclick="addFormField('${node.id}', 'company')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-[10px] font-medium text-[#171A21]">+ Company</button>
                            <button type="button" onclick="addFormField('${node.id}', 'consent')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-[10px] font-medium text-[#171A21]">+ Consent</button>
                        </div>

                        <div class="space-y-2 pt-1">
                            ${fields.length === 0 ? `
                                <div class="p-3 text-center text-[11px] text-amber-700 bg-amber-50 rounded-xl border border-amber-200">
                                    No fields added yet. Click one of the presets above to add fields to this form.
                                </div>
                            ` : fieldsHtml}
                        </div>
                    </div>

                    <div class="pt-4 border-t border-[#E4E7EC]">
                        <button type="button" onclick="deleteNode('${node.id}')" class="w-full py-2 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-semibold hover:bg-rose-100 transition-colors">
                            Delete Form Node
                        </button>
                    </div>
                </div>
            `;
        }

        if (preserveScroll && inspectorContent) {
            inspectorContent.scrollTop = scrollPos;
        }
    }

    // Graph Mutations
    window.addNodeToCanvas = function (type, customX = null, customY = null) {
        // Place in center of current view if not provided
        const canvasX = customX !== null ? customX : Math.max(40, (-panX + 220) / scale);
        const canvasY = customY !== null ? customY : Math.max(40, (-panY + 160) / scale);

        const newNodeId = Date.now(); // temporary unique id for draft
        const nodeKey = `node_${Math.random().toString(36).substring(2, 8)}`;

        let node = {
            id: newNodeId,
            node_key: nodeKey,
            node_type: type,
            title: type === 'start' ? 'Start' : (type === 'question' ? 'New Question' : (type === 'form' ? 'Lead Capture Form' : 'Landing Destination')),
            position_x: Math.round(canvasX),
            position_y: Math.round(canvasY),
        };

        if (type === 'question') {
            node.question = {
                question_text: 'What is your primary interest?',
                question_type: 'single_choice',
                is_required: 1,
                options: [
                    { id: Date.now() + 1, option_key: `opt_${Math.random().toString(36).substring(2, 8)}`, label: 'Option A', value: 'opt_a', display_order: 1 },
                    { id: Date.now() + 2, option_key: `opt_${Math.random().toString(36).substring(2, 8)}`, label: 'Option B', value: 'opt_b', display_order: 2 }
                ]
            };
        } else if (type === 'destination') {
            node.destination = {
                name: 'Custom Result Page',
                url: 'https://example.com/result'
            };
        } else if (type === 'form') {
            node.form = {
                fields: [
                    { id: Date.now() + 1, field_key: 'name', label: 'Full Name', field_type: 'text', placeholder: 'Enter your name', is_required: 1 },
                    { id: Date.now() + 2, field_key: 'email', label: 'Email Address', field_type: 'email', placeholder: 'Enter your email', is_required: 1 }
                ],
                submit_action: 'redirect',
                button_text: 'Submit Details',
                success_heading: 'Thank You!',
                success_message: 'Your submission has been received.'
            };
        }

        // If no node is currently designated as start, make this node the start node
        const hasStart = graphData.nodes.some(n => n.is_start);
        if (!hasStart && type !== 'destination') {
            node.is_start = true;
        }

        graphData.nodes.push(node);
        renderCanvas();
        selectNode(node.id);
        markDirty();
    };

    window.updateNodeTitle = function (nodeId, title) {
        const node = graphData.nodes.find(n => n.id === nodeId);
        if (node) {
            node.title = title;
            const el = document.getElementById(`node-${nodeId}`);
            if (el) {
                const titleSpan = el.querySelector('.node-header span.truncate');
                if (titleSpan) titleSpan.textContent = title;
            }
            markDirty();
        }
    };

    window.updateQuestionProperty = function (nodeId, prop, value) {
        const node = graphData.nodes.find(n => n.id === nodeId);
        if (node && node.question) {
            node.question[prop] = value;
            const el = document.getElementById(`node-${nodeId}`);
            if (el && prop === 'question_text') {
                const p = el.querySelector('.line-clamp-2');
                if (p) p.textContent = value;
            }
            markDirty();
        }
    };

    window.updateDestinationProperty = function (nodeId, prop, value) {
        const node = graphData.nodes.find(n => n.id === nodeId);
        if (node && node.destination) {
            node.destination[prop] = value;
            const el = document.getElementById(`node-${nodeId}`);
            if (el) {
                const nameEl = el.querySelector('.p-3 p.font-semibold');
                const urlEl = el.querySelector('.p-3 p.text-\\[10px\\]');
                if (nameEl && prop === 'name') nameEl.textContent = value;
                if (urlEl && prop === 'url') urlEl.textContent = value;
            }
            markDirty();
        }
    };

    window.updateFormProperty = function (nodeId, prop, value) {
        const node = findNode(nodeId);
        if (node) {
            if (!node.form) node.form = {};
            node.form[prop] = value;
            renderCanvas();
            renderInspector(true);
            markDirty();
        }
    };

    window.addFormField = function (nodeId, presetType = 'text') {
        const node = findNode(nodeId);
        if (!node) {
            console.error('addFormField: node not found for nodeId:', nodeId);
            return;
        }
        if (!node.form) node.form = { fields: [] };
        if (!node.form.fields) node.form.fields = [];

        let label = 'Custom Field';
        let placeholder = '';
        let key = `field_${Date.now().toString(36)}`;
        let type = 'text';

        if (presetType === 'name') {
            label = 'Full Name';
            key = 'name';
            placeholder = 'Enter your name';
            type = 'text';
        } else if (presetType === 'email') {
            label = 'Email Address';
            key = 'email';
            placeholder = 'Enter your email';
            type = 'email';
        } else if (presetType === 'phone') {
            label = 'Phone Number';
            key = 'phone';
            placeholder = '+1 (555) 000-0000';
            type = 'phone';
        } else if (presetType === 'company') {
            label = 'Company';
            key = 'company';
            placeholder = 'Your company name';
            type = 'text';
        } else if (presetType === 'consent') {
            label = 'I agree to the privacy policy & terms';
            key = 'consent';
            type = 'consent';
        }

        // Ensure unique key
        let testKey = key;
        let counter = 1;
        while (node.form.fields.some(f => f.field_key === testKey)) {
            testKey = `${key}_${counter++}`;
        }
        key = testKey;

        const newFieldId = Date.now() + Math.floor(Math.random() * 1000);

        node.form.fields.push({
            id: newFieldId,
            field_key: key,
            label: label,
            field_type: type,
            placeholder: placeholder,
            is_required: presetType === 'consent' ? 0 : 1
        });

        renderCanvas();
        renderInspector(true);
        markDirty();

        showToast(`Added ${label} field`, 'success');

        // Smoothly scroll down so user immediately sees the newly added field
        setTimeout(() => {
            if (inspectorContent) {
                inspectorContent.scrollTo({
                    top: inspectorContent.scrollHeight,
                    behavior: 'smooth'
                });
            }
        }, 50);
    };

    window.updateFormField = function (nodeId, fieldId, prop, value) {
        const node = findNode(nodeId);
        if (node && node.form && node.form.fields) {
            const field = node.form.fields.find(f => f.id == fieldId || f.field_key == fieldId);
            if (field) {
                field[prop] = value;
                if (prop === 'label' && (!field.field_key || field.field_key.startsWith('field_'))) {
                    field.field_key = value.toLowerCase().replace(/[^a-z0-9_]/g, '_').substring(0, 30);
                }
                renderCanvas();
                markDirty();
            }
        }
    };

    window.deleteFormField = function (nodeId, fieldId) {
        const node = findNode(nodeId);
        if (node && node.form && node.form.fields) {
            node.form.fields = node.form.fields.filter(f => f.id != fieldId && f.field_key != fieldId);
            renderCanvas();
            renderInspector(true);
            markDirty();
        }
    };

    window.moveFormField = function (nodeId, fieldId, direction) {
        const node = findNode(nodeId);
        if (!node || !node.form || !node.form.fields) return;
        const index = node.form.fields.findIndex(f => f.id == fieldId || f.field_key == fieldId);
        if (index < 0) return;

        const targetIndex = direction === 'up' ? index - 1 : index + 1;
        if (targetIndex < 0 || targetIndex >= node.form.fields.length) return;

        const [item] = node.form.fields.splice(index, 1);
        node.form.fields.splice(targetIndex, 0, item);

        renderCanvas();
        renderInspector(true);
        markDirty();
    };

    window.addOption = function (nodeId) {
        const node = graphData.nodes.find(n => n.id === nodeId);
        if (node && node.question) {
            const optId = Date.now();
            node.question.options.push({
                id: optId,
                option_key: `opt_${Math.random().toString(36).substring(2, 6)}`,
                label: `Option ${(node.question.options.length + 1)}`,
                value: `opt_val_${(node.question.options.length + 1)}`,
                display_order: node.question.options.length + 1
            });
            renderCanvas();
            renderInspector();
            markDirty();
        }
    };

    window.updateOptionLabel = function (nodeId, optionId, label) {
        const node = graphData.nodes.find(n => n.id === nodeId);
        if (node && node.question) {
            const opt = node.question.options.find(o => o.id === optionId);
            if (opt) {
                opt.label = label;
                opt.value = label;
                renderCanvas();
                markDirty();
            }
        }
    };

    window.deleteOption = function (nodeId, optionId) {
        const node = graphData.nodes.find(n => n.id === nodeId);
        if (node && node.question) {
            node.question.options = node.question.options.filter(o => o.id !== optionId);
            // Remove connected edges
            graphData.edges = (graphData.edges || []).filter(e => !(e.source_node_id == nodeId && e.source_option_id == optionId));
            renderCanvas();
            renderInspector();
            markDirty();
        }
    };

    window.setAllOptionsRoute = function (nodeId, targetNodeId) {
        const node = graphData.nodes.find(n => n.id == nodeId);
        if (!node || !node.question || !node.question.options) return;

        const tgtN = graphData.nodes.find(n => n.id == targetNodeId);

        node.question.options.forEach(opt => {
            // Remove existing edge for this option
            graphData.edges = (graphData.edges || []).filter(e => !(e.source_node_id == nodeId && e.source_option_id == opt.id));

            if (targetNodeId && targetNodeId !== '') {
                graphData.edges.push({
                    id: Date.now() + Math.floor(Math.random() * 10000),
                    source_node_id: parseInt(nodeId),
                    source_node_key: node.node_key,
                    source_option_id: parseInt(opt.id),
                    source_option_key: opt.option_key,
                    target_node_id: parseInt(targetNodeId),
                    target_node_key: tgtN ? tgtN.node_key : null
                });
            }
        });

        renderCanvas();
        renderInspector();
        markDirty();
    };

    window.updateEdgeTarget = function (sourceNodeId, sourceOptionId, targetNodeId) {
        // Remove existing edge from this source & option
        graphData.edges = (graphData.edges || []).filter(e => {
            if (sourceOptionId) {
                return !(e.source_node_id == sourceNodeId && e.source_option_id == sourceOptionId);
            } else {
                return !(e.source_node_id == sourceNodeId && !e.source_option_id);
            }
        });

        if (targetNodeId && targetNodeId !== '') {
            const srcN = graphData.nodes.find(n => n.id == sourceNodeId);
            const tgtN = graphData.nodes.find(n => n.id == targetNodeId);
            let optKey = null;
            if (srcN && srcN.question && srcN.question.options) {
                const optObj = srcN.question.options.find(o => o.id == sourceOptionId);
                if (optObj) optKey = optObj.option_key;
            }

            graphData.edges.push({
                id: Date.now(),
                source_node_id: parseInt(sourceNodeId),
                source_node_key: srcN ? srcN.node_key : null,
                source_option_id: sourceOptionId ? parseInt(sourceOptionId) : null,
                source_option_key: optKey,
                target_node_id: parseInt(targetNodeId),
                target_node_key: tgtN ? tgtN.node_key : null
            });
        }

        renderCanvas();
        renderInspector();
        markDirty();
    };

    function createEdge(sourceNodeId, sourceOptionId, targetNodeId) {
        updateEdgeTarget(sourceNodeId, sourceOptionId, targetNodeId);
    }

    window.deleteSelectedEdge = function () {
        if (selectedEdgeId) {
            graphData.edges = (graphData.edges || []).filter(e => e.id !== selectedEdgeId);
            selectedEdgeId = null;
            renderCanvas();
            deselectAll();
            markDirty();
        }
    };

    // Custom Beautified Confirm & Alert Dialogs
    window.showConfirmDialog = function ({ title = 'Confirm Action', message = 'Are you sure?', confirmText = 'Confirm', cancelText = 'Cancel', type = 'danger' }) {
        return new Promise((resolve) => {
            const modal = document.getElementById('customDialogModal');
            const card = document.getElementById('customDialogCard');
            const titleEl = document.getElementById('customDialogTitle');
            const msgEl = document.getElementById('customDialogMessage');
            const confirmBtn = document.getElementById('customDialogConfirmBtn');
            const cancelBtn = document.getElementById('customDialogCancelBtn');
            const iconContainer = document.getElementById('customDialogIconContainer');
            const dangerIcon = document.getElementById('customDialogDangerIcon');
            const warningIcon = document.getElementById('customDialogWarningIcon');
            const successIcon = document.getElementById('customDialogSuccessIcon');
            const infoIcon = document.getElementById('customDialogInfoIcon');

            if (!modal) {
                // Fallback if modal HTML not present
                resolve(confirm(message));
                return;
            }

            titleEl.textContent = title;
            msgEl.textContent = message;
            confirmBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;
            cancelBtn.classList.remove('hidden');

            dangerIcon.classList.add('hidden');
            warningIcon.classList.add('hidden');
            successIcon.classList.add('hidden');
            infoIcon.classList.add('hidden');

            if (type === 'danger') {
                iconContainer.className = 'w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0';
                confirmBtn.className = 'px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-xs shadow-rose-200 transition-colors';
                dangerIcon.classList.remove('hidden');
            } else if (type === 'warning') {
                iconContainer.className = 'w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0';
                confirmBtn.className = 'px-4 py-2 rounded-xl bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 shadow-xs shadow-amber-200 transition-colors';
                warningIcon.classList.remove('hidden');
            } else {
                iconContainer.className = 'w-10 h-10 rounded-xl bg-indigo-100 text-[#5B5BD6] flex items-center justify-center shrink-0';
                confirmBtn.className = 'px-4 py-2 rounded-xl bg-[#5B5BD6] text-white text-xs font-semibold hover:bg-[#4B4BC0] shadow-xs transition-colors';
                infoIcon.classList.remove('hidden');
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }, 10);

            function cleanup(confirmed) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 100);
                confirmBtn.onclick = null;
                cancelBtn.onclick = null;
                modal.onclick = null;
                resolve(confirmed);
            }

            confirmBtn.onclick = () => cleanup(true);
            cancelBtn.onclick = () => cleanup(false);
            modal.onclick = (e) => {
                if (e.target === modal) cleanup(false);
            };
        });
    };

    window.showAlertDialog = function ({ title = 'Notice', message = '', btnText = 'Got it', type = 'info' }) {
        return new Promise((resolve) => {
            const modal = document.getElementById('customDialogModal');
            const card = document.getElementById('customDialogCard');
            const titleEl = document.getElementById('customDialogTitle');
            const msgEl = document.getElementById('customDialogMessage');
            const confirmBtn = document.getElementById('customDialogConfirmBtn');
            const cancelBtn = document.getElementById('customDialogCancelBtn');
            const iconContainer = document.getElementById('customDialogIconContainer');
            const dangerIcon = document.getElementById('customDialogDangerIcon');
            const warningIcon = document.getElementById('customDialogWarningIcon');
            const successIcon = document.getElementById('customDialogSuccessIcon');
            const infoIcon = document.getElementById('customDialogInfoIcon');

            if (!modal) {
                alert(message);
                resolve();
                return;
            }

            titleEl.textContent = title;
            msgEl.textContent = message;
            confirmBtn.textContent = btnText;
            cancelBtn.classList.add('hidden'); // No cancel button for alert notice

            dangerIcon.classList.add('hidden');
            warningIcon.classList.add('hidden');
            successIcon.classList.add('hidden');
            infoIcon.classList.add('hidden');

            if (type === 'danger' || type === 'error') {
                iconContainer.className = 'w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0';
                confirmBtn.className = 'px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-xs transition-colors';
                dangerIcon.classList.remove('hidden');
            } else if (type === 'warning') {
                iconContainer.className = 'w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0';
                confirmBtn.className = 'px-4 py-2 rounded-xl bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 shadow-xs transition-colors';
                warningIcon.classList.remove('hidden');
            } else if (type === 'success') {
                iconContainer.className = 'w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0';
                confirmBtn.className = 'px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 shadow-xs transition-colors';
                successIcon.classList.remove('hidden');
            } else {
                iconContainer.className = 'w-10 h-10 rounded-xl bg-indigo-100 text-[#5B5BD6] flex items-center justify-center shrink-0';
                confirmBtn.className = 'px-4 py-2 rounded-xl bg-[#5B5BD6] text-white text-xs font-semibold hover:bg-[#4B4BC0] shadow-xs transition-colors';
                infoIcon.classList.remove('hidden');
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }, 10);

            function cleanup() {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 100);
                confirmBtn.onclick = null;
                modal.onclick = null;
                resolve();
            }

            confirmBtn.onclick = cleanup;
            modal.onclick = (e) => {
                if (e.target === modal) cleanup();
            };
        });
    };

    window.deleteNode = async function (nodeId) {
        const node = graphData.nodes.find(n => n.id == nodeId);
        const nodeName = node ? `"${node.title || 'Selected Node'}"` : 'this node';

        const confirmed = await showConfirmDialog({
            title: 'Delete Node?',
            message: `Are you sure you want to delete ${nodeName} and all its connected wires? This step will be removed from your funnel flow.`,
            confirmText: 'Delete Node',
            cancelText: 'Cancel',
            type: 'danger'
        });

        if (confirmed) {
            const wasStart = node && node.is_start;
            graphData.nodes = graphData.nodes.filter(n => n.id !== nodeId);
            graphData.edges = (graphData.edges || []).filter(e => e.source_node_id !== nodeId && e.target_node_id !== nodeId);

            if (wasStart) {
                const nextStart = graphData.nodes.find(n => n.node_type !== 'destination');
                if (nextStart) {
                    nextStart.is_start = true;
                }
            }

            deselectAll();
            renderCanvas();
            markDirty();
        }
    };

    // Persistence & Status
    function markDirty() {
        isDirty = true;
        setSaveStatus('unsaved', 'Unsaved changes');

        if (autoSaveTimer) clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            saveGraphNow(true);
        }, 3000);
    }

    window.saveGraphNow = async function (isAuto = false) {
        setSaveStatus('saving', 'Saving...');
        try {
            // Track the currently selected node by key so we don't lose selection or desync IDs
            const prevSelectedNode = selectedNodeId ? findNode(selectedNodeId) : null;
            const prevSelectedKey = prevSelectedNode ? prevSelectedNode.node_key : null;

            const resp = await fetch(`${window.API_BASE}/api/funnels/${window.FUNNEL_ID}/graph`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.CSRF_TOKEN
                },
                body: JSON.stringify(graphData)
            });
            const json = await resp.json();
            if (json.success) {
                graphData = json.data;
                isDirty = false;
                setSaveStatus('saved', 'Saved');

                // Restore selectedNodeId using its stable node_key
                if (prevSelectedKey) {
                    const matched = graphData.nodes.find(n => n.node_key === prevSelectedKey);
                    if (matched) {
                        selectedNodeId = matched.id;
                    }
                }

                renderCanvas();

                // If inspector is currently viewing a node, re-render with fresh IDs while keeping scroll position
                if (selectedNodeId) {
                    renderInspector(true);
                }
            } else {
                setSaveStatus('error', json.error || 'Save failed');
            }
        } catch (e) {
            console.error('Save error:', e);
            setSaveStatus('error', 'Network error');
        }
    };

    function setSaveStatus(type, text) {
        let dotColor = 'bg-emerald-500';
        if (type === 'unsaved') dotColor = 'bg-amber-500';
        if (type === 'saving' || type === 'loading') dotColor = 'bg-indigo-500 animate-pulse';
        if (type === 'error') dotColor = 'bg-rose-500';

        saveIndicator.innerHTML = `
            <span class="w-2 h-2 rounded-full ${dotColor}"></span>
            <span>${text}</span>
        `;
    }

    // Validation Drawer
    window.openValidationDrawer = async function () {
        const drawer = document.getElementById('validationDrawer');
        const container = document.getElementById('validationResults');
        drawer.classList.remove('translate-x-full');
        container.innerHTML = '<p class="text-[#687080]">Checking flow connectivity...</p>';

        try {
            const resp = await fetch(`${window.API_BASE}/api/funnels/${window.FUNNEL_ID}/validate`);
            const json = await resp.json();
            if (json.success) {
                const val = json.data;
                if (val.is_valid && val.issues.length === 0) {
                    container.innerHTML = `
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 space-y-1">
                            <p class="font-bold flex items-center gap-1.5">✓ Flow is 100% Valid</p>
                            <p class="text-[11px]">All answer options connect to reachable destinations. The funnel is ready to publish.</p>
                        </div>
                    `;
                } else {
                    let html = `<div class="mb-3 font-semibold text-[#171A21]">${val.errors_count} error(s), ${val.warnings_count} warning(s) found:</div>`;
                    val.issues.forEach(issue => {
                        const isErr = issue.severity === 'error';
                        html += `
                            <div class="p-3 rounded-lg border ${isErr ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-amber-50 border-amber-200 text-amber-800'} space-y-1 cursor-pointer hover:opacity-90"
                                 onclick="focusNode(${issue.node_id})">
                                <p class="font-bold text-xs">${isErr ? '⛔ Error' : '⚠️ Warning'}</p>
                                <p class="text-[11px]">${escapeHtml(issue.message)}</p>
                                ${issue.node_id ? '<span class="text-[10px] underline font-semibold">Click to focus node →</span>' : ''}
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                }
            }
        } catch (e) {
            container.innerHTML = '<p class="text-rose-600">Failed to validate flow.</p>';
        }
    };

    window.closeValidationDrawer = function () {
        document.getElementById('validationDrawer').classList.add('translate-x-full');
    };

    window.focusNode = function (nodeId) {
        if (!nodeId) return;
        selectNode(nodeId);
        const node = graphData.nodes.find(n => n.id === nodeId);
        if (node) {
            panX = -node.position_x * scale + viewport.clientWidth / 2 - 120;
            panY = -node.position_y * scale + viewport.clientHeight / 2 - 80;
            applyTransform();
        }
        closeValidationDrawer();
    };

    // Version Publishing
    window.publishFunnel = async function () {
        // First save current graph
        await saveGraphNow();

        const btn = document.getElementById('publishBtn');
        btn.disabled = true;
        btn.textContent = 'Publishing...';

        try {
            const resp = await fetch(`${window.API_BASE}/admin/funnels/${window.FUNNEL_ID}/publish`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.CSRF_TOKEN
                }
            });
            const json = await resp.json();
            if (json.success) {
                await showAlertDialog({
                    title: 'Funnel Published!',
                    message: `Funnel published successfully as Version ${json.data.version_number}! Public URL is active.`,
                    type: 'success',
                    btnText: 'Awesome!'
                });
                window.location.reload();
            } else {
                await showAlertDialog({
                    title: 'Cannot Publish Funnel',
                    message: json.error || 'Please fix flow errors before publishing.',
                    type: 'danger',
                    btnText: 'Inspect Errors'
                });
                openValidationDrawer();
            }
        } catch (e) {
            showAlertDialog({
                title: 'Network Error',
                message: 'Publish request failed. Please check your network or server connection.',
                type: 'danger',
                btnText: 'Close'
            });
        } finally {
            btn.disabled = false;
            btn.textContent = 'Publish Version';
        }
    };

    // Live Quiz Simulation Preview Modal
    let previewStepNodeId = null;

    window.openPreviewModal = function () {
        const modal = document.getElementById('previewModal');
        const body = document.getElementById('previewModalBody');
        modal.classList.remove('hidden');

        // Find Starting Node
        let startNode = graphData.nodes.find(n => n.is_start);
        if (!startNode) {
            startNode = graphData.nodes.find(n => n.node_type !== 'destination');
        }
        if (!startNode) {
            body.innerHTML = `<p class="text-rose-600 text-xs text-center py-6 font-semibold">Cannot preview: No starting step found.</p>`;
            return;
        }

        renderPreviewStep(startNode.id);
    };

    window.closePreviewModal = function () {
        document.getElementById('previewModal').classList.add('hidden');
    };

    function renderPreviewStep(nodeId) {
        previewStepNodeId = nodeId;
        const body = document.getElementById('previewModalBody');
        const node = graphData.nodes.find(n => n.id == nodeId);

        if (!node) {
            body.innerHTML = `<p class="text-rose-600 text-xs text-center py-4">Error: Step node ${nodeId} not found in graph.</p>`;
            return;
        }

        if (node.node_type === 'destination') {
            const dest = node.destination || { name: 'Completion', url: 'https://example.com' };
            body.innerHTML = `
                <div class="text-center py-6 space-y-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xl flex items-center justify-center mx-auto">✓</div>
                    <h3 class="text-base font-bold text-[#171A21]">Quiz Finished!</h3>
                    <p class="text-xs text-[#687080]">You reached terminal destination:</p>
                    <div class="p-3 bg-[#F1F3F6] rounded-xl text-xs font-semibold text-[#171A21]">${escapeHtml(dest.name)}</div>
                    <a href="${dest.url}" target="_blank" class="inline-flex px-4 py-2 rounded-lg bg-[#5B5BD6] text-white text-xs font-semibold hover:bg-[#4B4BC0]">
                        Open Landing Page (${escapeHtml(dest.url)}) →
                    </a>
                </div>
            `;
            return;
        }

        if (node.node_type === 'form') {
            const form = node.form || {
                fields: [],
                submit_action: 'redirect',
                button_text: 'Submit Details',
                success_heading: 'Thank You!',
                success_message: 'Your submission has been received.'
            };
            const fields = form.fields || [];
            const submitAction = form.submit_action || 'redirect';

            let fieldsHtml = '';
            if (fields.length === 0) {
                fieldsHtml = `<div class="p-3 text-center text-xs text-amber-700 bg-amber-50 rounded-xl">No form fields configured for this step.</div>`;
            } else {
                fields.forEach(f => {
                    if (f.field_type === 'consent') {
                        fieldsHtml += `
                            <label class="flex items-start gap-2.5 cursor-pointer pt-1">
                                <input type="checkbox" ${f.is_required ? 'required' : ''} class="mt-0.5 rounded text-[#5B5BD6]">
                                <span class="text-xs text-[#687080] leading-snug">${escapeHtml(f.label)}${f.is_required ? ' *' : ''}</span>
                            </label>
                        `;
                    } else if (f.field_type === 'dropdown') {
                        fieldsHtml += `
                            <div class="space-y-1">
                                <label class="block font-semibold text-[#171A21] text-xs">${escapeHtml(f.label)}${f.is_required ? ' *' : ''}</label>
                                <select class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs">
                                    <option value="">${escapeHtml(f.placeholder || 'Select an option')}</option>
                                    <option value="1">Sample Option 1</option>
                                    <option value="2">Sample Option 2</option>
                                </select>
                            </div>
                        `;
                    } else {
                        const inputType = f.field_type === 'email' ? 'email' : (f.field_type === 'number' ? 'number' : 'text');
                        fieldsHtml += `
                            <div class="space-y-1">
                                <label class="block font-semibold text-[#171A21] text-xs">${escapeHtml(f.label)}${f.is_required ? ' *' : ''}</label>
                                <input type="${inputType}" placeholder="${escapeHtml(f.placeholder || '')}" ${f.is_required ? 'required' : ''}
                                       class="w-full px-3 py-2 rounded-lg border border-[#E4E7EC] text-xs">
                            </div>
                        `;
                    }
                });
            }

            body.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <span class="text-[10px] font-bold text-sky-600 uppercase tracking-wider">Form Step</span>
                        <h3 class="text-sm font-bold text-[#171A21] mt-0.5">${escapeHtml(node.title || 'Lead Capture Form')}</h3>
                    </div>

                    <form onsubmit="event.preventDefault(); submitPreviewForm(${node.id});" class="space-y-3 pt-1">
                        ${fieldsHtml}
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-[#5B5BD6] text-white text-xs font-semibold shadow hover:bg-[#4B4BC0] transition-colors mt-2">
                            ${escapeHtml(form.button_text || 'Submit Details')} →
                        </button>
                    </form>
                </div>
            `;
            return;
        }

        const q = node.question || { question_text: 'Question', options: [] };
        const options = q.options || [];

        let html = `
            <div class="space-y-4">
                <div>
                    <span class="text-[10px] font-bold text-[#5B5BD6] uppercase tracking-wider">Preview Step</span>
                    <h3 class="text-sm font-bold text-[#171A21] mt-0.5">${escapeHtml(q.question_text)}</h3>
                    ${q.help_text ? `<p class="text-xs text-[#687080] mt-1">${escapeHtml(q.help_text)}</p>` : ''}
                </div>

                <div class="space-y-2 pt-2">
        `;

        options.forEach(opt => {
            const edge = (graphData.edges || []).find(e => e.source_node_id == node.id && e.source_option_id == opt.id);
            const targetId = edge ? edge.target_node_id : null;

            html += `
                <button type="button" onclick="choosePreviewOption(${targetId})" 
                        class="w-full p-3 rounded-xl border border-[#E4E7EC] hover:border-[#5B5BD6] hover:bg-indigo-50/30 text-left text-xs font-medium text-[#171A21] flex items-center justify-between transition-all">
                    <span>${escapeHtml(opt.label)}</span>
                    <span class="text-[10px] text-slate-400">→</span>
                </button>
            `;
        });

        html += `</div></div>`;
        body.innerHTML = html;
    }

    window.submitPreviewForm = function (nodeId) {
        const node = graphData.nodes.find(n => n.id == nodeId);
        if (!node) return;
        const form = node.form || {};
        const submitAction = form.submit_action || 'redirect';

        if (submitAction === 'message') {
            const body = document.getElementById('previewModalBody');
            body.innerHTML = `
                <div class="text-center py-6 space-y-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xl flex items-center justify-center mx-auto">✓</div>
                    <h3 class="text-base font-bold text-[#171A21]">${escapeHtml(form.success_heading || 'Thank You!')}</h3>
                    <p class="text-xs text-[#687080] leading-relaxed max-w-sm mx-auto">${escapeHtml(form.success_message || 'Your submission has been received.')}</p>
                    <div class="pt-2">
                        <button type="button" onclick="openPreviewModal()" class="px-4 py-2 rounded-lg border border-slate-200 text-xs font-semibold text-[#171A21] hover:bg-slate-50">
                            Restart Preview
                        </button>
                    </div>
                </div>
            `;
        } else {
            const edge = (graphData.edges || []).find(e => e.source_node_id == nodeId);
            if (!edge || !edge.target_node_id) {
                showAlertDialog({
                    title: 'No Connected Route',
                    message: 'This form is set to redirect to the next step, but no outgoing wire is connected to a next question or destination.',
                    type: 'warning',
                    btnText: 'Got It'
                });
                return;
            }
            renderPreviewStep(edge.target_node_id);
        }
    };

    window.choosePreviewOption = function (targetNodeId) {
        if (!targetNodeId) {
            showAlertDialog({
                title: 'No Connected Route',
                message: 'This option does not lead anywhere yet. Connect its handle to a next question or destination on the canvas!',
                type: 'warning',
                btnText: 'Got It'
            });
            return;
        }
        renderPreviewStep(targetNodeId);
    };

    function escapeHtml(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Toast Notification Utility
    window.showToast = function (text, icon = '✨') {
        const container = document.getElementById('toastContainer');
        const textEl = document.getElementById('toastText');
        const iconEl = document.getElementById('toastIcon');
        if (!container || !textEl) return;

        textEl.textContent = text;
        if (iconEl) iconEl.textContent = icon;

        container.classList.remove('opacity-0', '-translate-y-2');
        container.classList.add('opacity-100', 'translate-y-0');

        if (window._toastTimer) clearTimeout(window._toastTimer);
        window._toastTimer = setTimeout(() => {
            container.classList.remove('opacity-100', 'translate-y-0');
            container.classList.add('opacity-0', '-translate-y-2');
        }, 2600);
    };

    // Copy Public Funnel Link
    window.copyFunnelUrl = function () {
        const slug = graphData.funnel ? graphData.funnel.slug : 'quiz';
        const url = `${window.location.origin}/quiz/${slug}`;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                showToast('Public quiz URL copied to clipboard! 📋');
            }).catch(() => {
                showToast(`Quiz URL: /quiz/${slug}`, '🔗');
            });
        } else {
            showToast(`Quiz URL: /quiz/${slug}`, '🔗');
        }
    };

    // Collapsible Hotkeys Accordion
    window.toggleHotkeysAccordion = function () {
        const content = document.getElementById('hotkeysContent');
        const chevron = document.getElementById('hotkeysChevron') || document.getElementById('hotkeysArrow');
        if (!content) return;
        const isHidden = content.classList.contains('hidden');
        if (isHidden) {
            content.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-90');
        } else {
            content.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-90');
        }
    };

    // Palette Drag & Canvas Drop
    window.handlePaletteDragStart = function (e, type) {
        e.dataTransfer.setData('text/plain', type);
        e.dataTransfer.effectAllowed = 'copy';
    };

    window.handleCanvasDragOver = function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        viewport.classList.add('canvas-drag-over');
    };

    window.handleCanvasDragLeave = function (e) {
        viewport.classList.remove('canvas-drag-over');
    };

    window.handleCanvasDrop = function (e) {
        e.preventDefault();
        viewport.classList.remove('canvas-drag-over');
        const type = e.dataTransfer.getData('text/plain');
        if (!['start', 'question', 'destination', 'form'].includes(type)) return;

        const rect = surface.getBoundingClientRect();
        const dropX = Math.max(20, Math.round((e.clientX - rect.left) / scale));
        const dropY = Math.max(20, Math.round((e.clientY - rect.top) / scale));

        addNodeToCanvas(type, dropX, dropY);
        showToast(`Added ${type} node to canvas!`, '➕');
    };

    // Mini-Map Component
    window.toggleMinimap = function () {
        const container = document.getElementById('minimapContainer');
        const openBtn = document.getElementById('minimapOpenBtn');
        if (!container) return;
        if (container.classList.contains('hidden')) {
            container.classList.remove('hidden');
            if (openBtn) openBtn.classList.add('hidden');
            updateMinimap();
        } else {
            container.classList.add('hidden');
            if (openBtn) openBtn.classList.remove('hidden');
        }
    };

    function setupMinimapEvents() {
        const minimapVp = document.getElementById('minimapViewport');
        if (!minimapVp) return;

        minimapVp.addEventListener('click', (e) => {
            const canvas = document.getElementById('minimapCanvas');
            if (!canvas || !graphData.nodes || graphData.nodes.length === 0) return;
            const rect = canvas.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const clickY = e.clientY - rect.top;

            let minX = 0, minY = 0, maxX = 1200, maxY = 800;
            graphData.nodes.forEach(n => {
                if (n.position_x < minX) minX = n.position_x;
                if (n.position_y < minY) minY = n.position_y;
                if (n.position_x + 280 > maxX) maxX = n.position_x + 280;
                if (n.position_y + 240 > maxY) maxY = n.position_y + 240;
            });
            minX -= 100; minY -= 100; maxX += 100; maxY += 100;
            const worldW = Math.max(maxX - minX, 600);
            const worldH = Math.max(maxY - minY, 400);
            const mapScale = Math.min(canvas.width / worldW, canvas.height / worldH);

            const worldTargetX = (clickX / mapScale) + minX;
            const worldTargetY = (clickY / mapScale) + minY;

            panX = -(worldTargetX * scale) + viewport.clientWidth / 2;
            panY = -(worldTargetY * scale) + viewport.clientHeight / 2;
            applyTransform();
        });
    }

    function updateMinimap() {
        const canvas = document.getElementById('minimapCanvas');
        const viewBox = document.getElementById('minimapViewBox');
        const container = document.getElementById('minimapContainer');
        if (!canvas || !viewBox || !container || container.classList.contains('hidden')) return;

        const ctx = canvas.getContext('2d');
        const cw = canvas.width;
        const ch = canvas.height;
        ctx.clearRect(0, 0, cw, ch);

        if (!graphData.nodes || graphData.nodes.length === 0) return;

        let minX = 0, minY = 0, maxX = 1200, maxY = 800;
        graphData.nodes.forEach(n => {
            if (n.position_x < minX) minX = n.position_x;
            if (n.position_y < minY) minY = n.position_y;
            if (n.position_x + 280 > maxX) maxX = n.position_x + 280;
            if (n.position_y + 240 > maxY) maxY = n.position_y + 240;
        });

        minX -= 100; minY -= 100; maxX += 100; maxY += 100;
        const worldW = Math.max(maxX - minX, 600);
        const worldH = Math.max(maxY - minY, 400);
        const mapScale = Math.min(cw / worldW, ch / worldH);

        // Draw connections on minimap
        ctx.strokeStyle = '#CBD5E1';
        ctx.lineWidth = 1;
        (graphData.edges || []).forEach(e => {
            const s = graphData.nodes.find(n => n.id == e.source_node_id);
            const t = graphData.nodes.find(n => n.id == e.target_node_id);
            if (!s || !t) return;
            const sx = (s.position_x + 250 - minX) * mapScale;
            const sy = (s.position_y + 40 - minY) * mapScale;
            const tx = (t.position_x - minX) * mapScale;
            const ty = (t.position_y + 30 - minY) * mapScale;
            ctx.beginPath();
            ctx.moveTo(sx, sy);
            ctx.lineTo(tx, ty);
            ctx.stroke();
        });

        // Draw node rectangles
        graphData.nodes.forEach(n => {
            const x = (n.position_x - minX) * mapScale;
            const y = (n.position_y - minY) * mapScale;
            const w = Math.max(256 * mapScale, 8);
            const h = Math.max(120 * mapScale, 6);

            if (n.node_type === 'start') {
                ctx.fillStyle = '#10B981';
            } else if (n.node_type === 'question') {
                ctx.fillStyle = '#6366F1';
            } else if (n.node_type === 'form') {
                ctx.fillStyle = '#0EA5E9';
            } else {
                ctx.fillStyle = '#A855F7';
            }

            if (selectedNodeId === n.id) {
                ctx.fillStyle = '#312E81';
            }

            ctx.beginPath();
            ctx.roundRect ? ctx.roundRect(x, y, w, h, 2) : ctx.rect(x, y, w, h);
            ctx.fill();
        });

        // Viewport rect calculation
        const vpRect = viewport.getBoundingClientRect();
        const curLeft = (-panX / scale);
        const curTop = (-panY / scale);
        const curWidth = vpRect.width / scale;
        const curHeight = vpRect.height / scale;

        const boxX = Math.max(0, Math.min(cw, (curLeft - minX) * mapScale));
        const boxY = Math.max(0, Math.min(ch, (curTop - minY) * mapScale));
        const boxW = Math.max(10, Math.min(cw, curWidth * mapScale));
        const boxH = Math.max(10, Math.min(ch, curHeight * mapScale));

        viewBox.style.left = `${boxX}px`;
        viewBox.style.top = `${boxY}px`;
        viewBox.style.width = `${boxW}px`;
        viewBox.style.height = `${boxH}px`;
    }

    // Auto-Arrange Flow (Hierarchical Tree Layout)
    window.autoArrangeNodes = function () {
        if (!graphData.nodes || graphData.nodes.length === 0) return;

        const nodeMap = new Map();
        graphData.nodes.forEach(n => nodeMap.set(n.id, n));

        const adj = new Map();
        const inDegree = new Map();
        graphData.nodes.forEach(n => {
            adj.set(n.id, []);
            inDegree.set(n.id, 0);
        });

        (graphData.edges || []).forEach(e => {
            if (adj.has(e.source_node_id) && nodeMap.has(e.target_node_id)) {
                adj.get(e.source_node_id).push(e.target_node_id);
                inDegree.set(e.target_node_id, (inDegree.get(e.target_node_id) || 0) + 1);
            }
        });

        let startNode = graphData.nodes.find(n => n.is_start) || graphData.nodes.find(n => n.node_type === 'start') || graphData.nodes.find(n => n.node_type !== 'destination');
        const queue = [];
        const layers = new Map();
        const visited = new Set();

        if (startNode) {
            queue.push({ id: startNode.id, layer: 0 });
            visited.add(startNode.id);
            layers.set(startNode.id, 0);
        } else {
            graphData.nodes.forEach(n => {
                if (inDegree.get(n.id) === 0 && !visited.has(n.id)) {
                    queue.push({ id: n.id, layer: 0 });
                    visited.add(n.id);
                    layers.set(n.id, 0);
                }
            });
        }

        while (queue.length > 0) {
            const { id, layer } = queue.shift();
            const neighbors = adj.get(id) || [];
            neighbors.forEach(nextId => {
                const currentLayer = layers.get(nextId) || 0;
                const newLayer = Math.max(currentLayer, layer + 1);
                layers.set(nextId, newLayer);
                if (!visited.has(nextId)) {
                    visited.add(nextId);
                    queue.push({ id: nextId, layer: newLayer });
                }
            });
        }

        let maxLayer = 0;
        layers.forEach(l => { if (l > maxLayer) maxLayer = l; });
        graphData.nodes.forEach(n => {
            if (!visited.has(n.id)) {
                maxLayer++;
                layers.set(n.id, maxLayer);
                visited.add(n.id);
            }
        });

        const layerBuckets = new Map();
        graphData.nodes.forEach(n => {
            const l = layers.get(n.id) || 0;
            if (!layerBuckets.has(l)) layerBuckets.set(l, []);
            layerBuckets.get(l).push(n);
        });

        const startX = 60;
        const colSpacing = 340;
        const rowSpacing = 220;

        const sortedLayers = Array.from(layerBuckets.keys()).sort((a, b) => a - b);
        let maxBucketCount = 1;
        sortedLayers.forEach(l => {
            const count = layerBuckets.get(l).length;
            if (count > maxBucketCount) maxBucketCount = count;
        });

        const centerTargetY = Math.max(160, (maxBucketCount * rowSpacing) / 2);

        sortedLayers.forEach(layerIdx => {
            const nodesInLayer = layerBuckets.get(layerIdx);
            const totalHeight = (nodesInLayer.length - 1) * rowSpacing;
            const layerStartY = Math.max(60, centerTargetY - (totalHeight / 2));

            nodesInLayer.forEach((node, idx) => {
                node.position_x = startX + (layerIdx * colSpacing);
                node.position_y = layerStartY + (idx * rowSpacing);

                const el = document.getElementById(`node-${node.id}`);
                if (el) {
                    el.style.left = `${node.position_x}px`;
                    el.style.top = `${node.position_y}px`;
                }
            });
        });

        renderWires();
        updateMinimap();
        centerCanvas();
        markDirty();
        showToast('Auto-arranged flow into a neat tree layout!', '✨');
    };

})();
