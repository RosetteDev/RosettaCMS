<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $spec['info']['title'] }} — Swagger UI</title>
    <link rel="icon" type="image/png" href="{{ asset('vendor/swagger-ui/favicon-32x32.png') }}">
    <link rel="stylesheet" href="{{ asset('vendor/swagger-ui/swagger-ui.css') }}">
    <style>
        html { box-sizing: border-box; }
        *, *::before, *::after { box-sizing: inherit; }
        body { margin: 0; background: #fafafa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif; }
        .swagger-ui { max-width: 1400px; margin: 0 auto; padding: 0 1rem 3rem; }
        .swagger-ui .topbar { display: none; }
        .swagger-ui .info .title { font-weight: 700; color: #1f2937; }
        .swagger-ui .info .description p { color: #4b5563; }
        .swagger-ui .opblock-tag { border-bottom: 1px solid #e5e7eb; padding-bottom: .5rem; }
        .swagger-ui .btn.execute { background: #7c3aed; border-color: #7c3aed; }
        .swagger-ui .btn.execute:hover { background: #6d28d9; border-color: #6d28d9; }
        .swagger-ui .scheme-container { background: #ffffff; box-shadow: none; border-bottom: 1px solid #e5e7eb; padding: .5rem 0; }
        .api-footer { text-align: center; color: #9ca3af; font-size: .8rem; margin-top: 1rem; }
        .api-footer a { color: #7c3aed; text-decoration: none; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <div class="api-footer">
        <a href="{{ route('api.docs') }}">Human-readable docs</a> &nbsp;·&nbsp; <a href="{{ route('api.openapi') }}">OpenAPI JSON</a>
    </div>
    <script src="{{ asset('vendor/swagger-ui/swagger-ui-bundle.js') }}"></script>
    <script src="{{ asset('vendor/swagger-ui/swagger-ui-standalone-preset.js') }}"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "{{ route('api.openapi') }}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
                plugins: [SwaggerUIBundle.plugins.DownloadUrl],
                layout: 'BaseLayout',
                docExpansion: 'list',
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1,
                displayOperationId: false,
                filter: true,
                tryItOutEnabled: true,
            });
        };
    </script>
</body>
</html>
