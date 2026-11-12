@extends('layouts.app')

@section('title', __('products.new'))

@section('content')
    <x-page-header :title="__('products.new')" :subtitle="__('app.required_fields')" icon="plus" />

    <form method="POST" action="{{ route('products.store') }}">
        @csrf
        @include('products._form', ['product' => null])
    </form>
@endsection
