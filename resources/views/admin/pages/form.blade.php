@extends('admin.layouts.app')

@section('content')
    @include('admin.partials.editor-form')

    @if($post->exists)
        <div class="mt-4">
            @include('admin.pages.partials.block-editor')
        </div>
    @elseif($postType === 'page')
        <div class="mt-4">
            <div class="alert alert-info d-flex align-items-center gap-3" role="alert">
                <i class="fas fa-pen-nib fa-lg"></i>
                <div>
                    <strong>Page builder:</strong> blocks are added after the page exists — save the page below and you'll land on the page builder, where you can add, arrange and save blocks.
                </div>
            </div>
        </div>
    @endif
@endsection
