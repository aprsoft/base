<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
        Contrato N° : {{ $contract->id }}<br>
        Cliente     : {{ $contract->customer->fullName }}<br>
        Medidor     : {{ $contract->meter->numero_medidor }}
    </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 text-gray-900 dark:text-gray-100">                  
                <livewire:contracts.show-edit-contract
                    :contract="$contract"
                />                 
            </div>
        </div>
    </div>
</x-app-layout>
