<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Discovers and validates third-party plugins (plugin.json manifests) in the
 * plugins/ directory and runs a static security scan over their PHP files.
 *
 * Manifest format (plugins/{slug}/plugin.json):
 * {
 *   "name": "Example Plugin",
 *   "slug": "example-plugin",
 *   "version": "1.0.0",
 *   "description": "...",
 *   "author": "Author",
 *   "entry": "src/index.php",
 *   "permissions": ["filesystem", "network"],
 *   "requires_rosetta": ">=11.0"
 * }
 */
class PluginEcosystemService
{
    /** Permissions a third-party plugin may request. */
    public const PERMISSIONS = [
        'filesystem',   // read/write files outside its own folder
        'network',      // outbound HTTP requests
        'shell',        // execute shell commands
        'database',     // direct database access
        'admin',        // access admin/user data
    ];

    /** Patterns flagged by the static security scan, with severity. */
    public const DANGEROUS_PATTERNS = [
        ['pattern' => '/\beval\s*\(/i', 'severity' => 'critical', 'label' => 'eval() — executes arbitrary PHP'],
        ['pattern' => '/\bbase64_decode\s*\(/i', 'severity' => 'warning', 'label' => 'base64_decode() — often used to hide code'],
        ['pattern' => '/\b(system|shell_exec|exec|passthru|popen|proc_open)\s*\(/i', 'severity' => 'critical', 'label' => 'shell execution function'],
        ['pattern' => '/\b(unlink|rmdir|chmod|chown)\s*\(/i', 'severity' => 'warning', 'label' => 'filesystem mutation function'],
        ['pattern' => '/\bfile_put_contents\s*\(/i', 'severity' => 'warning', 'label' => 'file_put_contents() — writes files'],
        ['pattern' => '/\b(extract|parse_ini_file|unserialize)\s*\(/i', 'severity' => 'warning', 'label' => 'unsafe input handling'],
        ['pattern' => '/\$_(GET|POST|REQUEST|COOKIE|SERVER)\b/i', 'severity' => 'info', 'label' => 'direct superglobal access'],
    ];

    /**
     * Discover third-party plugins in plugins/ with a valid manifest.
     *
     * @return array<int, array{id: string, slug: string, name: string, version: string, description: string, author: string, entry: string|null, permissions: array, errors: array, findings: array, path: string}>
     */
    public function discover(): array
    {
        $plugins = [];
        $root = base_path('plugins');

        if (!is_dir($root)) {
            return $plugins;
        }

        foreach (File::directories($root) as $dir) {
            $manifestPath = $dir . '/plugin.json';

            if (!File::exists($manifestPath)) {
                continue;
            }

            $raw = File::get($manifestPath);
            $manifest = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($manifest)) {
                $plugins[] = [
                    'id' => basename($dir),
                    'slug' => basename($dir),
                    'name' => basename($dir),
                    'version' => '?',
                    'description' => '',
                    'author' => '',
                    'entry' => null,
                    'permissions' => [],
                    'errors' => ['plugin.json is not valid JSON'],
                    'findings' => [],
                    'path' => $dir,
                ];
                continue;
            }

            $slug = $manifest['slug'] ?? basename($dir);
            $errors = $this->validateManifest($manifest, $slug);

            $plugins[] = [
                'id' => $slug,
                'slug' => $slug,
                'name' => $manifest['name'] ?? basename($dir),
                'version' => $manifest['version'] ?? '?',
                'description' => $manifest['description'] ?? '',
                'author' => $manifest['author'] ?? '',
                'entry' => $manifest['entry'] ?? null,
                'permissions' => $this->normalizePermissions($manifest['permissions'] ?? []),
                'errors' => $errors,
                'findings' => $this->scanSecurity($dir),
                'path' => $dir,
            ];
        }

        return $plugins;
    }

    /**
     * Validate a manifest. Returns a list of human-readable errors.
     */
    public function validateManifest(array $manifest, string $dirName): array
    {
        $errors = [];

        if (empty($manifest['name'])) {
            $errors[] = 'Missing required field: name';
        }

        $slug = $manifest['slug'] ?? $dirName;

        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
            $errors[] = 'Slug "' . $slug . '" must be lowercase kebab-case';
        }

        if (empty($manifest['version']) || !preg_match('/^\d+\.\d+\.\d+/', $manifest['version'])) {
            $errors[] = 'Version "' . ($manifest['version'] ?? '') . '" must look like semver (e.g. 1.0.0)';
        }

        if (!empty($manifest['permissions']) && !is_array($manifest['permissions'])) {
            $errors[] = 'permissions must be an array';
        }

        foreach ($this->normalizePermissions($manifest['permissions'] ?? []) as $permission) {
            if (!in_array($permission, self::PERMISSIONS, true)) {
                $errors[] = 'Unknown permission: ' . $permission;
            }
        }

        if (!empty($manifest['entry'])) {
            $entry = $manifest['entry'];
            if (str_contains($entry, '..') || str_starts_with($entry, '/') || preg_match('/^[a-zA-Z]:/', $entry)) {
                $errors[] = 'Entry path "' . $entry . '" must be relative and stay inside the plugin folder';
            }
        }

        return $errors;
    }

    /**
     * Static security scan of the plugin's PHP files.
     *
     * @return array<int, array{file: string, line: int, label: string, severity: string}>
     */
    public function scanSecurity(string $path): array
    {
        $findings = [];

        if (!is_dir($path)) {
            return $findings;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }

            $lines = @file($file->getPathname(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($lines === false) {
                continue;
            }

            foreach ($lines as $i => $line) {
                // Strip single-line comments before scanning (naive but effective).
                $line = preg_replace('/(\/\/|#).*$/', '', $line) ?? $line;

                if (trim($line) === '') {
                    continue;
                }

                foreach (self::DANGEROUS_PATTERNS as $rule) {
                    if (preg_match($rule['pattern'], $line)) {
                        $findings[] = [
                            'file' => str_replace($path . '/', '', $file->getPathname()),
                            'line' => $i + 1,
                            'label' => $rule['label'],
                            'severity' => $rule['severity'],
                        ];
                    }
                }
            }
        }

        return $findings;
    }

    /**
     * Check whether a plugin's requested permissions are all approved.
     *
     * @return array<int, string> missing permission names
     */
    public function missingPermissions(array $permissions, array $approvedPermissions): array
    {
        $approved = array_map('strtolower', $approvedPermissions);

        return array_values(array_filter($permissions, fn ($p) => !in_array(strtolower($p), $approved, true)));
    }

    protected function normalizePermissions($permissions): array
    {
        if (!is_array($permissions)) {
            return [];
        }

        $out = [];

        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $normalized = strtolower(trim($permission));
                if ($normalized !== '' && !in_array($normalized, $out, true)) {
                    $out[] = $normalized;
                }
            }
        }

        return $out;
    }
}
