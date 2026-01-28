@extends('layouts.admin')

@section('title', 'Add New ' . ucfirst(request('type', 'Post')))
@section('main_padding', 'p-0 mt-8')
@section('header', '') {{-- Custom header --}}

@section('content')
    @include('admin.posts.form', [
        'post' => new \App\Models\Post(['type' => request('type', 'post')]),
        'action' => route('admin.posts.store')
    ])
@endsection