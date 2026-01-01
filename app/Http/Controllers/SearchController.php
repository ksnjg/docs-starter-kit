<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'results' => [],
                'query' => $query,
            ]);
        }

        $results = Page::search($query)
            ->query(fn ($builder) => $builder->published()->documents())
            ->take(10)
            ->get()
            ->map(fn (Page $page) => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'path' => $page->getFullPath(),
                'excerpt' => $this->getExcerpt($page->content, $query),
                'type' => $page->type,
            ]);

        return response()->json([
            'results' => $results,
            'query' => $query,
        ]);
    }

    private function getExcerpt(?string $content, string $query): string
    {
        if (empty($content)) {
            return '';
        }

        $plainContent = strip_tags($content);
        $position = stripos($plainContent, $query);

        if ($position === false) {
            return e(\Illuminate\Support\Str::limit($plainContent, 150));
        }

        $start = max(0, $position - 50);
        $excerpt = substr($plainContent, $start, 200);

        if ($start > 0) {
            $excerpt = '...'.$excerpt;
        }

        if (strlen($plainContent) > $start + 200) {
            $excerpt .= '...';
        }

        $excerpt = trim($excerpt);

        $excerpt = preg_replace(
            '/('.preg_quote($query, '/').')/i',
            '<mark class="bg-yellow-200 dark:bg-yellow-800 rounded px-0.5">$1</mark>',
            e($excerpt)
        );

        return $excerpt;
    }
}
