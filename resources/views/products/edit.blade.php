@extends('layouts.app')

@section('title', __('products.edit'))

@section('content')
    <x-page-header :title="__('products.edit')" :subtitle="$product->name" icon="pencil" />

    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf @method('PUT')
        @include('products._form', ['product' => $product])
    </form>
@endsection
