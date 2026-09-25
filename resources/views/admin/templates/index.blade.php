@extends('layouts.admin')

@section('title', 'Invoice Template Catalog & Pricing Manager')

@section('content')
<div class="space-y-8" x-data="templateManager()">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">Invoice Templates Catalog</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Manage 25 designer invoice themes, set individual prices ($0 for free), and configure store availability.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('templates.index') }}" target="_blank" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-850 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center gap-2">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                View Client Marketplace
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white dark:bg-slate-850 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Themes</p>
                <span class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center">
                    <i data-lucide="palette" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-2">{{ $templates->count() }} Designs</p>
            <p class="text-xs text-slate-400 mt-1">All pre-built & DomPDF ready</p>
        </div>

        <div class="bg-white dark:bg-slate-850 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Active in Store</p>
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ $activeCount }} Active</p>
            <p class="text-xs text-slate-400 mt-1">Visible to clients</p>
        </div>

        <div class="bg-white dark:bg-slate-850 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Template Sales</p>
                <span class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-600 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-2">{{ $totalSales }} Unlocks</p>
            <p class="text-xs text-slate-400 mt-1">Paid & free acquisitions</p>
        </div>

        <div class="bg-white dark:bg-slate-850 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Template Revenue</p>
                <span class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-2">${{ number_format($totalRevenue, 2) }}</p>
            <p class="text-xs text-slate-400 mt-1">Direct template earnings</p>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="bg-white dark:bg-slate-850 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="font-black text-slate-900 dark:text-white text-base">All 25 Invoice Templates</h3>
            <span class="text-xs text-slate-400 font-semibold">Click Edit to modify price or status</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4">Template Info</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Pricing</th>
                        <th class="py-3.5 px-4 text-center">Sales / Unlocks</th>
                        <th class="py-3.5 px-4 text-center">Store Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($templates as $tmpl)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 text-center font-mono text-slate-400">
                                {{ $tmpl->sort_order }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-mono font-bold text-slate-700 dark:text-slate-300">
                                        <i data-lucide="layout" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white text-sm block">{{ $tmpl->name }}</span>
                                        <span class="font-mono text-[11px] text-slate-400">{{ $tmpl->slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $tmpl->category }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($tmpl->is_free)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        Free
                                    </span>
                                @else
                                    <span class="font-black text-sm text-slate-900 dark:text-white">
                                        ${{ number_format($tmpl->price, 2) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 ml-1 font-semibold uppercase">{{ $tmpl->currency }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-700 dark:text-slate-300">
                                {{ $tmpl->purchases_count }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($tmpl->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('templates.preview', $tmpl->slug) }}" target="_blank" 
                                       class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                       title="Live Protected Preview">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>

                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($tmpl) }})"
                                            class="px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 font-bold hover:bg-blue-100 dark:hover:bg-blue-900/60 transition flex items-center gap-1.5">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('admin.templates.toggle-active', $tmpl->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                                title="{{ $tmpl->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i data-lucide="{{ $tmpl->is_active ? 'pause-circle' : 'play-circle' }}" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Template Modal -->
    <div x-show="editModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-850 rounded-3xl max-w-lg w-full p-6 shadow-2xl relative border border-slate-200 dark:border-slate-800"
             @click.outside="editModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-black text-slate-900 dark:text-white">Edit Template Pricing & Metadata</h3>
                <button type="button" @click="editModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/admin/templates/' + currentTemplate.id" method="POST" class="mt-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Template Name</label>
                    <input type="text" name="name" x-model="currentTemplate.name" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Category</label>
                        <select name="category" x-model="currentTemplate.category" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Corporate">Corporate</option>
                            <option value="Creative">Creative</option>
                            <option value="Minimal">Minimal</option>
                            <option value="Tech & SaaS">Tech & SaaS</option>
                            <option value="Industry">Industry</option>
                            <option value="Specialty">Specialty</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Price (USD)</label>
                        <input type="number" step="0.01" min="0" name="price" x-model="currentTemplate.price" required
                               :disabled="currentTemplate.is_free"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-semibold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                    </div>
                </div>

                <div class="flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_free" value="1" x-model="currentTemplate.is_free"
                               @change="if(currentTemplate.is_free) currentTemplate.price = 0"
                               class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Free Template ($0)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="currentTemplate.is_active"
                               class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Active in Store</span>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Description / Tagline</label>
                    <textarea name="description" rows="3" x-model="currentTemplate.description"
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="mt-6 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition shadow-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function templateManager() {
    return {
        editModalOpen: false,
        currentTemplate: {},
        openEditModal(template) {
            this.currentTemplate = Object.assign({}, template);
            this.currentTemplate.is_free = Boolean(template.is_free);
            this.currentTemplate.is_active = Boolean(template.is_active);
            this.editModalOpen = true;
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }
    };
}
</script>
@endsection