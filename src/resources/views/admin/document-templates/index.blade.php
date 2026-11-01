<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- Header --}}

            <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                <div>

                    <p class="mb-2 text-sm font-bold text-cyan-400">
                        HAG Brokers
                    </p>

                    <h1 class="text-3xl font-black">
                        {{ $isAr ? 'قوالب المستندات' : 'Document Templates' }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-400">
                        {{ $isAr
                            ? 'أنشئ وأدر قوالب المستندات المستخدمة في منصة الشحن.'
                            : 'Create and manage reusable documents for your freight platform.' }}
                    </p>

                </div>

                <a href="{{ route('admin.document-templates.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-400/10 transition hover:bg-cyan-300">

                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14M5 12h14" />
                    </svg>

                    {{ $isAr ? 'قالب جديد' : 'New Template' }}

                </a>

            </div>


            {{-- Templates --}}

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

                @forelse ($templates as $template)
                    <div
                        class="group rounded-2xl border border-white/10 bg-white/[0.03] p-5 backdrop-blur-xl transition hover:border-cyan-400/20 hover:bg-white/[0.05]">

                        <div class="flex items-start justify-between gap-4">

                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-400/10 text-cyan-400">

                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M7 3.75h7.5L19 8.25v12A1.75 1.75 0 0117.25 22h-10.5A1.75 1.75 0 015 20.25V5.5A1.75 1.75 0 016.75 3.75H7z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                        d="M14 3.75v5h5" />
                                </svg>

                            </div>

                            <span
                                class="rounded-full px-3 py-1 text-xs font-black {{ $template->is_active ? 'bg-emerald-400/10 text-emerald-300' : 'bg-slate-700 text-slate-400' }}">
                                {{ $template->is_active ? ($isAr ? 'نشط' : 'Active') : ($isAr ? 'متوقف' : 'Inactive') }}
                            </span>

                        </div>


                        <h2 class="mt-5 text-lg font-black text-white">
                            {{ $template->name }}
                        </h2>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ $template->slug }}
                        </p>


                        <div class="mt-5 grid grid-cols-2 gap-3">

                            <div class="rounded-xl bg-slate-900/70 p-3">

                                <p class="text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'النوع' : 'Type' }}
                                </p>

                                <p class="mt-1 truncate text-sm font-bold text-slate-200">
                                    {{ $template->document_type ?: 'General' }}
                                </p>

                            </div>


                            <div class="rounded-xl bg-slate-900/70 p-3">

                                <p class="text-[10px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    {{ $isAr ? 'الإصدار' : 'Version' }}
                                </p>

                                <p class="mt-1 text-sm font-bold text-cyan-300">
                                    v{{ $template->version }}
                                </p>

                            </div>

                        </div>


                        <div class="mt-5 flex gap-2">

                            <a href="{{ route('admin.document-templates.edit', $template) }}"
                                class="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-white/10">
                                {{ $isAr ? 'تعديل' : 'Edit' }}
                            </a>

                            <a href="{{ route('admin.documents.pdf.create', [
                                'template' => $template->id,
                            ]) }}"
                                class="flex-1 rounded-xl bg-cyan-400 px-4 py-2.5 text-center text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                                {{ $isAr ? 'إنشاء PDF' : 'Generate PDF' }}
                            </a>

                        </div>

                    </div>

                @empty

                    <div class="col-span-full rounded-2xl border border-dashed border-white/10 p-12 text-center">

                        <h3 class="font-black text-white">
                            {{ $isAr ? 'لا توجد قوالب' : 'No templates yet' }}
                        </h3>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ $isAr ? 'ابدأ بإنشاء أول قالب للمستندات.' : 'Create your first document template.' }}
                        </p>

                    </div>
                @endforelse

            </div>


            <div class="mt-8">
                {{ $templates->links() }}
            </div>

        </div>

    </div>

</x-admin-layout>
