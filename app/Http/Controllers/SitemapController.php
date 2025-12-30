<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = $this->getUrls();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= $this->buildUrlEntry($url);
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * Get all URLs for the sitemap.
     *
     * @return array<int, array{loc: string, lastmod?: string, changefreq?: string, priority?: string}>
     */
    protected function getUrls(): array
    {
        return [
            [
                'loc' => url('/'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            // Add more URLs here as needed:
            // [
            //     'loc' => url('/about'),
            //     'lastmod' => '2025-01-01',
            //     'changefreq' => 'monthly',
            //     'priority' => '0.8',
            // ],
        ];
    }

    /**
     * Build a single URL entry for the sitemap.
     *
     * @param  array{loc: string, lastmod?: string, changefreq?: string, priority?: string}  $url
     */
    protected function buildUrlEntry(array $url): string
    {
        $entry = '<url>';
        $entry .= '<loc>'.htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8').'</loc>';

        if (isset($url['lastmod'])) {
            $entry .= '<lastmod>'.$url['lastmod'].'</lastmod>';
        }

        if (isset($url['changefreq'])) {
            $entry .= '<changefreq>'.$url['changefreq'].'</changefreq>';
        }

        if (isset($url['priority'])) {
            $entry .= '<priority>'.$url['priority'].'</priority>';
        }

        $entry .= '</url>';

        return $entry;
    }
}
