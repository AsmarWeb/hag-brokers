<x-admin-layout>
    @php
        $isAr = app()->getLocale() === 'ar';

        $t = [
            'title' => $isAr ? 'جهات الاتصال' : 'Contacts',
            'subtitle' => $isAr
                ? 'إدارة جهات الاتصال المرتبطة بالعملاء'
                : 'Manage customer contacts and representatives',
            'add' => $isAr ? 'إضافة جهة اتصال' : 'Add Contact',
            'search' => $isAr ? 'بحث...' : 'Search...',
            'all_customers' => $isAr ? 'كل العملاء' : 'All Customers',
            'name' => $isAr ? 'الاسم' : 'Name',
            'customer' => $isAr ? 'العميل' : 'Customer',
            'job_title' => $isAr ? 'المسمى الوظيفي' : 'Job Title',
            'email' => $isAr ? 'البريد الإلكتروني' : 'Email',
            'phone' => $isAr ? 'الهاتف' : 'Phone',
            'whatsapp' => $isAr ? 'واتساب' : 'WhatsApp',
            'primary' => $isAr ? 'أساسي' : 'Primary',
            'actions' => $isAr ? 'الإجراءات' : 'Actions',
            'yes' => $isAr ? 'نعم' : 'Yes',
            'no' => $isAr ? 'لا' : 'No',
            'view' => $isAr ? 'عرض' : 'View',
            'edit' => $isAr ? 'تعديل' : 'Edit',
            'delete' => $isAr ? 'حذف' : 'Delete',
            'no_contacts' => $isAr ? 'لا توجد جهات اتصال حتى الآن.' : 'No contacts found.',
            'confirm_delete' => $isAr
                ? 'هل أنت متأكد من حذف جهة الاتصال؟'
                : 'Are you sure you want to delete this contact?',
            'showing' => $isAr ? 'عرض' : 'Showing',
            'of' => $isAr ? 'من' : 'of',
            'contacts' => $isAr ? 'جهات اتصال' : 'contacts',
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">
                    {{ $t['title'] }}
                </h1>

                <p class="mt-1 text-sm text-slate-400">
                    {{ $t['subtitle'] }}
                </p>
            </div>

            <a href="{{ route('admin.contacts.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-cyan-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>

                {{ $t['add'] }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">

        {{-- Filters --}}
        <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4 shadow-xl">
            <form method="GET" action="{{ route('admin.contacts.index') }}"
                class="grid grid-cols-1 gap-4 md:grid-cols-3">

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-300">
                        {{ $t['search'] }}
                    </label>

                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ $t['search'] }}"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none transition focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-300">
                        {{ $t['customer'] }}
                    </label>

                    <select name="customer_id"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white outline-none transition focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">

                        <option value="">
                            {{ $t['all_customers'] }}
                        </option>

                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>
                                {{ $customer->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="w-full rounded-xl border border-cyan-400/30 bg-cyan-400/10 px-4 py-2.5 text-sm font-bold text-cyan-300 transition hover:bg-cyan-400/20">
                        {{ $isAr ? 'تطبيق البحث' : 'Apply Filters' }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/70 shadow-xl">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10">

                    <thead class="bg-slate-950/70">
                        <tr>
                            <th class="px-5 py-4 text-start text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['name'] }}
                            </th>

                            <th class="px-5 py-4 text-start text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['customer'] }}
                            </th>

                            <th class="px-5 py-4 text-start text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['job_title'] }}
                            </th>

                            <th class="px-5 py-4 text-start text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['email'] }}
                            </th>

                            <th class="px-5 py-4 text-start text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['phone'] }}
                            </th>

                            <th class="px-5 py-4 text-start text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['primary'] }}
                            </th>

                            <th class="px-5 py-4 text-end text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ $t['actions'] }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">

                        @forelse($contacts as $contact)
                            <tr class="transition hover:bg-white/[0.03]">

                                <td class="whitespace-nowrap px-5 py-4">
                                    <div class="font-semibold text-white">
                                        {{ $contact->name }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @if ($contact->customer)
                                        <a href="{{ route('admin.customers.show', $contact->customer) }}"
                                            class="text-cyan-300 transition hover:text-cyan-200">
                                            {{ $contact->customer->company_name }}
                                        </a>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-300">
                                    {{ $contact->job_title ?: '—' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-300">
                                    {{ $contact->email ?: '—' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-300">
                                    {{ $contact->phone ?: '—' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @if ($contact->is_primary)
                                        <span
                                            class="inline-flex rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-bold text-emerald-300">
                                            {{ $t['yes'] }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-slate-400/10 px-3 py-1 text-xs font-bold text-slate-400">
                                            {{ $t['no'] }}
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-end">
                                    <div class="flex items-center justify-end gap-2">

                                        <a href="{{ route('admin.contacts.show', $contact) }}"
                                            class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white">
                                            {{ $t['view'] }}
                                        </a>

                                        <a href="{{ route('admin.contacts.edit', $contact) }}"
                                            class="rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-3 py-2 text-xs font-semibold text-cyan-300 transition hover:bg-cyan-400/20">
                                            {{ $t['edit'] }}
                                        </a>

                                        <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}"
                                            onsubmit="return confirm('{{ $t['confirm_delete'] }}')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="rounded-lg border border-red-400/20 bg-red-400/10 px-3 py-2 text-xs font-semibold text-red-300 transition hover:bg-red-400/20">
                                                {{ $t['delete'] }}
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center">

                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M17 20h5V4H2v16h5m10 0v-5H7v5m10 0H7" />
                                        </svg>
                                    </div>

                                    <p class="mt-4 text-sm text-slate-400">
                                        {{ $t['no_contacts'] }}
                                    </p>

                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            @if ($contacts->hasPages())
                <div class="border-t border-white/10 px-5 py-4">
                    {{ $contacts->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>
</x-admin-layout>
