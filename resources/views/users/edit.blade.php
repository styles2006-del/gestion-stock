@extends('layouts.app')

@section('title', __('users.edit'))

@section('content')
    @include('users.form', ['user' => $user])
@endsection
