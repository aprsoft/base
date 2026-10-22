
@props([
    'label' => 'Guardar',
    'loadingLabel' => 'Guardando...',
    'target' => 'save',
])

<button
    type="submit"
    wire:loading.attr="disabled"
    wire:target="{{ $target }}"
    {{ $attributes->class([
        'inline-flex min-w-40 items-center justify-center',
        'rounded-lg bg-brand-500 px-4 py-2.5',
        'text-sm font-medium text-white shadow-theme-xs',
        'transition hover:bg-brand-600',
        'disabled:cursor-not-allowed disabled:opacity-60',
    ]) }}
>
    <span
        wire:loading.remove
        wire:target="{{ $target }}"
    >
        {{ __($label) }}
    </span>

    <span
        wire:loading.inline-flex
        wire:target="{{ $target }}"
        class="items-center gap-2"
    >
        <svg
            class="h-4 w-4 animate-spin"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>

            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
            ></path>
        </svg>

        {{ __($loadingLabel) }}
    </span>
</button>
