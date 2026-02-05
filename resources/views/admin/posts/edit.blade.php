@extends('admin.components.admin')

@section('title', 'Edit ' . ucfirst($post->type))
@section('main_padding', 'p-0')
@section('header', '')

@section('content')
    @include('admin.posts.form', [
        'post' => $post,
        'action' => $action,
        'postTypeObj' => $postTypeObj ?? null
    ])

        <!-- Trash Form (Separate from main form) -->
        @if($post->status !== 'trash')
            <form id="trashForm" action="{{ route('admin.posts.destroy', $post->id) }}" method="POST"
                onsubmit="return confirm('Move to Trash?');" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
@endsection