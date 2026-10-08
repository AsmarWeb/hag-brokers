<x-admin-layout>

    @php
        $isAr = app()->getLocale() === 'ar';
    @endphp

    <div class="min-h-screen bg-slate-950 text-white" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

            <div class="mb-8">

                <a href="{{ route('admin.document-templates.index') }}"
                    class="text-sm font-bold text-slate-400 hover:text-cyan-400">
                    ← {{ $isAr ? 'القوالب' : 'Templates' }}
                </a>

                <h1 class="mt-4 text-3xl font-black">
                    {{ $isAr ? 'إنشاء قالب مستند' : 'Create Document Template' }}
                </h1>

            </div>


            <form method="POST" action="{{ route('admin.document-templates.store') }}" class="space-y-6">

                @csrf


                {{-- Basic --}}

                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">

                    <h2 class="mb-5 text-lg font-black">
                        {{ $isAr ? 'معلومات القالب' : 'Template Information' }}
                    </h2>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>

                            <label class="text-sm font-bold text-slate-300">
                                {{ $isAr ? 'اسم القالب' : 'Template Name' }}
                            </label>

                            <input name="name" value="{{ old('name') }}" required placeholder="Commercial Invoice"
                                class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                        </div>


                        <div>

                            <label class="text-sm font-bold text-slate-300">
                                Slug
                            </label>

                            <input name="slug" value="{{ old('slug') }}" placeholder="commercial-invoice"
                                class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400">

                        </div>


                        <div>

                            <label class="text-sm font-bold text-slate-300">
                                {{ $isAr ? 'الكيان' : 'Entity Type' }}
                            </label>

                            <select name="entity_type"
                                class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-white">

                                <option value="generic">Generic</option>
                                <option value="customer">Customer</option>
                                <option value="shipment">Shipment</option>
                                <option value="quotation">Quotation</option>

                            </select>

                        </div>


                        <div>

                            <label class="text-sm font-bold text-slate-300">
                                {{ $isAr ? 'نوع المستند' : 'Document Type' }}
                            </label>

                            <input name="document_type" value="{{ old('document_type') }}"
                                placeholder="Commercial Invoice"
                                class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-white">

                        </div>

                    </div>

                </div>


                {{-- Header --}}

                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">

                    <h2 class="mb-2 text-lg font-black">
                        {{ $isAr ? 'رأس المستند' : 'Document Header' }}
                    </h2>

                    <p class="mb-4 text-sm text-slate-500">
                        HTML اختياري.
                    </p>

                    <textarea name="header" rows="5"
                        class="w-full rounded-xl border border-white/10 bg-slate-900 p-4 font-mono text-sm text-slate-200 outline-none focus:border-cyan-400"
                        placeholder="<div>...</div>">{{ old('header') }}</textarea>

                </div>



                {{-- Content --}}

                <div class="rounded-2xl border border-cyan-400/20 bg-cyan-400/[0.03] p-6">

                    <h2 class="mb-2 text-lg font-black text-cyan-300">
                        {{ $isAr ? 'محتوى المستند' : 'Document Content' }}
                    </h2>

                    <p class="mb-4 text-sm text-slate-400">
                        {{ $isAr
                            ? 'استخدم المتغيرات المتاحة لإدخال بيانات العميل أو الشحنة أو العرض.'
                            : 'Use variables to insert customer, shipment and quotation data.' }}
                    </p>

                    <textarea name="content" rows="18" required
                        class="w-full rounded-xl border border-white/10 bg-slate-950 p-5 font-mono text-sm leading-7 text-slate-200 outline-none focus:border-cyan-400"
                        placeholder="@verbatim
<h1>Commercial Invoice</h1>

<p>Customer: {{ customer . company_name }}</p>

<p>Shipment: {{ shipment . shipment_no }}</p>

<p>Quotation: {{ quotation . reference_no }}</p>

<p>Total: {{ quotation . total_charge }}</p> @endverbatim">{{ old('content') }}</textarea>

                </div>


                {{-- Variables --}}

                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">

                    <h2 class="mb-4 text-lg font-black">
                        {{ $isAr ? 'المتغيرات المتاحة' : 'Available Variables' }}
                    </h2>

                    @php
                        $variables = [
                            'company.name',
                            'company.email',
                            'company.phone',
                            'company.logo',
                            'company.tax_number',
                            'customer.company_name',
                            'customer.email',
                            'shipment.shipment_no',
                            'shipment.booking_no',
                            'shipment.cargo_type',
                            'quotation.reference_no',
                            'quotation.total_charge',
                        ];
                    @endphp

                    <div class="flex flex-wrap gap-2">

                        @foreach ($variables as $variable)
                            <code class="rounded-lg bg-slate-900 px-3 py-2 text-xs text-cyan-300">
                                @{{ $variable }}
                            </code>
                        @endforeach

                    </div>

                </div>


                {{-- Footer / Styles --}}

                <div class="grid gap-6 lg:grid-cols-2">

                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">

                        <h2 class="mb-4 font-black">
                            CSS
                        </h2>

                        <textarea name="styles" rows="8"
                            class="w-full rounded-xl border border-white/10 bg-slate-900 p-4 font-mono text-xs text-slate-200"
                            placeholder=".invoice-title { ... }">{{ old('styles') }}</textarea>

                    </div>


                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6">

                        <h2 class="mb-4 font-black">
                            {{ $isAr ? 'التذييل' : 'Footer' }}
                        </h2>

                        <textarea name="footer" rows="8"
                            class="w-full rounded-xl border border-white/10 bg-slate-900 p-4 font-mono text-sm text-slate-200"
                            placeholder="HAG Brokers — Sea Freight Solutions">{{ old('footer') }}</textarea>

                    </div>

                </div>


                {{-- Active --}}

                <label
                    class="flex cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-white/[0.03] p-4">

                    <input type="checkbox" name="is_active" value="1" checked
                        class="h-5 w-5 rounded border-white/20 bg-slate-900 text-cyan-400">

                    <span class="text-sm font-bold text-slate-200">
                        {{ $isAr ? 'تفعيل القالب' : 'Activate template' }}
                    </span>

                </label>


                {{-- Actions --}}

                <div class="flex justify-end gap-3">

                    <a href="{{ route('admin.document-templates.index') }}"
                        class="rounded-xl border border-white/10 px-5 py-3 text-sm font-bold text-slate-300">
                        {{ $isAr ? 'إلغاء' : 'Cancel' }}
                    </a>

                    <button type="submit"
                        class="rounded-xl bg-cyan-400 px-6 py-3 text-sm font-black text-slate-950 hover:bg-cyan-300">
                        {{ $isAr ? 'إنشاء القالب' : 'Create Template' }}
                    </button>

                </div>

            </form>

        </div>

    </div>

</x-admin-layout>
