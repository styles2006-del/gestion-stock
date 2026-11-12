@extends('layouts.app')

@section('title', __('categories.new'))

@section('content')
    @include('categories.form', ['category' => new \App\Models\Category()])
@endsection
