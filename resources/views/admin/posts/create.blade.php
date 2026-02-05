@extends('admin.components.admin')

@section('title', 'Add New ' . ucfirst(request('type', 'Post')))
@section('main_padding', 'p-0')
@section('header', '') {{-- Custom header --}}

@section('content')
    @include('admin.posts.form', [
        'post' => $post,
        'action' => $action,
        'postTypeObj' => $postTypeObj ?? null
    ])
@endsection