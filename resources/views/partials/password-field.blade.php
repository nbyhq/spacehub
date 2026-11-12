<div>
    <label class="label" for="{{ $id }}">{{ $label }}</label>
    <div class="relative">
        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-3 text-[20px] text-on-surface-variant">lock</span>
        <input id="{{ $id }}" type="password" name="{{ $name }}" required autocomplete="{{ $autocomplete }}"
               class="input h-11 pl-10 pr-11 text-base sm:text-body-md">
        <button type="button" data-password-toggle="{{ $id }}" aria-label="Tampilkan password"
                class="absolute right-1.5 top-1.5 flex size-8 cursor-pointer items-center justify-center rounded-md text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-on-surface">
            <span class="material-symbols-outlined text-[20px]">visibility</span>
        </button>
    </div>
    @if (! empty($hint))
        <p class="mt-1 text-body-sm text-on-surface-variant">{{ $hint }}</p>
    @endif
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</div>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = document.getElementById(btn.dataset.passwordToggle);
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
                    btn.querySelector('.material-symbols-outlined').textContent = show ? 'visibility_off' : 'visibility';
                });
            });
        </script>
    @endpush
@endonce
