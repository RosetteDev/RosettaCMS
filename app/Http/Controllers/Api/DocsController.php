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
        return response()->json(OpenApi::spec(), 200, [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Serve the in-app documentation page.
     */
    public function docs(): Response
    {
        return response()->view('api.docs', [
            'spec' => OpenApi::spec(),
        ]);
    }
}
