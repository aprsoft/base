<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"> 
            Editar Contrato          
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
                <livewire:contracts.edit-contract
                    :contract="$contract"
                />             
        </div>
    </div>
</x-app-layout>
