<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Post Types
    |--------------------------------------------------------------------------
    |
    | The registry of content types supported by the CMS. Each entry describes
    | the type's label, plural label, description, icon, and the fields that
    | appear in its dedicated admin form. The "post" type is the default blog
    | post and is handled by the dedicated Posts controller; every other type
    | is served by the generic CustomPostTypeController.
    |
    */

    'post' => [
        'label' => 'Post',
        'plural' => 'Posts',
        'description' => 'Standard blog posts and articles',
        'icon' => 'fas fa-file-alt',
        'menu' => 'Posts',
        'fields' => ['title', 'excerpt', 'content', 'seo_title', 'seo_description'],
        'supports' => ['categories', 'tags'],
        'translatable' => true,
        'dedicated_controller' => true,
    ],

    'portfolio' => [
        'label' => 'Portfolio Item',
        'plural' => 'Portfolio',
        'description' => 'Showcase projects, case studies, and work highlights',
        'icon' => 'fas fa-briefcase',
        'menu' => 'Portfolio',
        'fields' => ['title', 'excerpt', 'content', 'seo_title', 'seo_description'],
        'supports' => ['categories'],
        'translatable' => true,
        'dedicated_controller' => false,
    ],

    'testimonial' => [
        'label' => 'Testimonial',
        'plural' => 'Testimonials',
        'description' => 'Client quotes and endorsements',
        'icon' => 'fas fa-quote-right',
        'menu' => 'Testimonials',
        'fields' => ['title', 'content', 'excerpt', 'seo_title', 'seo_description'],
        'supports' => [],
        'translatable' => true,
        'dedicated_controller' => false,
    ],

    'event' => [
        'label' => 'Event',
        'plural' => 'Events',
        'description' => 'Upcoming and past events with dates and venues',
        'icon' => 'fas fa-calendar-alt',
        'menu' => 'Events',
        'fields' => ['title', 'excerpt', 'content', 'seo_title', 'seo_description'],
        'supports' => ['categories'],
        'translatable' => true,
        'dedicated_controller' => false,
    ],

];
