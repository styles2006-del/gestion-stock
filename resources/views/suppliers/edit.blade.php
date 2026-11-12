@extends('layouts.app')

@section('title', __('suppliers.edit'))

@section('content')
    @include('suppliers.form', ['supplier' => $supplier])
@endsection
