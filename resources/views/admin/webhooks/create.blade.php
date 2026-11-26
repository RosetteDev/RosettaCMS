@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>New Webhook</h1>
            <p>External systems receive a signed JSON POST when content changes.</p>
        </div>
    </div>

    <div class="container" style="max-width: 720px; padding-top: 1.5rem;">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.webhooks.store') }}">
            @csrf

            <div class="mb-3">
                <label for="url" class="form-label"><strong>Endpoint URL</strong></label>
                <input type="url" name="url" id="url" class="form-control" required maxlength="500"
                       placeholder="https://example.com/hooks/rosetta" value="{{ old('url') }}">
                <div class="form-text">Must be an http(s) URL. The endpoint must accept JSON POSTs.</div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description <span class="text-muted">(optional)</span></label>
                <input type="text" name="description" id="description" class="form-control" maxlength="255"
                       placeholder="e.g. Marketing sync" value="{{ old('description') }}">
            </div>

            <div class="mb-3">
                <span class="form-label"><strong>Events</strong> <span class="text-muted">(at least one)</span></span>
                <div style="background: var(--light-color, #f9fafb); border-radius: 8px; padding: .75rem 1rem;">
                    @foreach($availableEvents as $event)
                        <div class="form-check" style="padding: .35rem 0;">
                            <input class="form-check-input" type="checkbox" name="events[]" value="{{ $event }}" id="event-{{ $loop->index }}"
                                   {{ in_array($event, old('events', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="event-{{ $loop->index }}">
                                <code>{{ $event }}</code>
                                <span class="text-muted small ms-1">
                                    @switch($event)
                                        @case('post.published')
                                            — a post goes live
                                            @break
                                        @case('post.updated')
                                            — a post is saved (after first publish)
                                            @break
                                        @case('page.published')
                                            — a page goes live
                                            @break
                                        @case('page.updated')
                                            — a page is saved (after first publish)
                                            @break
                                        @case('comment.created')
                                            — a comment is submitted
                                            @break
                                        @case('media.created')
                                            — a media file is uploaded
                                            @break
                                    @endswitch
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Webhook</button>
                <a href="{{ route('admin.webhooks.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
