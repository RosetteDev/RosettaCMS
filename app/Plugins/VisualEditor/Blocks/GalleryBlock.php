<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class GalleryBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'gallery';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Gallery';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-images';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'images' => [],
        'columns' => 3,
        'gap' => 10,
        'link' => 'none', // none, file, attachment
        'size' => 'medium',
        'rounded' => false,
        'layout' => 'grid', // grid, carousel
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.gallery';
    }

    /**
     * Normalize the images attribute into a list of ['url', 'alt', 'title'].
     *
     * Accepts plain URL strings (the editor's one-per-line format) and
     * associative entries (['url' => ..., 'alt' => ..., 'title' => ...]).
     *
     * @param array $images
     * @return array
     */
    protected function normalizeImages(array $images): array
    {
        $normalized = [];

        foreach ($images as $image) {
            if (is_string($image)) {
                $url = trim($image);
                if ($url === '') {
                    continue;
                }
                $normalized[] = ['url' => $url, 'alt' => $this->altFromUrl($url), 'title' => ''];
                continue;
            }

            if (is_array($image)) {
                $url = trim((string) ($image['url'] ?? ''));
                if ($url === '') {
                    continue;
                }
                $normalized[] = [
                    'url' => $url,
                    'alt' => (string) ($image['alt'] ?? $this->altFromUrl($url)),
                    'title' => (string) ($image['title'] ?? ''),
                ];
            }
        }

        return $normalized;
    }

    /**
     * Derive a fallback alt text from the URL.
     */
    protected function altFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $name = pathinfo($path, PATHINFO_FILENAME) ?: 'image';
        $name = str_replace(['_', '-', '.'], ' ', $name);
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? '');

        return ucwords(strtolower($name)) ?: 'image';
    }

    /**
     * Render the block.
     *
     * @return string
     */
    public function render(): string
    {
        $images = $this->normalizeImages((array) $this->getAttribute('images', []));
        $columns = (int) $this->getAttribute('columns', 3);
        $gap = (int) $this->getAttribute('gap', 10);
        $link = $this->getAttribute('link', 'none');
        $rounded = $this->getAttribute('rounded', false);
        $layout = $this->getAttribute('layout', 'grid');

        if (empty($images)) {
            return '<div class="text-muted text-center py-3"><i class="fas fa-images me-1"></i>No images in gallery</div>';
        }

        $roundedClass = $rounded ? ' rounded' : '';

        if ($layout === 'carousel') {
            return $this->renderCarousel($images, $gap, $rounded, $link);
        }

        $columnsClass = match ($columns) {
            1 => 'col-12',
            2 => 'col-6 col-md-6',
            3 => 'col-6 col-md-4',
            4 => 'col-6 col-md-3',
            5 => 'col-4 col-md-2',
            6 => 'col-4 col-md-2',
            default => 'col-6',
        };

        $html = '<div class="gallery gallery-columns-'.$columns.' gallery-gap-'.$gap.' mb-3">';
        $html .= '<div class="row g-'.$gap.'">';

        foreach ($images as $image) {
            $imgUrl = $image['url'];
            $imgAlt = $image['alt'];
            $imgTitle = $image['title'];

            $href = $link === 'file' ? $imgUrl : '#';

            $html .= sprintf(
                '<div class="%s"><a href="%s" class="d-block"><img src="%s" alt="%s" title="%s" loading="lazy" decoding="async" class="img-fluid%s"></a></div>',
                $columnsClass,
                e($href),
                e($imgUrl),
                e($imgAlt),
                e($imgTitle),
                $roundedClass
            );
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Render a horizontal, keyboard-accessible scroll-snap carousel.
     */
    protected function renderCarousel(array $images, int $gap, bool $rounded, string $link): string
    {
        $roundedClass = $rounded ? ' rounded' : '';
        $gapPx = $gap; // editor gap (0-40) maps directly to px

        $uid = 'gc-' . bin2hex(random_bytes(6));
        $html = '<div class="gallery-carousel mb-3" data-gallery-carousel id="' . $uid . '">';
        $html .= '<button type="button" class="gallery-carousel-prev" aria-label="Previous images"><i class="fas fa-chevron-left"></i></button>';
        $html .= '<div class="gallery-carousel-track" style="gap:' . (int) $gapPx . 'px;" tabindex="0" role="list" aria-label="Image carousel">';

        foreach ($images as $i => $image) {
            $imgUrl = $image['url'];
            $imgAlt = $image['alt'];
            $imgTitle = $image['title'];
            $href = $link === 'file' ? $imgUrl : '#';

            $html .= sprintf(
                '<div class="gallery-carousel-item" role="listitem"><a href="%s" class="d-block" aria-label="%s"><img src="%s" alt="%s" title="%s" loading="lazy" decoding="async" class="img-fluid%s"></a></div>',
                e($href),
                e($imgAlt),
                e($imgUrl),
                e($imgAlt),
                e($imgTitle),
                $roundedClass
            );
        }

        $html .= '</div>';
        $html .= '<button type="button" class="gallery-carousel-next" aria-label="Next images"><i class="fas fa-chevron-right"></i></button>';
        $html .= '</div>';

        $html .= '<style>
.gallery-carousel{position:relative;max-width:100%;}
.gallery-carousel-track{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;padding:4px 8px;scrollbar-width:thin;}
.gallery-carousel-track:focus-visible{outline:2px solid #3b82f6;outline-offset:2px;border-radius:.5rem;}
.gallery-carousel-item{flex:0 0 42%;max-width:60%;scroll-snap-align:start;}
@media(min-width:768px){.gallery-carousel-item{flex:0 0 30%;max-width:34%;}}
.gallery-carousel-item img{height:220px;object-fit:cover;border-radius:.5rem;width:100%;}
.gallery-carousel-prev,.gallery-carousel-next{position:absolute;top:50%;transform:translateY(-50%);z-index:2;background:rgba(0,0,0,.55);color:#fff;border:none;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s;}
.gallery-carousel-prev:hover,.gallery-carousel-next:hover{background:rgba(0,0,0,.8);}
.gallery-carousel-prev{left:-4px;}
.gallery-carousel-next{right:-4px;}
@media(max-width:640px){.gallery-carousel-item{flex:0 0 70%;max-width:70%;}.gallery-carousel-prev{left:0;}.gallery-carousel-next{right:0;}}
</style>';

        $html .= '<script>
(function(){
  var root = document.getElementById("' . $uid . '");
  if (!root) return;
  var track = root.querySelector(".gallery-carousel-track");
  root.querySelector(".gallery-carousel-prev").addEventListener("click", function(){ track.scrollBy({left: -track.clientWidth * 0.8, behavior: "smooth"}); });
  root.querySelector(".gallery-carousel-next").addEventListener("click", function(){ track.scrollBy({left: track.clientWidth * 0.8, behavior: "smooth"}); });
})();
</script>';

        return $html;
    }
}
