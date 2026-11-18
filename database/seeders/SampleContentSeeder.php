<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Media;
use App\Models\Taxonomy;
use App\Models\Term;
use App\Models\User;
use App\Models\Site;
use Illuminate\Support\Facades\Hash;

class SampleContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $site = Site::first();
        $adminUser = User::first();

        if (!$site || !$adminUser) {
            $this->command?->warn('Skipping sample content: run DatabaseSeeder first.');
            return;
        }

        // Create (or reuse) additional test users
        $editorUser = User::firstOrCreate(
            ['email' => 'editor@rosettacms.com'],
            [
                'name' => 'Editor User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $authorUser = User::firstOrCreate(
            ['email' => 'author@rosettacms.com'],
            [
                'name' => 'Author User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $subscriberUser = User::firstOrCreate(
            ['email' => 'subscriber@example.com'],
            [
                'name' => 'Subscriber User',
                'password' => Hash::make('password'),
            ]
        );

        // Assign roles to additional users without duplicating pivots
        $editorRole = \App\Models\Role::where('slug', 'editor')->first();
        $authorRole = \App\Models\Role::where('slug', 'author')->first();
        $subscriberRole = \App\Models\Role::where('slug', 'subscriber')->first();

        if ($editorRole) {
            $editorUser->roles()->syncWithoutDetaching([$editorRole->id]);
        }

        if ($authorRole) {
            $authorUser->roles()->syncWithoutDetaching([$authorRole->id]);
        }

        if ($subscriberRole) {
            $subscriberUser->roles()->syncWithoutDetaching([$subscriberRole->id]);
        }

        // Create taxonomies (with site_id)
        $categoryTaxonomy = Taxonomy::firstOrCreate(
            ['slug' => 'category', 'site_id' => $site->id, 'type' => 'category'],
            ['name' => 'Category', 'description' => 'Category taxonomy']
        );

        $tagTaxonomy = Taxonomy::firstOrCreate(
            ['slug' => 'post_tag', 'site_id' => $site->id, 'type' => 'post_tag'],
            ['name' => 'Tags', 'description' => 'Tag taxonomy']
        );

        // Create categories
        $techCategory = Term::firstOrCreate(
            ['slug' => 'technology', 'taxonomy_id' => $categoryTaxonomy->id],
            ['name' => 'Technology', 'description' => 'Tech news and reviews']
        );

        $businessCategory = Term::firstOrCreate(
            ['slug' => 'business', 'taxonomy_id' => $categoryTaxonomy->id],
            ['name' => 'Business', 'description' => 'Business news and analysis']
        );

        // Create tags
        $laravelTag = Term::firstOrCreate(
            ['slug' => 'laravel', 'taxonomy_id' => $tagTaxonomy->id],
            ['name' => 'Laravel', 'description' => 'Laravel PHP framework']
        );

        $cmsTag = Term::firstOrCreate(
            ['slug' => 'cms', 'taxonomy_id' => $tagTaxonomy->id],
            ['name' => 'CMS', 'description' => 'Content Management System']
        );

        // Create sample posts
        $posts = [
            [
                'title' => 'Welcome to Rosetta CMS',
                'slug' => 'welcome-to-rosetta-cms',
                'content' => '# Welcome to Rosetta CMS

This is the first post on your new Rosetta CMS website. We hope you enjoy building your content with our flexible and modern CMS platform.

## Features

- Easy-to-use admin panel
- Multi-site support
- SEO-friendly
- Media management
- Customizable themes

## Get Started

Explore the admin panel to manage your content, users, and settings. Happy publishing!',
                'status' => 'published',
                'post_type' => 'post',
                'comment_status' => 'open',
            ],
            [
                'title' => 'Building a Blog with Rosetta CMS',
                'slug' => 'building-a-blog-with-rosetta-cms',
                'content' => '# Building a Blog with Rosetta CMS

Rosetta CMS provides everything you need to build a powerful blog or website. Our intuitive admin panel makes it easy to create and manage content.

## Why Choose Rosetta CMS?

- Modern technology stack
- Flexible plugin system
- Built-in SEO tools
- Multi-site capabilities

Start building your dream website today!',
                'status' => 'published',
                'post_type' => 'post',
                'comment_status' => 'open',
            ],
            [
                'title' => 'About Our CMS',
                'slug' => 'about-our-cms',
                'content' => '# About Rosetta CMS

Rosetta CMS is a modern, flexible content management system built with Laravel. We focus on providing an excellent user experience while maintaining high performance and security standards.

## Our Mission

To empower businesses and individuals to create and manage digital content with ease and confidence.',
                'status' => 'published',
                'post_type' => 'page',
                'comment_status' => 'closed',
            ],
        ];

        foreach ($posts as $postData) {
            $post = Post::firstOrCreate(
                ['slug' => $postData['slug'], 'site_id' => $site->id],
                [
                    'user_id' => $adminUser->id,
                    'title' => $postData['title'],
                    'content' => $postData['content'],
                    'status' => $postData['status'],
                    'post_type' => $postData['post_type'],
                ]
            );

            // Attach categories and tags without duplicating pivots
            if ($postData['post_type'] === 'post') {
                $post->terms()->syncWithoutDetaching([$techCategory->id, $laravelTag->id]);
            }
        }

        // Create sample media
        $media = [
            [
                'file_name' => 'sample-image-1.jpg',
                'file_path' => 'media/sample-image-1.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 102400,
                'alt_text' => 'Sample image 1',
            ],
            [
                'file_name' => 'sample-document.pdf',
                'file_path' => 'media/sample-document.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 51200,
                'alt_text' => 'Sample document',
            ],
        ];

        foreach ($media as $mediaData) {
            Media::firstOrCreate(
                ['file_path' => $mediaData['file_path'], 'site_id' => $site->id],
                [
                    'user_id' => $adminUser->id,
                    ...$mediaData,
                ]
            );
        }
    }
}
