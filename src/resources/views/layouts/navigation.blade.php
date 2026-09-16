<nav x-data="{ open: false }" class="border-b border-slate-200 bg-white">

    {{-- Primary Navigation Menu --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="flex h-16 justify-between">

            <div class="flex">

                {{-- Logo --}}
                <div class="flex shrink-0 items-center">

                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-slate-800" />
                    </a>

                </div>


                {{-- Navigation Links --}}
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">

                        {{ __('dashboard.dashboard') }}

                    </x-nav-link>

                </div>

            </div>


            {{-- Right Side --}}
            <div class="hidden items-center sm:flex sm:ms-6">

                {{-- Language Switcher --}}
                <div class="me-4 flex items-center rounded-lg border border-slate-200 bg-slate-50 p-1">

                    <a href="{{ route('language.switch', 'en') }}"
                        class="rounded-md px-3 py-1.5 text-xs font-medium transition
                        {{ app()->getLocale() === 'en'
                            ? 'bg-slate-900 text-white'
                            : 'text-slate-500 hover:bg-white hover:text-slate-900' }}">

                        🇬🇧 English

                    </a>

                    <a href="{{ route('language.switch', 'ar') }}"
                        class="rounded-md px-3 py-1.5 text-xs font-medium transition
                        {{ app()->getLocale() === 'ar'
                            ? 'bg-slate-900 text-white'
                            : 'text-slate-500 hover:bg-white hover:text-slate-900' }}">

                        🇦🇪 العربية

                    </a>

                </div>


                {{-- Settings Dropdown --}}
                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button
                            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-slate-500 transition duration-150 ease-in-out hover:text-slate-700 focus:outline-none">

                            <div>
                                {{ Auth::user()->name }}
                            </div>

                            <div class="ms-1">

                                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">

                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />

                                </svg>

                            </div>

                        </button>

                    </x-slot>


                    <x-slot name="content">

                        {{-- Profile --}}
                        <x-dropdown-link :href="route('profile.edit')">

                            {{ __('Profile') }}

                        </x-dropdown-link>


                        {{-- Authentication --}}
                        <form method="POST" action="{{ route('logout') }}">

                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">

                                {{ __('Log Out') }}

                            </x-dropdown-link>

                        </form>

                    </x-slot>

                </x-dropdown>

            </div>


            {{-- Hamburger --}}
            <div class="-me-2 flex items-center sm:hidden">

                <button @click="open = ! open"
                    class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-500 focus:bg-slate-100 focus:text-slate-500 focus:outline-none">

                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">

                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />

                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />

                    </svg>

                </button>

            </div>

        </div>

    </div>


    {{-- Responsive Navigation Menu --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">

        {{-- Dashboard --}}
        <div class="space-y-1 pb-3 pt-2">

            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">

                {{ __('dashboard.dashboard') }}

            </x-responsive-nav-link>

        </div>


        {{-- Mobile Language Switcher --}}
        <div class="border-t border-slate-200 px-4 py-4">

            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Language
            </p>

            <div class="flex gap-2">

                <a href="{{ route('language.switch', 'en') }}"
                    class="rounded-lg border px-4 py-2 text-sm font-medium transition
                    {{ app()->getLocale() === 'en'
                        ? 'border-slate-900 bg-slate-900 text-white'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-400' }}">

                    🇬🇧 English

                </a>

                <a href="{{ route('language.switch', 'ar') }}"
                    class="rounded-lg border px-4 py-2 text-sm font-medium transition
                    {{ app()->getLocale() === 'ar'
                        ? 'border-slate-900 bg-slate-900 text-white'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-slate-400' }}">

                    🇦🇪 العربية

                </a>

            </div>

        </div>


        {{-- Responsive Settings Options --}}
        <div class="border-t border-slate-200 pb-1 pt-4">

            <div class="px-4">

                <div class="text-base font-medium text-slate-800">
                    {{ Auth::user()->name }}
                </div>

                <div class="text-sm font-medium text-slate-500">
                    {{ Auth::user()->email }}
                </div>

            </div>


            <div class="mt-3 space-y-1">

                {{-- Profile --}}
                <x-responsive-nav-link :href="route('profile.edit')">

                    {{ __('Profile') }}

                </x-responsive-nav-link>


                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">

                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">

                        {{ __('Log Out') }}

                    </x-responsive-nav-link>

                </form>

            </div>

        </div>

    </div>

</nav>
