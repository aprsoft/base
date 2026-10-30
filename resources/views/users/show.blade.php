@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        <livewire:user.show-user :user="$user" />
    </div>
@endsection
