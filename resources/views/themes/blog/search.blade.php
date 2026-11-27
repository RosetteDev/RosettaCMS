@extends('themes.blog.layout')

@section('content')
    <div class="container" style="padding:3rem 0;">
        <h1 style="font-size:2rem;margin-bottom:1.5rem;">Search</h1>
        <form action="{{ url('/search') }}" method="GET" style="display:flex;gap:0.5rem;margin-bottom:2rem;">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search posts..." required style="flex:1;padding:0.7rem;border:1px solid var(--border-color);background:#fff;border-radius:0.375rem;">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        @if($posts->isNotEmpty())
            <p style="color:var(--secondary-color);margin-bottom:1.5rem;">Found {{ $posts->total() }} result{{ $posts->total() === 1 ? '' : 's' }}.</p>
            @foreach($posts as $post)
                <article style="padding:1.25rem 0;border-bottom:1px solid var(--border-color);">
                    <h2 style="font-size:1.35rem;margin-bottom:0.35rem;"><a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a></h2>
                    <p style="color:var(--secondary-color);">{{ Str::limit($post->plain_excerpt, 180) }}</p>
                </article>
            @endforeach
            <div style="text-align:center;margin-top:1.5rem;">{{ $posts->withQueryString()->links() }}</div>
        @else
            <p style="text-align:center;color:var(--secondary-color);padding:2rem 0;">No results found. Try different keywords.</p>
        @endif
    </div>
@endsection
