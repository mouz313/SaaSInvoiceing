@extends('layouts.admin')

@section('title', 'FAQ Knowledge Base Manager')

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    currentFaq: { id: '', question: '', answer: '', category: 'general', order: 0, is_published: true },
    openCreate() {
        this.editMode = false;
        this.currentFaq = { id: '', question: '', answer: '', category: 'general', order: 0, is_published: true };
        this.modalOpen = true;
    },
    openEdit(faq) {
        this.editMode = true;
        this.currentFaq = { ...faq };
        this.modalOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">FAQ Manager</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Add, update, categorize, and reorder public FAQ questions.</p>
        </div>
        <div>
            <button @click="openCreate()" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add FAQ Question</span>
            </button>
        </div>
    </div>

    <!-- FAQ Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-extrabold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-4 px-6">Order</th>
                        <th class="py-4 px-6">Category</th>
                        <th class="py-4 px-6">Question & Answer Preview</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($faqs as $faq)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-4 px-6 font-mono text-xs text-slate-400 font-bold">
                                #{{ $faq->order }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $faq->category }}
                                </span>
                            </td>
                            <td class="py-4 px-6 max-w-md">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $faq->question }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $faq->answer }}</p>
                            </td>
                            <td class="py-4 px-6">
                                @if($faq->is_published)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        Hidden
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit({{ $faq->toJson() }})" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/60 transition" title="Edit">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    <form action="{{ route('admin.cms.faqs.delete', $faq) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this FAQ?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/60 transition" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                No FAQs created yet. Click "Add FAQ Question" to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Create / Edit -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl space-y-5" @click.away="modalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <h3 class="text-lg font-black text-slate-900 dark:text-white" x-text="editMode ? 'Edit FAQ Question' : 'Create New FAQ'"></h3>
                <button @click="modalOpen = false" class="p-1 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="editMode ? '{{ url('/admin/cms/faqs') }}/' + currentFaq.id : '{{ route('admin.cms.faqs.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Question *</label>
                    <input type="text" name="question" x-model="currentFaq.question" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Category *</label>
                        <select name="category" x-model="currentFaq.category" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="general">General</option>
                            <option value="billing">Billing & Packages</option>
                            <option value="features">Features & PDF</option>
                            <option value="security">Security & Auth</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Sort Order</label>
                        <input type="number" name="order" x-model="currentFaq.order" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Answer *</label>
                    <textarea name="answer" x-model="currentFaq.answer" rows="4" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_published" id="faq_is_published" value="1" :checked="currentFaq.is_published" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                    <label for="faq_is_published" class="text-xs font-bold text-slate-800 dark:text-slate-200">
                        Published (visible to public)
                    </label>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="modalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md">
                        Save Question
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
