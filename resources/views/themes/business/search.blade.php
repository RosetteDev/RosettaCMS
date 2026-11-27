@extends('themes.business.layout')

@section('content')
    <section class="section" style="padding-top:3rem;">
        <div class="container">
            <div class="section-title"><h1>Search</h1></div>
            <form action="{{ url('/search') }}" method="GET" style="max-width:560px;margin:0 auto 2rem;display:flex;gap:0.5rem;">
                <input type="text" name="q" value="{{ $query }}" placeholder="Search posts..." required style="flex:1;padding:0.75rem;border:1px solid var(--border-color);border-radius:0.375rem;">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            @if($posts->isNotEmpty())
                <p style="text-align:center;color:var(--secondary-color);margin-bottom:1.5rem;">Found {{ $posts->total() }} result{{ $posts->total() === 1 ? '' : 's' }}.</p>
                <div class="posts-grid">
                    @foreach($posts as $post)
                        <article class="card">
                            <div class="card-body">
                                <h3><a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a></h3>
                                <p>{{ Str::limit($post->plain_excerpt, 140) }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div style="margin-top:2rem;text-align:center;">{{ $posts->withQueryString()->links() }}</div>
            @else
                <div style="text-align:center;padding:2rem 0;">
                    <h2>No results found</h2>
                    <p style="color:var(--secondary-color);">Try different keywords.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
