<x-admin-layout>

    <x-slot name="header">

        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                System Settings
            </h2>

            <p class="text-sm text-slate-500 dark:text-slate-400">
                Manage platform availability, maintenance mode and contact information.
            </p>
        </div>

    </x-slot>


    <div class="py-6">

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- Success --}}
            @if (session('success'))
                <div
                    class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">
                    {{ session('success') }}
                </div>
            @endif


            {{-- Errors --}}
            @if ($errors->any())

                <div
                    class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400">

                    <ul class="list-inside list-disc space-y-1">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            @endif


            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">

                @csrf
                @method('PUT')


                {{-- Maintenance --}}
                <div
                    class="rounded-2xl bg-white p-6 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">

                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

                        <div>

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">

                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.42 15.17 17.25 21 21 17.25l-5.83-5.83m-3.09-3.09a6.75 6.75 0 01-9.57 0 6.75 6.75 0 010-9.55 6.75 6.75 0 019.57 0l.92.92a.75.75 0 010 1.06l-2.12 2.12a.75.75 0 01-1.06 0l-.92-.92a3.75 3.75 0 00-5.3 0 3.75 3.75 0 000 5.3 3.75 3.75 0 005.3 0l.92-.92a.75.75 0 011.06 0l2.12 2.12a.75.75 0 010 1.06l-.92.92a6.75 6.75 0 01-4.78 1.98z" />
                                    </svg>

                                </div>

                                <div>

                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                        Maintenance Mode
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        Temporarily hide the public platform while you perform maintenance.
                                    </p>

                                </div>

                            </div>

                        </div>


                        {{-- Toggle --}}
                        <label class="relative inline-flex cursor-pointer items-center">

                            <input type="checkbox" name="maintenance_mode" value="1" class="peer sr-only"
                                @checked($setting->maintenance_mode)>

                            <div
                                class="h-7 w-14 rounded-full bg-slate-300 after:absolute after:start-[3px] after:top-[3px] after:h-[22px] after:w-[22px] after:rounded-full after:bg-white after:transition-all peer-checked:bg-orange-500 peer-checked:after:translate-x-7 dark:bg-slate-700">
                            </div>

                        </label>

                    </div>


                    @if ($setting->maintenance_mode)
                        <div
                            class="mt-5 rounded-xl border border-orange-200 bg-orange-50 p-4 text-sm text-orange-700 dark:border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-400">

                            <strong>
                                Maintenance mode is currently ON.
                            </strong>

                            Public visitors will see the maintenance page.
                            Authenticated users can continue accessing the platform.

                        </div>
                    @else
                        <div
                            class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">

                            <strong>
                                Platform is currently online.
                            </strong>

                            Public visitors can access the website normally.

                        </div>
                    @endif

                </div>


                {{-- Contact --}}
                <div
                    class="rounded-2xl bg-white p-6 shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">

                    <div class="mb-6">

                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            Maintenance Contact Information
                        </h3>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            These details are displayed to visitors on the maintenance page.
                        </p>

                    </div>


                    <div class="grid gap-6 md:grid-cols-2">

                        {{-- Phone --}}
                        <div>

                            <label for="phone"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Contact Phone
                            </label>

                            <input id="phone" name="phone" type="text"
                                value="{{ old('phone', $setting->phone) }}" placeholder="+971 50 000 0000"
                                class="w-full rounded-xl border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                            @error('phone')
                                <p class="mt-1 text-sm text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Email --}}
                        <div>

                            <label for="email"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Contact Email
                            </label>

                            <input id="email" name="email" type="email"
                                value="{{ old('email', $setting->email) }}" placeholder="info@example.com"
                                class="w-full rounded-xl border-slate-300 px-4 py-3 dark:border-slate-700 dark:bg-slate-800 dark:text-white">

                            @error('email')
                                <p class="mt-1 text-sm text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </div>


                {{-- Save --}}
                <div class="flex justify-end">

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-cyan-500 px-7 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                        </svg>

                        Save Settings

                    </button>

                </div>

            </form>

        </div>

    </div>

</x-admin-layout>
