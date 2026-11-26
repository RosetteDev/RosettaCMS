<?php

namespace App\Services;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Dispatches signed HTTP POST notifications to subscribed webhooks.
 *
 * Delivery is synchronous with a short timeout and best-effort semantics:
 * failures are recorded on the webhook row (never thrown), so a slow or
 * unreachable endpoint can never break a content save.
 *
 * Signature: HMAC-SHA256 of the raw JSON body with the webhook secret, sent
 * as `X-Rosetta-Signature: sha256=<hex>` alongside `X-Rosetta-Event` and
 * `X-Rosetta-Delivery` (a UUID per delivery attempt).
 */
class WebhookService
{
    public const TIMEOUT_SECONDS = 10;

    /**
     * Dispatch an event to every active webhook subscribed to it for the site.
     *
     * @param string $event  One of Webhook::ALLOWED_EVENTS.
     * @param array  $payload  Event payload (content details). Must be JSON-serialisable.
     * @param int|null $siteId  Site the event belongs to (null = any site).
     */
    public function dispatch(string $event, array $payload, ?int $siteId = null): void
    {
        $webhooks = Webhook::query()
            ->activeForSite($siteId)
            ->get()
            ->filter(fn (Webhook $w) => $w->subscribesTo($event));

        foreach ($webhooks as $webhook) {
            $this->send($webhook, $event, $payload);
        }
    }

    /**
     * Send a test delivery for a single webhook. Returns the outcome array.
     */
    public function test(Webhook $webhook): array
    {
        return $this->send($webhook, 'test', [
            'event' => 'test',
            'message' => 'This is a test webhook delivery from Rosetta CMS.',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Send a signed POST to one webhook. Records the outcome on the row.
     *
     * @return array{success: bool, status: int|null, error: string|null}
     */
    protected function send(Webhook $webhook, string $event, array $payload): array
    {
        $body = json_encode(array_merge(['event' => $event], $payload), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signature = hash_hmac('sha256', $body, $webhook->secret);

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'RosettaCMS-Webhook/1.0',
                    'X-Rosetta-Signature' => 'sha256=' . $signature,
                    'X-Rosetta-Event' => $event,
                    'X-Rosetta-Delivery' => (string) Str::uuid(),
                ])
                ->send('POST', $webhook->url, ['body' => $body]);

            $status = $response->status();
            $success = $response->successful();

            $webhook->recordDelivery($status, $success ? null : 'Endpoint returned HTTP ' . $status);

            return ['success' => $success, 'status' => $status, 'error' => $success ? null : 'Endpoint returned HTTP ' . $status];
        } catch (\Throwable $e) {
            $webhook->recordDelivery(null, $e->getMessage());

            return ['success' => false, 'status' => null, 'error' => $e->getMessage()];
        }
    }
}
