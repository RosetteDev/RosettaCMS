@extends('themes.magazine.layout')

@section('content')
    <section class="container" style="padding:3rem 0;">
        <h1 style="font-size:2.5rem;margin-bottom:1.5rem;">Search</h1>
        <form action="{{ url('/search') }}" method="GET" style="display:flex;gap:0.5rem;margin-bottom:2rem;">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search stories..." required style="flex:1;padding:0.75rem;border:1px solid var(--border-color);background:#fff;border-radius:0.375rem;font-size:1rem;">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        @if($posts->isNotEmpty())
            <p style="color:var(--secondary-color);font-family:-apple-system,sans-serif;margin-bottom:1.5rem;">Found {{ $posts->total() }} result{{ $posts->total() === 1 ? '' : 's' }}.</p>
            <div class="mag-stories" style="padding:0;">
                @foreach($posts as $post)
                    <article class="mag-story">
                        <h3><a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a></h3>
                        <p>{{ Str::limit($post->plain_excerpt, 150) }}</p>
                    </article>
                @endforeach
            </div>
            <div style="text-align:center;margin-top:2rem;">{{ $posts->withQueryString()->links() }}</div>
        @else
            <p style="text-align:center;color:var(--secondary-color);padding:2rem 0;">No results found. Try different keywords.</p>
        @endif
    </section>
@endsection
