@extends('layouts.app')

@section('title', __('documents.new'))

@section('content')
    <x-page-header :title="__('documents.new')" :subtitle="__('app.required_fields')" icon="document">
        <a href="{{ route('documents.index') }}" class="btn-ghost">
            <x-icon name="chevron-left" class="h-4 w-4" /> {{ __('app.back') }}
        </a>
    </x-page-header>

    <form method="POST" action="{{ route('documents.store') }}">
        @csrf
        @include('documents._form', [
            'document' => $document,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
        ])
    </form>
@endsection
