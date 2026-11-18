<?php

namespace App\Traits;

use Mews\Purifier\Facades\Purifier;

trait SanitizeContent
{
    /**
     * Sanitize HTML content to prevent XSS attacks.
     *
     * HTMLPurifier's `HTML.Allowed` directive expects a comma separated list
     * of tags, with attributes in the form `tag[attr1|attr2]`.
     *
     * @param  string  $content
     * @return string
     */
    public function sanitizeContent(string $content): string
    {
        $allowed = implode(',', [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li',
            'a[href|title|target|rel]',
            'img[src|alt|title|width|height]',
            'blockquote', 'code', 'pre',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'div', 'span', 'hr', 'figure', 'figcaption',
            'iframe[src|width|height|frameborder|allowfullscreen]',
        ]);

        return Purifier::clean($content, [
            'HTML.Allowed' => $allowed,
            'Attr.EnableID' => true,
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^https://(www\.)?(youtube\.com/embed/|player\.vimeo\.com/video/)%',
        ]);
    }
}
