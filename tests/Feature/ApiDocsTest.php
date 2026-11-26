<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDocsTest extends TestCase
{
    use RefreshDatabase;

    public function test_openapi_document_is_served_as_json(): void
    {
        $response = $this->getJson('/api/v1/openapi.json')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json');

        $spec = $response->json();

        $this->assertSame('3.0.3', $spec['openapi']);
        $this->assertSame('Rosetta CMS Public API', $spec['info']['title']);
        $this->assertSame('v1', $spec['info']['version']);
        $this->assertArrayHasKey('/posts', $spec['paths']);
        $this->assertArrayHasKey('/pages', $spec['paths']);
        $this->assertArrayHasKey('/categories', $spec['paths']);
        $this->assertArrayHasKey('/tags', $spec['paths']);
        $this->assertArrayHasKey('/search', $spec['paths']);
        $this->assertArrayHasKey('/posts/{slug}', $spec['paths']);
        $this->assertArrayHasKey('/pages/{slug}', $spec['paths']);
        $this->assertArrayHasKey('components', $spec);
        $this->assertArrayHasKey('schemas', $spec['components']);
        $this->assertArrayHasKey('securitySchemes', $spec['components']);
    }

    public function test_openapi_document_documents_write_operations_with_auth(): void
    {
        $spec = $this->getJson('/api/v1/openapi.json')->json();

        $postCreate = $spec['paths']['/posts']['post'] ?? null;
        $postDelete = $spec['paths']['/posts/{slug}']['delete'] ?? null;

        $this->assertNotNull($postCreate);
        $this->assertNotNull($postDelete);
        $this->assertNotEmpty($postCreate['security'] ?? []);
        $this->assertNotEmpty($postDelete['security'] ?? []);
    }

    public function test_docs_page_renders_for_anonymous_users(): void
    {
        $response = $this->get('/api/v1/docs')
            ->assertOk()
            ->assertSee('Rosetta CMS Public API', false)
            ->assertSee('Authentication', false)
            ->assertSee('Webhooks', false);

        $this->assertStringContainsString('text/html', $response->headers->get('Content-Type'));
    }

    public function test_docs_page_lists_all_tagged_endpoints(): void
    {
        $response = $this->get('/api/v1/docs')->assertOk();

        foreach (['/posts', '/pages', '/categories', '/tags', '/search'] as $path) {
            $response->assertSee($path, false);
        }
    }

    public function test_openapi_and_docs_routes_are_not_admin_only(): void
    {
        $this->get('/api/v1/openapi.json')->assertOk();
        $this->get('/api/v1/docs')->assertOk();
    }
}
