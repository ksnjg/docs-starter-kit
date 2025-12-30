<?php

namespace App\Http\Controllers;

use App\Models\FeedbackResponse;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'totalPages' => Page::count(),
            'publishedPages' => Page::where('status', 'published')->count(),
            'draftPages' => Page::where('status', 'draft')->count(),
            'totalFeedback' => FeedbackResponse::count(),
            'positiveFeedback' => FeedbackResponse::where('is_helpful', true)->count(),
        ];

        $recentPages = Page::query()
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'type', 'status', 'updated_at']);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentPages' => $recentPages,
        ]);
    }
}
