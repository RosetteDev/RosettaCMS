{!! '<'. '?xml version="1.0" encoding="UTF-8"?'. '>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $settings->site_title ?? ($site->name ?? 'Rosetta CMS') }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ $settings->site_description ?? ($site->description ?? '') }}</description>
        <language>{{ app()->getLocale() }}</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ url('/feed') }}" rel="self" type="application/rss+xml" />
@foreach($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ $post->public_url }}</link>
            <guid isPermaLink="true">{{ $post->public_url }}</guid>
            <pubDate>{{ ($post->published_at ?? $post->created_at)->toRssString() }}</pubDate>
            <description>{{ $post->plain_excerpt }}</description>
@if($post->primary_category)
            <category>{{ $post->primary_category->name }}</category>
@endif
@foreach($post->tags as $tag)
            <category>{{ $tag->name }}</category>
@endforeach
        </item>
@endforeach
    </channel>
</rss>
