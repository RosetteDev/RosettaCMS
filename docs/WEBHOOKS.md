# Webhooks

Rosetta CMS can notify external systems when content changes. Register a
webhook in the admin (`/admin/webhooks`) and it receives a signed JSON POST
whenever an event fires for its site (or for every site, if it has no
site assigned).

## Events

| Event             | Fires when                              |
| ----------------- | --------------------------------------- |
| `post.published`  | A post is created or transitioned to published |
| `post.updated`    | A post is saved after it is published    |
| `page.published`  | A page is created or transitioned to published |
| `page.updated`    | A page is saved after it is published    |
| `comment.created` | A comment is submitted                   |
| `media.created`   | A media file is uploaded                |

Drafts are deliberately silent — the stream tracks publishing, not
keystrokes.

## Delivery

Each delivery is an HTTP `POST` to the configured URL with:

- `Content-Type: application/json`
- `X-Rosetta-Signature: sha256=<hex>` — HMAC-SHA256 of the **raw request
  body** using the webhook's signing secret
- `X-Rosetta-Event: <event name>`
- `X-Rosetta-Delivery: <uuid>` — a fresh UUID per attempt

The body is JSON:

```json
{
  "event": "post.published",
  "type": "post",
  "id": 42,
  "slug": "hello-world",
  "title": "Hello World",
  "status": "published",
  "url": "https://example.com/posts/hello-world",
  "published_at": "2026-11-26T09:00:00+00:00"
}
```

Payloads are intentionally flat — they carry the slug/URL and the essential
content fields, not the full HTML body.

## Verifying signatures

Always verify the signature before trusting a payload:

```php
$expected = hash_hmac('sha256', $rawBody, $signingSecret);
$signature = $request->header('X-Rosetta-Signature');
// compare as "sha256=" . $expected, e.g. hash_equals($expected, substr($signature, 7))
```

## Operational notes

- Delivery is synchronous with a 10-second timeout and best-effort: a slow
  or unreachable endpoint never blocks or fails a content save. Failures
  are recorded on the webhook row (`last_fired_at`, `last_response_code`,
  `last_error`) and visible in the admin.
- The signing secret is shown once at creation. It is never re-displayed;
  if it is lost, delete and re-create the webhook.
- Webhooks are site-scoped: an event fires only to webhooks assigned to
  that site, plus site-agnostic webhooks (no site assigned).
- Use the **Test** button in the admin to send a signed test delivery
  (event `test`) and verify your endpoint end-to-end.
