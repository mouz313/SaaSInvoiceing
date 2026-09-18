@extends('layouts.admin')

@section('title', 'Contact Inquiries & Leads')

@section('content')
<div class="space-y-6" x-data="{
    detailModal: false,
    selectedInquiry: null,
    viewInquiry(inquiry) {
        this.selectedInquiry = inquiry;
        this.detailModal = true;
    }
}">

    <!-- Header & Filter Pills -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Contact Inquiries</h1>
                @if($unreadCount > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-500 text-white animate-pulse">
                        {{ $unreadCount }} New
                    </span>
                @endif
            </div>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Review inquiries received from the public Contact Us form.</p>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 p-1.5 rounded-2xl text-xs font-bold">
            <a href="{{ route('admin.cms.inquiries', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-xl transition {{ $status === 'all' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                All
            </a>
            <a href="{{ route('admin.cms.inquiries', ['status' => 'unread']) }}" class="px-3 py-1.5 rounded-xl transition {{ $status === 'unread' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                Unread
            </a>
            <a href="{{ route('admin.cms.inquiries', ['status' => 'read']) }}" class="px-3 py-1.5 rounded-xl transition {{ $status === 'read' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                Read
            </a>
            <a href="{{ route('admin.cms.inquiries', ['status' => 'replied']) }}" class="px-3 py-1.5 rounded-xl transition {{ $status === 'replied' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                Replied
            </a>
        </div>
    </div>

    <!-- Inquiries List / Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] uppercase font-extrabold text-slate-400 tracking-wider">
                    <tr>
                        <th class="py-4 px-6">Sender Details</th>
                        <th class="py-4 px-6">Topic / Subject</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Received</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @forelse($inquiries as $inquiry)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition {{ $inquiry->status === 'unread' ? 'bg-blue-50/30 dark:bg-blue-950/20' : '' }}">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center font-bold text-xs uppercase">
                                        {{ substr($inquiry->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $inquiry->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $inquiry->email }}</p>
                                        @if($inquiry->phone)
                                            <p class="text-[11px] text-slate-400">{{ $inquiry->phone }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 max-w-xs">
                                <p class="font-bold text-slate-900 dark:text-white truncate">{{ $inquiry->subject }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $inquiry->message }}</p>
                            </td>
                            <td class="py-4 px-6">
                                @if($inquiry->status === 'unread')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                        Unread
                                    </span>
                                @elseif($inquiry->status === 'read')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        Read
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                        Replied
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-400">
                                {{ $inquiry->created_at->diffForHumans() }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="viewInquiry({{ $inquiry->toJson() }})" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/60 transition" title="Read Message">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    <form action="{{ route('admin.cms.inquiries.delete', $inquiry) }}" method="POST" onsubmit="return confirm('Delete this inquiry message?');" class="inline">
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
                                No contact inquiries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inquiries->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $inquiries->links() }}
            </div>
        @endif
    </div>

    <!-- Inquiry Message Detail Modal -->
    <div x-show="detailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl space-y-6" @click.away="detailModal = false" x-show="selectedInquiry">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white" x-text="selectedInquiry?.subject"></h3>
                    <p class="text-xs text-slate-400" x-text="'From: ' + selectedInquiry?.name + ' (' + selectedInquiry?.email + ')'"></p>
                </div>
                <button @click="detailModal = false" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Message Body -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 text-sm text-slate-700 dark:text-slate-200 leading-relaxed whitespace-pre-wrap max-h-60 overflow-y-auto" x-text="selectedInquiry?.message">
            </div>

            <!-- Status Form & Direct Reply Action -->
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 dark:border-slate-800">
                <form :action="'{{ url('/admin/cms/inquiries') }}/' + selectedInquiry?.id + '/status'" method="POST" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold">
                        <option value="unread" :selected="selectedInquiry?.status === 'unread'">Unread</option>
                        <option value="read" :selected="selectedInquiry?.status === 'read'">Read</option>
                        <option value="replied" :selected="selectedInquiry?.status === 'replied'">Replied</option>
                    </select>
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold">
                        Update
                    </button>
                </form>

                <a :href="'mailto:' + selectedInquiry?.email + '?subject=Re: ' + encodeURIComponent(selectedInquiry?.subject)" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                    <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                    <span>Reply via Email</span>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
