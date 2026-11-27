<?php

namespace App\Support;

/**
 * Builds the OpenAPI 3.0 document for the versioned public API
 * (/api/v1). Served at /api/v1/openapi.json and rendered as a docs page at
 * /api/v1/docs.
 */
class OpenApi
{
    public static function spec(): array
    {
        $base = url('/api/v1');

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Rosetta CMS Public API',
                'description' => "Versioned read/write API for Rosetta CMS.\n\n"
                    . "Read endpoints are public (rate limited). Write endpoints require an API key with the matching scope (`posts:write` / `pages:write`) passed as a Bearer token.\n\n"
                    . "Media and menus are read-only. API keys are managed in the admin at `/admin/api-keys`.",
                'version' => 'v1',
                'contact' => [
                    'name' => 'Rosetta CMS',
                ],
            ],
            'servers' => [
                ['url' => $base, 'description' => 'Current install'],
            ],
            'tags' => [
                ['name' => 'Discovery', 'description' => 'API discovery document'],
                ['name' => 'Posts', 'description' => 'List and fetch published posts'],
                ['name' => 'Pages', 'description' => 'List and fetch published pages'],
                ['name' => 'Categories', 'description' => 'List categories'],
                ['name' => 'Tags', 'description' => 'List tags'],
                ['name' => 'Search', 'description' => 'Full-text search over published posts'],
                ['name' => 'Media', 'description' => 'List and fetch media assets'],
                ['name' => 'Menus', 'description' => 'List and fetch navigation menus'],
                ['name' => 'Write', 'description' => 'Authenticated create/update/delete (API key required)'],
            ],
            'paths' => [
                '/' => [
                    'get' => [
                        'tags' => ['Discovery'],
                        'summary' => 'API discovery document',
                        'operationId' => 'discovery',
                        'responses' => [
                            '200' => self::jsonResponse('Discovery document', ['type' => 'object']),
                        ],
                    ],
                ],
                '/posts' => [
                    'get' => [
                        'tags' => ['Posts'],
                        'summary' => 'List published posts',
                        'operationId' => 'listPosts',
                        'parameters' => self::paginationParams(),
                        'responses' => [
                            '200' => self::jsonResponse('Paginated list of posts', ['type' => 'object']),
                        ],
                    ],
                    'post' => self::writeOperation('Create a post', 'createPost', ['posts:write']),
                ],
                '/posts/{slug}' => [
                    'get' => [
                        'tags' => ['Posts'],
                        'summary' => 'Fetch a published post by slug',
                        'operationId' => 'getPost',
                        'parameters' => self::slugParam(),
                        'responses' => [
                            '200' => self::jsonResponse('A single post', ['type' => 'object']),
                            '404' => self::errorResponse(),
                        ],
                    ],
                    'put' => self::writeOperation('Update a post', 'updatePost', ['posts:write'], self::slugParam()),
                    'delete' => self::writeOperation('Delete a post', 'deletePost', ['posts:write'], self::slugParam(), false),
                ],
                '/pages' => [
                    'get' => [
                        'tags' => ['Pages'],
                        'summary' => 'List published pages',
                        'operationId' => 'listPages',
                        'parameters' => self::paginationParams(),
                        'responses' => [
                            '200' => self::jsonResponse('Paginated list of pages', ['type' => 'object']),
                        ],
                    ],
                    'post' => self::writeOperation('Create a page', 'createPage', ['pages:write']),
                ],
                '/pages/{slug}' => [
                    'get' => [
                        'tags' => ['Pages'],
                        'summary' => 'Fetch a published page by slug',
                        'operationId' => 'getPage',
                        'parameters' => self::slugParam(),
                        'responses' => [
                            '200' => self::jsonResponse('A single page', ['type' => 'object']),
                            '404' => self::errorResponse(),
                        ],
                    ],
                    'put' => self::writeOperation('Update a page', 'updatePage', ['pages:write'], self::slugParam()),
                    'delete' => self::writeOperation('Delete a page', 'deletePage', ['pages:write'], self::slugParam(), false),
                ],
                '/categories' => [
                    'get' => [
                        'tags' => ['Categories'],
                        'summary' => 'List categories with published post counts',
                        'operationId' => 'listCategories',
                        'responses' => [
                            '200' => self::jsonResponse('List of categories', ['type' => 'object']),
                        ],
                    ],
                ],
                '/tags' => [
                    'get' => [
                        'tags' => ['Tags'],
                        'summary' => 'List tags with published post counts',
                        'operationId' => 'listTags',
                        'responses' => [
                            '200' => self::jsonResponse('List of tags', ['type' => 'object']),
                        ],
                    ],
                ],
                '/search' => [
                    'get' => [
                        'tags' => ['Search'],
                        'summary' => 'Search published posts',
                        'operationId' => 'searchPosts',
                        'parameters' => [
                            [
                                'name' => 'q',
                                'in' => 'query',
                                'required' => false,
                                'schema' => ['type' => 'string', 'maxLength' => 200],
                            ],
                        ],
                        'responses' => [
                            '200' => self::jsonResponse('Matching published posts', ['type' => 'object']),
                        ],
                    ],
                ],
                '/media' => [
                    'get' => [
                        'tags' => ['Media'],
                        'summary' => 'List media assets',
                        'operationId' => 'listMedia',
                        'parameters' => [
                            ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'minimum' => 1]],
                            ['name' => 'per_page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 10]],
                            ['name' => 'folder', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string']],
                            ['name' => 'type', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string', 'enum' => ['image', 'video', 'audio', 'document']]],
                            ['name' => 'search', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string', 'maxLength' => 200]],
                        ],
                        'responses' => [
                            '200' => self::jsonResponse('Paginated list of media', ['type' => 'object']),
                        ],
                    ],
                ],
                '/media/{id}' => [
                    'get' => [
                        'tags' => ['Media'],
                        'summary' => 'Fetch a media asset by id',
                        'operationId' => 'getMedia',
                        'parameters' => self::idParam(),
                        'responses' => [
                            '200' => self::jsonResponse('A single media asset', ['type' => 'object']),
                            '404' => self::errorResponse(),
                        ],
                    ],
                ],
                '/menus' => [
                    'get' => [
                        'tags' => ['Menus'],
                        'summary' => 'List navigation menus',
                        'operationId' => 'listMenus',
                        'responses' => [
                            '200' => self::jsonResponse('List of menus', ['type' => 'object']),
                        ],
                    ],
                ],
                '/menus/{slug}' => [
                    'get' => [
                        'tags' => ['Menus'],
                        'summary' => 'Fetch a menu with its full item tree',
                        'operationId' => 'getMenu',
                        'parameters' => self::slugParam(),
                        'responses' => [
                            '200' => self::jsonResponse('A single menu', ['type' => 'object']),
                            '404' => self::errorResponse(),
                        ],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'API key',
                        'description' => 'API key generated at /admin/api-keys. Send as `Authorization: Bearer rk_...`.',
                    ],
                ],
                'schemas' => [
                    'Post' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'type' => ['type' => 'string', 'example' => 'post'],
                            'slug' => ['type' => 'string'],
                            'status' => ['type' => 'string', 'enum' => ['draft', 'pending', 'published', 'private', 'trash']],
                            'title' => ['type' => 'string'],
                            'excerpt' => ['type' => 'string'],
                            'content' => ['type' => 'string'],
                            'url' => ['type' => 'string'],
                            'featured_image' => ['type' => ['string', 'null']],
                            'author' => [
                                'type' => ['object', 'null'],
                                'properties' => [
                                    'id' => ['type' => 'integer'],
                                    'name' => ['type' => 'string'],
                                ],
                            ],
                            'categories' => ['type' => 'array', 'items' => ['type' => 'object']],
                            'tags' => ['type' => 'array', 'items' => ['type' => 'object']],
                            'published_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                            'updated_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                        ],
                    ],
                    'Media' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'file_name' => ['type' => 'string'],
                            'url' => ['type' => 'string'],
                            'folder' => ['type' => 'string'],
                            'mime_type' => ['type' => 'string'],
                            'file_size' => ['type' => 'integer'],
                            'human_size' => ['type' => 'string'],
                            'alt_text' => ['type' => ['string', 'null']],
                            'caption' => ['type' => ['string', 'null']],
                            'is_image' => ['type' => 'boolean'],
                            'created_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                        ],
                    ],
                    'Menu' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'slug' => ['type' => 'string'],
                            'location' => ['type' => 'string'],
                            'item_count' => ['type' => 'integer'],
                            'items' => [
                                'type' => 'array',
                                'items' => ['$ref' => '#/components/schemas/MenuItem'],
                            ],
                        ],
                    ],
                    'MenuItem' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'title' => ['type' => 'string'],
                            'type' => ['type' => 'string', 'example' => 'page'],
                            'url' => ['type' => 'string'],
                            'target' => ['type' => ['string', 'null']],
                            'order' => ['type' => 'integer'],
                            'children' => [
                                'type' => 'array',
                                'items' => ['$ref' => '#/components/schemas/MenuItem'],
                            ],
                        ],
                    ],
                    'Error' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string'],
                            'errors' => ['type' => 'object'],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected static function slugParam(): array
    {
        return [[
            'name' => 'slug',
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'string'],
        ]];
    }

    protected static function idParam(): array
    {
        return [[
            'name' => 'id',
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'integer'],
        ]];
    }

    protected static function paginationParams(): array
    {
        return [
            [
                'name' => 'page',
                'in' => 'query',
                'required' => false,
                'schema' => ['type' => 'integer', 'minimum' => 1],
            ],
            [
                'name' => 'per_page',
                'in' => 'query',
                'required' => false,
                'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 10],
            ],
            [
                'name' => 'category',
                'in' => 'query',
                'required' => false,
                'schema' => ['type' => 'string'],
            ],
            [
                'name' => 'tag',
                'in' => 'query',
                'required' => false,
                'schema' => ['type' => 'string'],
            ],
            [
                'name' => 'search',
                'in' => 'query',
                'required' => false,
                'schema' => ['type' => 'string'],
            ],
        ];
    }

    protected static function writeOperation(string $summary, string $operationId, array $scopes, array $pathParams = [], bool $returnsBody = true): array
    {
        $parameters = array_merge($pathParams, [
            [
                'name' => 'body',
                'in' => 'body',
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'title' => ['type' => 'string'],
                                'content' => ['type' => 'string'],
                                'excerpt' => ['type' => 'string'],
                                'status' => ['type' => 'string', 'enum' => ['draft', 'pending', 'published', 'private']],
                                'published_at' => ['type' => 'string', 'format' => 'date-time'],
                                'categories' => ['type' => 'array', 'items' => ['type' => 'integer']],
                                'tags' => ['type' => 'array', 'items' => ['type' => 'integer']],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $responses = [
            '201' => self::jsonResponse('Created', ['type' => 'object']),
            '200' => self::jsonResponse('Updated', ['type' => 'object']),
            '400' => self::errorResponse('Validation failed'),
            '401' => self::errorResponse('Missing or invalid API key'),
            '403' => self::errorResponse('Insufficient scope'),
        ];

        if (!$returnsBody) {
            $responses = ['204' => self::jsonResponse('Deleted', ['type' => 'object'])] + $responses;
        }

        return [
            'tags' => ['Write'],
            'summary' => $summary,
            'operationId' => $operationId,
            'security' => [['bearerAuth' => []]],
            'parameters' => $parameters,
            'responses' => $responses,
        ];
    }

    protected static function jsonResponse(string $description, array $schema, string $type = 'application/json'): array
    {
        return [
            'description' => $description,
            'content' => [
                $type => [
                    'schema' => $schema,
                ],
            ],
        ];
    }

    protected static function errorResponse(string $description = 'Error'): array
    {
        return self::jsonResponse($description, ['$ref' => '#/components/schemas/Error']);
    }
}
