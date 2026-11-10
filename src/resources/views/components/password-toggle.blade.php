@props([
    'id',
    'name',
    'required' => false,
    'autocomplete' => 'new-password',
    'minlength' => 12,
    'maxlength' => 128,
    'placeholder' => null,
])

<div class="relative">
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="password"
        autocomplete="{{ $autocomplete }}"
        @if ($required) required @endif
        @if ($minlength !== null) minlength="{{ $minlength }}" @endif
        @if ($maxlength !== null) maxlength="{{ $maxlength }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        {{ $attributes->merge(['class' => 'block w-full pe-12']) }}
    >
    <button
        type="button"
        class="absolute inset-y-0 end-0 inline-flex items-center justify-center px-3 text-slate-500 transition hover:text-cyan-600 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-cyan-500"
        aria-label="{{ __('auth.show_password') }}"
        aria-controls="{{ $id }}"
        aria-pressed="false"
        data-show-label="{{ __('auth.show_password') }}"
        data-hide-label="{{ __('auth.hide_password') }}"
        onclick="
            const field = this.previousElementSibling;
            const visible = field.type === 'password';
            field.type = visible ? 'text' : 'password';
            this.setAttribute('aria-pressed', visible ? 'true' : 'false');
            this.setAttribute('aria-label', visible ? this.dataset.hideLabel : this.dataset.showLabel);
            this.querySelector('[data-eye]').classList.toggle('hidden', visible);
            this.querySelector('[data-eye-off]').classList.toggle('hidden', !visible);
        "
    >
        <svg data-eye xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.3-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.3 6.5-9.5 6.5S2.5 12 2.5 12Z" />
            <circle cx="12" cy="12" r="2.7" />
        </svg>
        <svg data-eye-off xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 5.7c.5-.1.9-.2 1.4-.2 6.2 0 9.5 6.5 9.5 6.5a16 16 0 0 1-3.2 4.1M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.3 6.5 9.5 6.5c1.3 0 2.5-.3 3.5-.8M9.9 9.9a3 3 0 0 0 4.2 4.2" />
        </svg>
    </button>
</div>
