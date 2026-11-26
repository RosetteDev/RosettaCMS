<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey;

class AuthenticateApiKey
{
    /**
     * Resolve the API key for the current request.
     *
     * Accepts either `Authorization: Bearer rk_...` or an `X-API-Key` header.
     * The scope is set on the route as `{scope}` — for example `posts:write`.
     */
    public function handle(Request $request, Closure $next, ?string $scope = null): Response
    {
        $plaintext = $this->extractKey($request);

        if (!$plaintext) {
            return response()->json([
                'error' => 'unauthenticated',
                'message' => 'An API key is required. Send `Authorization: Bearer rk_...` or `X-API-Key: rk_...`.',
            ], 401);
        }

        $key = ApiKey::findValid($plaintext);

        if (!$key || !$key->isValid()) {
            return response()->json([
                'error' => 'invalid_key',
                'message' => 'The API key is invalid, expired, or revoked.',
            ], 401);
        }

        if ($scope && !$key->hasScope($scope)) {
            return response()->json([
                'error' => 'insufficient_scope',
                'message' => "This API key does not have the required scope: {$scope}",
            ], 403);
        }

        $key->touchUsage();

        $request->attributes->set('apiKey', $key);

        return $next($request);
    }

    /**
     * Extract the plaintext key from the request headers.
     *
     * Only headers are accepted: keys in query strings would leak into
     * access logs, proxies and browser history, so they are deliberately
     * not supported.
     */
    protected function extractKey(Request $request): ?string
    {
        $auth = $request->header('Authorization', '');

        if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return trim($m[1]);
        }

        $xApiKey = $request->header('X-API-Key');

        if ($xApiKey) {
            return trim($xApiKey);
        }

        return null;
    }
}
