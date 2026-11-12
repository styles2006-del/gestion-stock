@extends('layouts.app')

@section('title', __('suppliers.new'))

@section('content')
    @include('suppliers.form', ['supplier' => new \App\Models\Supplier(['is_active' => true])])
@endsection
