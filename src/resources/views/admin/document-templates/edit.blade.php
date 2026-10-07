<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <a href="{{ route('admin.document-templates.index') }}"
                        class="text-sm font-bold text-slate-400 hover:text-cyan-400">
                        ← {{ $isAr ? 'القوالب' : 'Templates' }}
                    </a>

                    <h1 class="mt-4 text-3xl font-black">
                        {{ $isAr ? 'تعديل القالب' : 'Edit Template' }}
                    </h1>

                </div>

                <div class="rounded-xl bg-cyan-400/10 px-4 py-3 text-sm font-black text-cyan-300">
                    v{{ $template->version }}
                </div>

            </div>


            <form method="POST" action="{{ route('admin.document-templates.update', $template) }}" class="space-y-6">

                @csrf
                @method('PUT')


                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>

                            <label class="text-sm font-bold text-slate-300">
                                {{ $isAr ? 'اسم القالب' : 'Template Name' }}
                            </label>

                            <input name="name" value="{{ old('name', $template->name) }}" required
                                class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">

                        </div>


                        <div>

                            <label class="text-sm font-bold text-slate-300">
                                Slug
                            </label>

                            <input name="slug" value="{{ old('slug', $template->slug) }}" required
                                class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">

                        </div>


                        <div>

                            <label class="text-sm font-bold text-slate-300">
                                Entity Type
                            </label>

                            <select name="entity_type"
                                class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">

                                @foreach ([
        'generic' => 'Generic',
        'customer' => 'Customer',
        'shipment' => 'Shipment',
        'quotation' => 'Quotation',
    ] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('entity_type', $template->entity_type) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach

                            </select>

                        </div>


                        <div>

                            <label class="text-sm font-bold text-slate-300">
                                Document Type
                            </label>

                            <input name="document_type" value="{{ old('document_type', $template->document_type) }}"
                                class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white">

                        </div>

                    </div>

                </div>


                <div class="rounded-2xl border border-cyan-400/20 bg-cyan-400/[0.03] p-6">

                    <label class="mb-3 block text-sm font-black text-cyan-300">
                        Content
                    </label>

                    <textarea name="content" rows="20" required
                        class="w-full rounded-xl border border-white/10 bg-slate-950 p-5 font-mono text-sm leading-7 text-slate-200">{{ old('content', $template->content) }}</textarea>

                </div>


                <div class="grid gap-6 lg:grid-cols-2">

                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">

                        <label class="mb-3 block text-sm font-black">
                            Header
                        </label>

                        <textarea name="header" rows="8"
                            class="w-full rounded-xl border border-white/10 bg-slate-900 p-4 font-mono text-sm text-slate-200">{{ old('header', $template->header) }}</textarea>

                    </div>


                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">

                        <label class="mb-3 block text-sm font-black">
                            Footer
                        </label>

                        <textarea name="footer" rows="8"
                            class="w-full rounded-xl border border-white/10 bg-slate-900 p-4 font-mono text-sm text-slate-200">{{ old('footer', $template->footer) }}</textarea>

                    </div>

                </div>


                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">

                    <label class="mb-3 block text-sm font-black">
                        CSS
                    </label>

                    <textarea name="styles" rows="10"
                        class="w-full rounded-xl border border-white/10 bg-slate-900 p-4 font-mono text-xs text-slate-200">{{ old('styles', $template->styles) }}</textarea>

                </div>


                <label class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-4">

                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active))
                        class="h-5 w-5 rounded bg-slate-900 text-cyan-400">

                    <span class="text-sm font-bold">
                        {{ $isAr ? 'القالب نشط' : 'Template is active' }}
                    </span>

                </label>


                <div class="flex flex-wrap justify-end gap-3">

                    <a href="{{ route('admin.documents.pdf.create', [
                        'template' => $template->id,
                    ]) }}"
                        class="rounded-xl border border-cyan-400/20 bg-cyan-400/10 px-5 py-3 text-sm font-black text-cyan-300">
                        {{ $isAr ? 'إنشاء PDF' : 'Generate PDF' }}
                    </a>

                    <button type="submit"
                        class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-black text-slate-950 hover:bg-cyan-300">
                        {{ $isAr ? 'حفظ التغييرات' : 'Save Changes' }}
                    </button>

                </div>

            </form>


            <div class="mt-6 flex justify-end">

                <form method="POST" action="{{ route('admin.document-templates.destroy', $template) }}"
                    onsubmit="return confirm('{{ $isAr ? 'هل أنت متأكد من حذف هذا القالب؟' : 'Delete this template?' }}')">

                    @csrf
                    @method('DELETE')

                    <button type="submit" class="text-sm font-bold text-red-400 hover:text-red-300">
                        {{ $isAr ? 'حذف القالب' : 'Delete Template' }}
                    </button>

                </form>

            </div>

        </div>

    </div>

</x-admin-layout>
