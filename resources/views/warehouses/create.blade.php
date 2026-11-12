@extends('layouts.app')

@section('title', __('warehouses.new'))

@section('content')
    @include('warehouses.form', ['warehouse' => new \App\Models\Warehouse(['is_active' => true])])
@endsection
