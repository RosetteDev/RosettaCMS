<?php

namespace RosettaCMS\Plugins\MultiSite;

use RosettaCMS\Models\Site;
use Closure;

class MultiSiteManager
{
    /**
     * The current site.
     *
     * @var Site|null
     */
    protected $currentSite;

    /**
     * Get the current site.
     *
     * @return Site|null
     */
    public function current(): ?Site
    {
        if (!$this->currentSite) {
            $this->currentSite = $this->resolveCurrentSite();
        }
        
        return $this->currentSite;
    }

    /**
     * Resolve the current site based on domain or route.
     *
     * @return Site|null
     */
    protected function resolveCurrentSite(): ?Site
    {
        $domain = request()->getHttpHost();
        
        // Try to find site by domain
        $site = Site::where('domain', $domain)->first();
        
        // If not found, try by slug
        if (!$site && config('app.debug')) {
            $site = Site::where('slug', 'default')->first();
        }
        
        // If still not found, get first active site
        if (!$site) {
            $site = Site::where('is_active', true)->first();
        }
        
        return $site;
    }

    /**
     * Set the current site.
     *
     * @param Site $site
     * @return $this
     */
    public function setCurrent(Site $site): self
    {
        $this->currentSite = $site;
        return $this;
    }

    /**
     * Get all active sites.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Site::where('is_active', true)->get();
    }

    /**
     * Get sites by domain.
     *
     * @param string $domain
     * @return Site|null
     */
    public function findByDomain(string $domain): ?Site
    {
        return Site::where('domain', $domain)->first();
    }

    /**
     * Get site by slug.
     *
     * @param string $slug
     * @return Site|null
     */
    public function findBySlug(string $slug): ?Site
    {
        return Site::where('slug', $slug)->first();
    }

    /**
     * Get site by ID.
     *
     * @param int $id
     * @return Site|null
     */
    public function findById(int $id): ?Site
    {
        return Site::find($id);
    }
}
