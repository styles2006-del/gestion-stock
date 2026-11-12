@extends('layouts.app')

@section('title', __('warehouses.edit'))

@section('content')
    @include('warehouses.form', ['warehouse' => $warehouse])
@endsection
