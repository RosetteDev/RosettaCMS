<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $spec['info']['title'] }} — API Documentation</title>
    <style>
        :root {
            --violet-50: #f5f3ff; --violet-100: #ede9fe; --violet-600: #7c3aed; --violet-700: #6d28d9;
            --gray-50: #f9fafb; --gray-100: #f3f4f6; --gray-200: #e5e7eb; --gray-300: #d1d5db;
            --gray-500: #6b7280; --gray-700: #374151; --gray-900: #111827;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--gray-50); color: var(--gray-900); line-height: 1.6; }
        .wrap { max-width: 1080px; margin: 0 auto; padding: 0 1.5rem 4rem; }
        header.hero { background: linear-gradient(135deg, var(--violet-600), var(--violet-700)); color: #fff; padding: 3rem 1.5rem 2.5rem; text-align: center; }
        header.hero h1 { font-size: 2rem; font-weight: 800; margin: 0 0 .5rem; letter-spacing: -0.02em; }
        header.hero p { margin: 0 auto; max-width: 640px; opacity: .9; font-size: .95rem; }
        .badge { display: inline-block; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3); padding: .15rem .6rem; border-radius: 999px; font-size: .75rem; font-weight: 600; margin: .2rem; vertical-align: middle; }
        nav.toc { background: #fff; border: 1px solid var(--gray-200); border-radius: 12px; padding: 1rem 1.25rem; margin: 1.5rem 0; display: flex; flex-wrap: wrap; gap: .35rem; align-items: center; }
        nav.toc a { color: var(--violet-700); text-decoration: none; font-size: .8rem; font-weight: 600; padding: .3rem .7rem; border-radius: 6px; background: var(--violet-50); }
        nav.toc a:hover { background: var(--violet-100); }
        h2 { font-size: 1.35rem; font-weight: 700; margin: 2.25rem 0 .9rem; padding-bottom: .4rem; border-bottom: 2px solid var(--violet-100); }
        .endpoint { background: #fff; border: 1px solid var(--gray-200); border-radius: 12px; padding: 1.1rem 1.25rem; margin-bottom: .9rem; }
        .endpoint-head { display: flex; align-items: baseline; gap: .75rem; flex-wrap: wrap; }
        .method { font-size: .72rem; font-weight: 800; letter-spacing: .06em; padding: .2rem .55rem; border-radius: 6px; color: #fff; }
        .method-get { background: #0d9488; } .method-post { background: #2563eb; } .method-put { background: #d97706; } .method-delete { background: #dc2626; }
        .path { font-family: 'SFMono-Regular', Consolas, monospace; font-size: .95rem; font-weight: 600; color: var(--gray-900); }
        .summary { margin: .5rem 0 .75rem; color: var(--gray-700); font-size: .9rem; }
        .req { display: inline-block; font-size: .7rem; font-weight: 700; color: #b45309; background: #fef3c7; border: 1px solid #fde68a; border-radius: 6px; padding: .15rem .5rem; }
        .req.public { color: #065f46; background: #ecfdf5; border-color: #a7f3d0; }
        table.params { width: 100%; border-collapse: collapse; margin-top: .6rem; font-size: .82rem; }
        table.params th { text-align: left; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: var(--gray-500); border-bottom: 2px solid var(--gray-200); padding: .4rem .5rem; }
        table.params td { border-bottom: 1px solid var(--gray-100); padding: .45rem .5rem; vertical-align: top; }
        code.param { font-family: 'SFMono-Regular', Consolas, monospace; font-size: .8rem; color: var(--violet-700); font-weight: 600; }
        .responses { margin-top: .6rem; font-size: .8rem; color: var(--gray-700); }
        .responses code { font-size: .72rem; color: var(--gray-700); background: var(--gray-100); padding: .1rem .35rem; border-radius: 4px; }
        .actions { display: flex; gap: .6rem; margin-top: 1.25rem; }
        .btn { display: inline-block; background: var(--violet-600); color: #fff; text-decoration: none; font-weight: 600; font-size: .85rem; padding: .5rem 1.1rem; border-radius: 8px; border: none; cursor: pointer; }
        .btn:hover { background: var(--violet-700); }
        .btn-outline { background: transparent; color: var(--violet-700); border: 1px solid var(--violet-100); }
        .btn-outline:hover { background: var(--violet-50); }
        pre.example { background: #1f2937; color: #e5e7eb; padding: 1rem; border-radius: 10px; overflow-x: auto; font-size: .78rem; line-height: 1.55; margin: .6rem 0 0; }
        pre.example .k { color: #93c5fd; } pre.example .s { color: #86efac; } pre.example .c { color: #6b7280; }
        .callout { background: var(--violet-50); border: 1px solid var(--violet-100); border-radius: 10px; padding: .9rem 1.1rem; margin: 1rem 0; font-size: .88rem; color: var(--gray-700); }
        .callout strong { color: var(--violet-700); }
        footer { text-align: center; color: var(--gray-500); font-size: .78rem; margin-top: 2.5rem; }
        .hidden-mobile { display: none; }
        @media (min-width: 720px) { .hidden-mobile { display: inline; } }
    </style>
</head>
<body>
<header class="hero">
    <h1>{{ $spec['info']['title'] }}</h1>
    <p>{{ $spec['info']['description'] }}</p>
    <div>
        <span class="badge">v{{ $spec['info']['version'] }}</span>
        <span class="badge">Rate limited · 120 req/min</span>
        <span class="badge">Bearer token auth for writes</span>
    </div>
</header>

<div class="wrap">
    <nav class="toc">
        <strong style="font-size:.78rem; color: var(--gray-500);">Endpoints:</strong>
        @foreach($spec['tags'] as $tag)
            @if(!in_array($tag['name'], ['Discovery']))
                <a href="#tag-{{ strtolower(str_replace(' ', '-', $tag['name'])) }}">{{ $tag['name'] }}</a>
            @endif
        @endforeach
    </nav>

    <div class="callout">
        <strong>Quick start.</strong> Read endpoints are public (rate limited, 120 req/min).
        Write endpoints require an API key from <code>/admin/api-keys</code> — send it as
        <code>Authorization: Bearer &lt;key&gt;</code>. Every response is JSON.
    </div>

    <div class="actions">
        <a class="btn" href="{{ route('api.openapi') }}" target="_blank" rel="noopener">Download OpenAPI JSON</a>
        <a class="btn btn-outline" href="{{ route('api.index') }}" target="_blank" rel="noopener">API discovery document</a>
    </div>

    @foreach($spec['tags'] as $tag)
        @php
            $tagName = $tag['name'];
            $tagId = 'tag-' . strtolower(str_replace(' ', '-', $tagName));
            $ops = [];
            foreach($spec['paths'] as $path => $pathOps) {
                foreach($pathOps as $method => $op) {
                    if(in_array($tagName, $op['tags'] ?? [])) {
                        $ops[] = ['path' => $path, 'method' => strtoupper($method), 'op' => $op];
                    }
                }
            }
        @endphp
        @if(!empty($ops))
            <h2 id="{{ $tagId }}">{{ $tagName }}</h2>
            <p class="summary" style="margin-top:-.3rem;">{{ $tag['description'] ?? '' }}</p>
            @foreach($ops as $item)
                @php $op = $item['op']; @endphp
                <div class="endpoint">
                    <div class="endpoint-head">
                        <span class="method method-{{ strtolower($item['method']) }}">{{ $item['method'] }}</span>
                        <code class="path">{{ $item['path'] }}</code>
                        @if(!empty($op['security']))
                            <span class="req">Requires API key</span>
                        @else
                            <span class="req public">Public</span>
                        @endif
                    </div>
                    <p class="summary">{{ $op['summary'] ?? '' }}</p>

                    @php $params = collect($op['parameters'] ?? [])->reject(fn($p) => $p['name'] === 'body'); @endphp
                    @if($params->isNotEmpty())
                        <table class="params">
                            <thead>
                                <tr><th>Parameter</th><th>In</th><th>Required</th><th>Type</th><th>Notes</th></tr>
                            </thead>
                            <tbody>
                                @foreach($params as $param)
                                    <tr>
                                        <td><code class="param">{{ $param['name'] }}</code></td>
                                        <td>{{ $param['in'] }}</td>
                                        <td>{{ ($param['required'] ?? false) ? 'Yes' : 'No' }}</td>
                                        <td>{{ $param['schema']['type'] ?? '—' }}</td>
                                        <td>{{ $param['description'] ?? ($param['schema']['default'] ?? '') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if(!empty($op['parameters']) && collect($op['parameters'])->contains(fn($p) => $p['name'] === 'body'))
                        <p class="responses"><strong>Request body:</strong> JSON — <code>title</code>, <code>content</code>, <code>excerpt</code>, <code>status</code>, <code>published_at</code>, <code>categories</code>, <code>tags</code></p>
                    @endif

                    <p class="responses">
                        <strong>Responses:</strong>
                        @foreach($op['responses'] as $code => $resp)
                            <code>{{ $code }}</code> {{ $resp['description'] }}@if(!$loop->last) · @endif
                        @endforeach
                    </p>
                </div>
            @endforeach
        @endif
    @endforeach

    <h2 id="authentication">Authentication</h2>
    <div class="endpoint">
        <p class="summary" style="margin-top:0;">
            Write endpoints (<code>POST /posts</code>, <code>PUT /posts/{slug}</code>, <code>DELETE /posts/{slug}</code> and the page equivalents) require an API key with the matching scope:
        </p>
        <ul style="margin:.4rem 0; padding-left:1.4rem; color: var(--gray-700); font-size:.9rem;">
            <li><code>posts:write</code> — create/update/delete posts</li>
            <li><code>pages:write</code> — create/update/delete pages</li>
        </ul>
        <p class="summary">
            Send the key as a Bearer token: <code>Authorization: Bearer rk_...</code>.
            Keys are created and revoked in the admin at <code>/admin/api-keys</code>. The plaintext key is shown once at creation and is never stored in plaintext.
        </p>
        <pre class="example"><span class="c"># Create a post</span>
curl -X POST {{ url('/api/v1/posts') }} \
  -H "Authorization: Bearer $ROSETTA_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"title": "Hello", "content": "...", "status": "published"}'

<span class="c"># Response (201)</span>
{
  <span class="k">"success"</span>: <span class="s">true</span>,
  <span class="k">"data"</span>: {
    <span class="k">"slug"</span>: <span class="s">"hello"</span>,
    <span class="k">"status"</span>: <span class="s">"published"</span>,
    <span class="k">"url"</span>: <span class="s">"/posts/hello"</span>
  }
}</pre>
    </div>

    <h2 id="webhooks">Webhooks</h2>
    <div class="endpoint">
        <p class="summary" style="margin-top:0;">
            Subscribed webhooks receive a signed JSON POST whenever content changes — useful for syncing an external system (newsletter, search index, analytics).
        </p>
        <ul style="margin:.4rem 0; padding-left:1.4rem; color: var(--gray-700); font-size:.9rem;">
            <li><code>post.published</code> / <code>post.updated</code></li>
            <li><code>page.published</code> / <code>page.updated</code></li>
            <li><code>comment.created</code></li>
            <li><code>media.created</code></li>
        </ul>
        <p class="summary">
            Each delivery sends <code>Content-Type: application/json</code> with headers:
            <code>X-Rosetta-Signature: sha256=&lt;hmac&gt;</code> (HMAC-SHA256 of the raw body with the webhook secret),
            <code>X-Rosetta-Event</code>, and <code>X-Rosetta-Delivery</code> (a UUID).
            Verify the signature before processing — never trust the payload blindly.
        </p>
        <p class="summary" style="margin-bottom:0;">
            Register and manage webhooks in the admin at <code>/admin/webhooks</code>.
        </p>
    </div>

    <footer>
        Rosetta CMS · Public API v{{ $spec['info']['version'] }} ·
        <a href="{{ url('/') }}" style="color: var(--violet-700);">Back to site</a>
    </footer>
</div>
</body>
</html>
