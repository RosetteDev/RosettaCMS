<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\OpenApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DocsController extends Controller
{
    /**
     * Serve the OpenAPI 3.0 document as JSON.
     */
    public function spec(): JsonResponse
    {
        $this->ensurePublicDocsEnabled();

        // CORS is handled by the allowlist in config/cors.php — no wildcard.
        return response()->json(OpenApi::spec(), 200);
    }

    /**
     * Serve the in-app documentation page.
     */
    public function docs(): Response
    {
        $this->ensurePublicDocsEnabled();

        return response()->view('api.docs', [
            'spec' => OpenApi::spec(),
        ]);
    }

    /**
     * Serve the in-app Swagger UI documentation page.
     */
    public function swagger(): Response
    {
        $this->ensurePublicDocsEnabled();

        return response()->view('api.swagger', [
            'spec' => OpenApi::spec(),
        ]);
    }

    /**
     * Public API docs are hidden unless explicitly enabled (audit #28).
     * When disabled the endpoints 404 rather than reveal the spec.
     */
    protected function ensurePublicDocsEnabled(): void
    {
        abort_unless(config('rosetta.public_api_docs'), 404);
    }
}
