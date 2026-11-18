<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Site;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create (or reuse) the default site
        $site = Site::firstOrCreate(
            ['name' => 'My Website'],
            [
                'domain' => null,
                'description' => 'Default website for Rosetta CMS',
                'timezone' => 'UTC',
                'locale' => 'en',
                'currency' => 'USD',
                'is_active' => true,
            ]
        );

        // Ensure the site has a settings row
        SiteSetting::firstOrCreate(
            ['site_id' => $site->id],
            [
                'site_title' => 'Rosetta CMS',
                'site_description' => 'A modern, flexible content management system',
                'admin_email' => 'admin@rosettacms.com',
                'post_per_page' => 10,
            ]
        );

        // Create (or reuse) the default roles
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full access to all features',
                'permissions' => [
                    'dashboard' => true,
                    'posts' => true,
                    'pages' => true,
                    'media' => true,
                    'users' => true,
                    'settings' => true,
                    'plugins' => true,
                    'themes' => true,
                    'seo' => true,
                    'multi_site' => true,
                ],
            ]
        );

        $editorRole = Role::firstOrCreate(
            ['slug' => 'editor'],
            [
                'name' => 'Editor',
                'description' => 'Manage content and media',
                'permissions' => [
                    'dashboard' => true,
                    'posts' => true,
                    'pages' => true,
                    'media' => true,
                    'seo' => true,
                ],
            ]
        );

        $authorRole = Role::firstOrCreate(
            ['slug' => 'author'],
            [
                'name' => 'Author',
                'description' => 'Create and manage own content',
                'permissions' => [
                    'dashboard' => true,
                    'posts' => true,
                    'media' => true,
                ],
            ]
        );

        $subscriberRole = Role::firstOrCreate(
            ['slug' => 'subscriber'],
            [
                'name' => 'Subscriber',
                'description' => 'Basic access with limited permissions',
                'permissions' => [
                    'dashboard' => true,
                    'comments' => true,
                ],
            ]
        );

        // Create (or reuse) the default admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@rosettacms.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Attach roles without duplicating existing pivots
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        // Run sample content seeder
        $this->call(SampleContentSeeder::class);
    }
}
