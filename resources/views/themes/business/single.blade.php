@extends('themes.business.layout')

@section('content')
    <section class="section" style="padding-top:3rem;">
        <div class="container" style="max-width:820px;">
            <div class="post-meta">
                <span>{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</span>
                <span>By {{ $post->author->name ?? 'Author' }}</span>
            </div>
            <h1 style="font-size:2.5rem;margin-bottom:1.5rem;">{{ $post->title }}</h1>
            @if($post->featured_image_url)
                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" style="width:100%;border-radius:0.75rem;margin-bottom:2rem;max-height:420px;object-fit:cover;">
            @endif

            @if($post && $post->blocks && $post->blocks->whereNull('parent_block_id')->isNotEmpty())
                <div class="post-content">{!! $post->rendered_content !!}</div>
            @else
                <div class="post-content">{!! $post->rendered_content !!}</div>
            @endif

            @if($post->categories()->count() > 0 || $post->tags()->count() > 0)
                <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border-color);">
                    @foreach($post->categories as $category)
                        <a href="{{ url('/categories/' . $category->slug) }}" class="btn btn-outline" style="font-size:0.8rem;padding:0.35rem 0.9rem;margin:0 0.35rem 0.35rem 0;">{{ $category->name }}</a>
                    @endforeach
                    @foreach($post->tags as $tag)
                        <a href="{{ url('/tags/' . $tag->slug) }}" style="display:inline-block;margin:0 0.35rem 0.35rem 0;padding:0.35rem 0.9rem;background:#f1f5f9;border-radius:20px;font-size:0.8rem;color:var(--secondary-color);">{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if(isset($relatedPosts) && $relatedPosts->isNotEmpty())
        <section class="section" style="background:#f8fafc;">
            <div class="container">
                <div class="section-title"><h2>Related posts</h2></div>
                <div class="posts-grid">
                    @foreach($relatedPosts as $related)
                        <article class="card">
                            <div class="card-body">
                                <div class="post-meta"><span>{{ $related->published_at?->format('F j, Y') ?? $related->created_at->format('F j, Y') }}</span></div>
                                <h3><a href="{{ url('/posts/' . $related->slug) }}">{{ $related->title }}</a></h3>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
