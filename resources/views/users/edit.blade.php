
@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar usuario" />

    <div class="space-y-6">
        <livewire:user.user-edit :user="$user" />
    </div>
@endsection
