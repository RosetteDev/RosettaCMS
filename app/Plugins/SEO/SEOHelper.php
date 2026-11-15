<?php

namespace RosettaCMS\Plugins\SEO;

use Illuminate\Support\Str;

class SEOHelper
{
    /**
     * Generate meta title.
     *
     * @param string|null $title
     * @param string|null $separator
     * @return string
     */
    public function title(string $title = null, string $separator = '|'): string
    {
        $siteName = config('seo.site_name', 'Rosetta CMS');
        
        if (empty($title)) {
            return $siteName;
        }
        
        return "$title $separator $siteName";
    }

    /**
     * Generate meta description.
     *
     * @param string|null $description
     * @param int $length
     * @return string
     */
    public function description(string $description = null, int $length = 160): string
    {
        if (empty($description)) {
            return config('seo.default_description', '');
        }
        
        return Str::limit($description, $length);
    }

    /**
     * Generate meta keywords.
     *
     * @param array|string|null $keywords
     * @return string
     */
    public function keywords($keywords = null): string
    {
        if (empty($keywords)) {
            return config('seo.default_keywords', '');
        }
        
        if (is_array($keywords)) {
            return implode(', ', $keywords);
        }
        
        return $keywords;
    }

    /**
     * Generate canonical URL.
     *
     * @param string|null $url
     * @return string
     */
    public function canonical(string $url = null): string
    {
        if (empty($url)) {
            return url()->current();
        }
        
        return url($url);
    }

    /**
     * Generate robots meta tag.
     *
     * @param string|array $rules
     * @return string
     */
    public function robots($rules = 'index, follow'): string
    {
        if (is_array($rules)) {
            $rules = implode(', ', $rules);
        }
        
        return $rules;
    }

    /**
     * Generate Open Graph tags.
     *
     * @param array $data
     * @return array
     */
    public function openGraph(array $data = []): array
    {
        $defaults = [
            'type' => 'website',
            'title' => config('seo.og_title', config('seo.site_name')),
            'description' => config('seo.og_description', ''),
            'url' => url()->current(),
            'image' => config('seo.og_image', ''),
            'site_name' => config('seo.site_name'),
            'locale' => config('app.locale', 'en_US'),
        ];

        return array_merge($defaults, $data);
    }

    /**
     * Generate Twitter Card tags.
     *
     * @param array $data
     * @return array
     */
    public function twitterCards(array $data = []): array
    {
        $defaults = [
            'card' => 'summary_large_image',
            'title' => config('seo.twitter_title', ''),
            'description' => config('seo.twitter_description', ''),
            'image' => config('seo.twitter_image', ''),
            'site' => config('seo.twitter_site', ''),
            'creator' => config('seo.twitter_creator', ''),
        ];

        return array_merge($defaults, $data);
    }
}
