<section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Documentación PDF</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Genera una copia actualizada de este perfil.</p>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('pdf.download', ['type' => $type, 'record' => $recordId]) }}"
           class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            Descargar PDF
        </a>
        <a href="{{ route('pdf.show', ['type' => $type, 'record' => $recordId]) }}"
           target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-50 dark:border-gray-600 dark:text-white dark:hover:bg-gray-800">
            Visualizar / imprimir
        </a>
        @if ($canEmail)
            <button type="button" wire:click="openEmail"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-50 dark:border-gray-600 dark:text-white dark:hover:bg-gray-800">
                Enviar por correo
            </button>
        @endif
    </div>

    @if ($notice)
        <p role="status" class="mt-4 text-sm text-green-700 dark:text-green-300">{{ $notice }}</p>
    @endif

    @if ($showEmailForm && $canEmail)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 p-4" role="presentation">
            <div role="dialog" aria-modal="true" aria-labelledby="pdf-email-title-{{ $recordId }}"
                 class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h4 id="pdf-email-title-{{ $recordId }}" class="text-lg font-semibold text-gray-900 dark:text-white">Enviar perfil por correo</h4>
                <form wire:submit="send" class="mt-4 space-y-3">
                    <label for="pdf-recipient-{{ $recordId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Destinatario</label>
                    <input id="pdf-recipient-{{ $recordId }}" type="email" wire:model="recipient"
                           required autocomplete="email" maxlength="254"
                           class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                           placeholder="correo@ejemplo.cl">
                    @error('recipient')
                        <p class="text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 dark:text-gray-400">Revisa el destinatario antes de confirmar. El archivo adjunto contiene datos personales.</p>
                    <div class="flex flex-wrap gap-3 pt-3">
                        <button type="submit" wire:loading.attr="disabled" wire:target="send"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">Confirmar envío</button>
                        <button type="button" wire:click="closeEmail" class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-600 dark:text-white">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</section>
