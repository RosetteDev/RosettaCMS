<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'name' => 'Laravel',
                'slug' => 'laravel',
                'description' => 'The PHP framework for web artisans',
                'order' => 1,
            ],
            [
                'name' => 'PHP',
                'slug' => 'php',
                'description' => 'Popular general-purpose scripting language',
                'order' => 2,
            ],
            [
                'name' => 'JavaScript',
                'slug' => 'javascript',
                'description' => 'Programming language for web development',
                'order' => 3,
            ],
            [
                'name' => 'CSS',
                'slug' => 'css',
                'description' => 'Style sheet language for describing presentation',
                'order' => 4,
            ],
            [
                'name' => 'HTML',
                'slug' => 'html',
                'description' => 'Standard markup language for documents',
                'order' => 5,
            ],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['slug' => $tag['slug']],
                $tag
            );
        }
    }
}
