@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Clientes" />

    <div class="space-y-6">
        
        <livewire:customer.customer-table
            :routeName="$routeName"
        />
    
    </div>
@endsection
