@extends('themes.rosetta.layout')

@section('content')
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>{{ $post->title ?? 'Post' }}</h1>
            <p style="font-size: 1.2rem; margin-top: 0.5rem;">
                By {{ $post->author->name ?? 'Author' }} on {{ $post->published_at?->format('F j, Y') ?? now()->format('F j, Y') }}
            </p>
        </div>
    </div>

    <!-- Post Content -->
    @if($post && $post->blocks && $post->blocks->whereNull('parent_block_id')->isNotEmpty())
        <section style="padding: 4rem 0;">
            <div class="container">
                @include('themes.rosetta.partials.blocks', ['post' => $post])
            </div>
        </section>
    @else
    <section style="padding: 4rem 0;">
        <div class="container">
            <div style="max-width: 800px; margin: 0 auto; line-height: 1.8;">
                @if($post->featured_image_url)
                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" style="width: 100%; border-radius: 8px; margin-bottom: 2rem;">
                @endif
                
                <div class="post-content">
                    @php
                        $content = $post->rendered_content;

                        if (function_exists('apply_filters')) {
                            $content = apply_filters('the_content', $content);
                        }

                        // Convert YouTube URLs to embedded iframes (handles watch, shorts, and embed URLs)
                        echo preg_replace_callback(
                            '/https?:\/\/(?:www\.)?youtube\.com\/(?:watch\?v=|shorts\/|embed\/)([a-zA-Z0-9_-]{11})/',
                            function($matches) {
                                $videoId = $matches[1];
                                return '<div class="youtube-embed" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;"><iframe src="https://www.youtube.com/embed/' . $videoId . '" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
                            },
                            $content
                        );
                    @endphp
                </div>
                
                <!-- Categories and Tags -->
                @if($post->categories()->count() > 0 || $post->tags()->count() > 0)
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                        @if($post->categories()->count() > 0)
                            <div style="margin-bottom: 1rem;">
                                <strong>Categories:</strong>
                                @foreach($post->categories as $category)
                                    <a href="{{ url('/categories/' . $category->slug) }}" style="display: inline-block; margin-right: 0.5rem; padding: 0.25rem 0.75rem; background-color: var(--light-color); border-radius: 4px; text-decoration: none; color: var(--text-color);">{{ $category->name }}</a>
                                @endforeach
                            </div>
                        @endif
                        
                        @if($post->tags()->count() > 0)
                            <div>
                                <strong>Tags:</strong>
                                @foreach($post->tags as $tag)
                                    <a href="{{ url('/tags/' . $tag->slug) }}" style="display: inline-block; margin-right: 0.5rem; padding: 0.25rem 0.75rem; background-color: var(--light-color); border-radius: 4px; text-decoration: none; color: var(--text-color);">{{ $tag->name }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    @if(isset($relatedPosts) && $relatedPosts->isNotEmpty())
        <section style="padding: 2rem 0 4rem;">
            <div class="container" style="max-width: 800px; margin: 0 auto;">
                <div class="section-title" style="text-align:left;">
                    <h2>Related Posts</h2>
                </div>
                <div class="posts-grid">
                    @foreach($relatedPosts as $related)
                        <article class="post-item card">
                            @if($related->featured_image_url)
                                <img src="{{ $related->featured_image_url }}" alt="{{ $related->title }}" class="card-img">
                            @endif
                            <div class="card-content">
                                <div class="post-meta">
                                    <span class="post-date">{{ $related->published_at?->format('F j, Y') ?? $related->created_at->format('F j, Y') }}</span>
                                </div>
                                <h3 class="post-title">
                                    <a href="{{ url('/posts/' . $related->slug) }}">{{ $related->title }}</a>
                                </h3>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    @if(isset($commentsEnabled) && ($post->post_type ?? 'post') === 'post')
        <section id="comments" style="padding: 0 0 4rem;">
            <div class="container" style="max-width: 800px; margin: 0 auto;">
                <div class="section-title" style="text-align:left;">
                    <h2>Comments @if($comments->isNotEmpty())<span class="text-muted" style="font-size:1rem;">({{ $comments->count() }})</span>@endif</h2>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($comments->isEmpty())
                    <p class="text-muted">No comments yet. Be the first to share your thoughts.</p>
                @else
                    <div class="comment-list" style="margin-bottom: 2.5rem;">
                        @foreach($comments as $comment)
                            <article class="comment" style="padding: 1.25rem 0; border-bottom: 1px solid var(--border-color);">
                                <div class="post-meta" style="margin-bottom: .5rem;">
                                    <strong>{{ $comment->author_name ?: 'Anonymous' }}</strong>
                                    <span> &middot; {{ $comment->created_at->format('F j, Y \a\t g:i a') }}</span>
                                </div>
                                <div>{!! nl2br(e($comment->content)) !!}</div>

                                @foreach($comment->replies as $reply)
                                    <div class="comment-reply" style="margin: 1rem 0 0 2rem; padding-left: 1rem; border-left: 3px solid var(--border-color);">
                                        <div class="post-meta" style="margin-bottom: .35rem;">
                                            <strong>{{ $reply->author_name ?: 'Anonymous' }}</strong>
                                            <span> &middot; {{ $reply->created_at->format('F j, Y \a\t g:i a') }}</span>
                                        </div>
                                        <div>{!! nl2br(e($reply->content)) !!}</div>
                                    </div>
                                @endforeach
                            </article>
                        @endforeach
                    </div>
                @endif

                @if($commentsEnabled)
                    <div class="card" style="padding: 1.75rem;">
                        <h3 style="margin-bottom: 1rem;">Leave a Comment</h3>
                        <form action="{{ route('comments.store', $post->slug) }}" method="POST">
                            @csrf
                            <div style="position:absolute; left:-9999px;" aria-hidden="true">
                                <label for="website">Website</label>
                                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                            </div>

                            @guest
                                <div class="form-group">
                                    <label for="author_name">Name</label>
                                    <input type="text" name="author_name" id="author_name" value="{{ old('author_name') }}" required>
                                    @error('author_name')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="author_email">Email <span class="text-muted">(not published)</span></label>
                                    <input type="email" name="author_email" id="author_email" value="{{ old('author_email') }}" required>
                                    @error('author_email')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="author_url">Website <span class="text-muted">(optional)</span></label>
                                    <input type="url" name="author_url" id="author_url" value="{{ old('author_url') }}" placeholder="https://">
                                    @error('author_url')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                            @endguest

                            <div class="form-group">
                                <label for="content">Comment</label>
                                <textarea name="content" id="content" required>{{ old('content') }}</textarea>
                                @error('content')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn">Post Comment</button>
                        </form>
                    </div>
                @else
                    <div class="alert alert-info">Comments are closed.</div>
                @endif
            </div>
        </section>
    @endif
@endsection
