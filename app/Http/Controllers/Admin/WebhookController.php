<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WebhookController extends Controller
{
    /**
     * List webhooks for the current site (plus site-agnostic ones).
     */
    public function index()
    {
        $site = Site::first();

        $webhooks = Webhook::query()
            ->where(function ($q) use ($site) {
                $q->whereNull('site_id');
                if ($site) {
                    $q->orWhere('site_id', $site->id);
                }
            })
            ->orderByDesc('created_at')
            ->get();

        return view('admin.webhooks.index', compact('webhooks', 'site'));
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        return view('admin.webhooks.create', [
            'availableEvents' => Webhook::ALLOWED_EVENTS,
        ]);
    }

    /**
     * Store a newly created webhook.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url:http,https|max:500',
            'description' => 'nullable|string|max:255',
            'events' => 'required|array|min:1',
            'events.*' => ['string', Rule::in(Webhook::ALLOWED_EVENTS)],
        ]);

        $site = Site::first();

        $result = Webhook::createWebhook(
            $site?->id,
            $validated['url'],
            array_values(array_unique($validated['events'])),
            $validated['description'] ?? null
        );

        // Stash the plaintext secret in the session for one-time display.
        session()->flash('webhook_secret', $result['secret']);

        return redirect()->route('admin.webhooks.index')
            ->with('success', 'Webhook created. Copy the signing secret now — it will not be shown again.');
    }

    /**
     * Send a test delivery to the given webhook.
     */
    public function test(Webhook $webhook, WebhookService $service)
    {
        $result = $service->test($webhook);

        if ($result['success']) {
            return redirect()->route('admin.webhooks.index')
                ->with('success', "Test delivery succeeded (HTTP {$result['status']}).");
        }

        return redirect()->route('admin.webhooks.index')
            ->with('error', 'Test delivery failed: ' . ($result['error'] ?? 'unknown error'));
    }

    /**
     * Delete a webhook.
     */
    public function destroy(Webhook $webhook)
    {
        $webhook->delete();

        return redirect()->route('admin.webhooks.index')
            ->with('success', 'Webhook deleted.');
    }
}
