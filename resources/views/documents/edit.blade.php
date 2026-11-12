@extends('layouts.app')

@section('title', __('documents.edit'))

@section('content')
    <x-page-header :title="__('documents.edit')" :subtitle="$document->number" icon="document">
        <a href="{{ route('documents.show', $document) }}" class="btn-ghost">
            <x-icon name="chevron-left" class="h-4 w-4" /> {{ __('app.back') }}
        </a>
    </x-page-header>

    <form method="POST" action="{{ route('documents.update', $document) }}">
        @csrf
        @method('PUT')
        @include('documents._form', [
            'document' => $document,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
        ])
    </form>
@endsection
