@extends('admin.layouts.app')

@section('content')
    @include('admin.partials.editor-form')

    @if($post->exists)
        <div class="mt-4">
            @include('admin.pages.partials.block-editor')
        </div>
    @endif
@endsection
