<?php

namespace RosettaCMS\Plugins\SEO;

class SEOService
{
    /**
     * Generate all SEO tags.
     *
     * @param array $data
     * @return string
     */
    public function all(array $data = []): string
    {
        $html = '';
        
        // Title
        $html .= $this->title($data['title'] ?? null);
        
        // Meta description
        $html .= $this->meta('description', $data['description'] ?? null);
        
        // Meta keywords
        $html .= $this->meta('keywords', $data['keywords'] ?? null);
        
        // Canonical URL
        $html .= $this->canonical($data['canonical'] ?? null);
        
        // Robots
        $html .= $this->robots($data['robots'] ?? null);
        
        // Open Graph
        foreach ($this->openGraph($data['open_graph'] ?? []) as $property => $content) {
            $html .= $this->meta("og:$property", $content, 'property');
        }
        
        // Twitter Cards
        foreach ($this->twitterCards($data['twitter'] ?? []) as $name => $content) {
            $html .= $this->meta("twitter:$name", $content, 'name');
        }
        
        return $html;
    }

    /**
     * Generate title tag.
     *
     * @param string|null $title
     * @return string
     */
    public function title(string $title = null): string
    {
        $title = $this->cleanTitle($title);
        return "<title>$title</title>\n";
    }

    /**
     * Generate meta tag.
     *
     * @param string $name
     * @param string|null $content
     * @param string $attribute
     * @return string
     */
    public function meta(string $name, string $content = null, string $attribute = 'name'): string
    {
        if (empty($content)) {
            return '';
        }
        
        $content = e($content);
        return "<meta $attribute=\"$name\" content=\"$content\">\n";
    }

    /**
     * Generate canonical URL tag.
     *
     * @param string|null $url
     * @return string
     */
    public function canonical(string $url = null): string
    {
        $url = $url ?? url()->current();
        return "<link rel=\"canonical\" href=\"$url\">\n";
    }

    /**
     * Generate robots meta tag.
     *
     * @param string|array|null $rules
     * @return string
     */
    public function robots($rules = null): string
    {
        if (empty($rules)) {
            return '';
        }
        
        if (is_array($rules)) {
            $rules = implode(', ', $rules);
        }
        
        return "<meta name=\"robots\" content=\"$rules\">\n";
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

    /**
     * Clean title for display.
     *
     * @param string|null $title
     * @return string
     */
    protected function cleanTitle(string $title = null): string
    {
        $siteName = config('seo.site_name', 'Rosetta CMS');
        
        if (empty($title)) {
            return $siteName;
        }
        
        $separator = config('seo.title_separator', '|');
        return "$title $separator $siteName";
    }
}
