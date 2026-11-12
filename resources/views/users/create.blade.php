@extends('layouts.app')

@section('title', __('users.new'))

@section('content')
    @include('users.form', ['user' => new \App\Models\User(['role' => 'user', 'locale' => 'fr', 'is_active' => true])])
@endsection
