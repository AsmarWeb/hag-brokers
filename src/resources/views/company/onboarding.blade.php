@php($isAr = app()->getLocale() === 'ar')
<x-app-layout>
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-cyan-600">HAG Brokers</p>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">{{ $isAr ? 'إعداد مساحة شركتك' : 'Set up your company workspace' }}</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $isAr ? 'أكمل بيانات شركتك لبدء إدارة الشحنات والعروض والعمليات من مساحة عمل مستقلة.' : 'Add your company details to manage shipments, quotations and operations in your own workspace.' }}</p>
        </div>
        <x-auth-session-status class="mb-4" :status="session('status')" />
        <form method="POST" action="{{ route('company.onboarding.store') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @csrf
            <div>
                <x-input-label for="company_name" :value="$isAr ? 'اسم الشركة القانوني أو التجاري' : 'Company legal or trading name'" />
                <x-text-input id="company_name" name="company_name" class="mt-1 block w-full" required maxlength="255" :value="old('company_name')" autocomplete="organization" />
                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="country_code" :value="$isAr ? 'الدولة' : 'Country'" />
                <select id="country_code" name="country_code" required class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-950">
                    @foreach (['AE'=>'United Arab Emirates','SA'=>'Saudi Arabia','OM'=>'Oman','QA'=>'Qatar','BH'=>'Bahrain','KW'=>'Kuwait'] as $code=>$country)
                        <option value="{{ $code }}" @selected(old('country_code', 'AE') === $code)>{{ $country }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('country_code')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="city" :value="$isAr ? 'المدينة' : 'City'" />
                <x-text-input id="city" name="city" class="mt-1 block w-full" maxlength="255" :value="old('city')" autocomplete="address-level2" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                {{ $isAr ? 'سيتم إنشاء مساحة عمل جديدة باسم شركتك، وستكون أنت المالك. لا تتم إضافة حسابك إلى شركة موجودة.' : 'A new workspace will be created for this company, and you will be its owner. This account cannot join an existing company.' }}
            </div>
            <x-primary-button class="w-full justify-center bg-cyan-500 py-3 text-slate-950 hover:bg-cyan-400">{{ $isAr ? 'إنشاء مساحة الشركة' : 'Create company workspace' }}</x-primary-button>
        </form>
    </div>
</x-app-layout>
