<?php

namespace App\Http\Controllers;

use App\Models\FeedbackResponse;
use App\Models\GitSync;
use App\Models\Page;
use App\Models\SystemConfig;
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
            'gitSyncStatus' => $this->getGitSyncStatus(),
        ]);
    }

    private function getGitSyncStatus(): ?array
    {
        $config = SystemConfig::instance();

        if (! $config->setup_completed || $config->content_mode !== 'git') {
            return null;
        }

        $lastSync = GitSync::latest()->first();

        return [
            'enabled' => true,
            'lastSync' => $lastSync ? [
                'status' => $lastSync->sync_status,
                'commitHash' => $lastSync->commit_hash ? substr($lastSync->commit_hash, 0, 7) : null,
                'commitMessage' => $lastSync->commit_message,
                'syncedAt' => $lastSync->created_at?->toISOString(),
                'filesChanged' => $lastSync->files_changed,
                'error' => $lastSync->error_message,
            ] : null,
            'lastSyncedAt' => $config->last_synced_at?->toISOString(),
        ];
    }
}
