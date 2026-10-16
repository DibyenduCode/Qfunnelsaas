<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-[#171A21]">Create Funnel</h2>
            <p class="text-xs text-[#687080]">Configure your new branching quiz funnel</p>
        </div>
        <a href="<?= url('/admin/funnels') ?>" class="text-xs text-[#687080] hover:text-[#171A21]">← Back to Funnels</a>
    </div>

    <div class="bg-white border border-[#E4E7EC] rounded-xl p-6 shadow-sm">
        <form action="<?= url('/admin/funnels/create') ?>" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div>
                <label for="name" class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Funnel Title *</label>
                <input type="text" id="name" name="name" value="<?= e(old('name')) ?>" placeholder="e.g. Real Estate Client Qualification Quiz" required
                       class="w-full px-3.5 py-2 rounded-lg border border-[#E4E7EC] text-sm text-[#171A21] focus:outline-none focus:ring-2 focus:ring-[#5B5BD6]">
            </div>

            <div>
                <label for="slug" class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Public URL Slug</label>
                <div class="flex items-center">
                    <span class="px-3 py-2 bg-[#F1F3F6] border border-r-0 border-[#E4E7EC] rounded-l-lg text-xs text-[#687080]">/quiz/</span>
                    <input type="text" id="slug" name="slug" value="<?= e(old('slug')) ?>" placeholder="client-quiz (auto-generated if empty)"
                           class="w-full px-3.5 py-2 rounded-r-lg border border-[#E4E7EC] text-sm text-[#171A21] focus:outline-none focus:ring-2 focus:ring-[#5B5BD6]">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold text-[#171A21] uppercase mb-1">Description / Internal Notes</label>
                <textarea id="description" name="description" rows="3" placeholder="Brief explanation of this funnel's target audience..."
                          class="w-full px-3.5 py-2 rounded-lg border border-[#E4E7EC] text-sm text-[#171A21] focus:outline-none focus:ring-2 focus:ring-[#5B5BD6]"><?= e(old('description')) ?></textarea>
            </div>

            <div class="pt-4 border-t border-[#E4E7EC] flex items-center justify-end gap-3">
                <a href="<?= url('/admin/funnels') ?>" class="px-4 py-2 rounded-lg border border-[#E4E7EC] text-xs font-semibold text-[#171A21] hover:bg-[#F7F8FA]">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#5B5BD6] text-white text-xs font-semibold shadow hover:bg-[#4B4BC0]">
                    Create & Open Builder
                </button>
            </div>
        </form>
    </div>
</div>
