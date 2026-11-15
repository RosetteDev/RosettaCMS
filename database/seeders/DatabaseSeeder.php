<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use App\Models\Taxonomy;
use App\Models\Term;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default site
        $site = Site::create([
            'name' => 'My Website',
            'slug' => 'default',
            'domain' => null,
            'description' => 'Default website for Rosetta CMS',
            'timezone' => 'UTC',
            'locale' => 'en',
            'currency' => 'USD',
            'is_active' => true,
        ]);

        // Create default roles
        $adminRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
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
        ]);

        // Create default admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@rosettacms.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->roles()->attach($adminRole->id);

        $editorRole = Role::create([
            'name' => 'Editor',
            'slug' => 'editor',
            'description' => 'Manage content and media',
            'permissions' => [
                'dashboard' => true,
                'posts' => true,
                'pages' => true,
                'media' => true,
                'seo' => true,
            ],
        ]);

        $authorRole = Role::create([
            'name' => 'Author',
            'slug' => 'author',
            'description' => 'Create and manage own content',
            'permissions' => [
                'dashboard' => true,
                'posts' => true,
                'media' => true,
            ],
        ]);
    }
}
