@extends('layouts.app')

@section('title', __('categories.edit'))

@section('content')
    @include('categories.form', ['category' => $category])
@endsection
