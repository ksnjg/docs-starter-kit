<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Display the main activity logs page.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'user_id',
            'action',
            'route_name',
            'start_date',
            'end_date',
            'successful',
            'with_errors',
            'page',
            'per_page',
        ]);

        $query = $this->activityLogService->getLogs($filters);

        $logs = $query->with('user')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        $stats = $this->getStats();
        $filterOptions = $this->getFilterOptions();

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $logs,
            'stats' => $stats,
            'filterOptions' => $filterOptions,
            'filters' => $filters,
        ]);
    }

    /**
     * Display details of a specific log.
     */
    public function show(int $id): Response
    {
        $log = $this->activityLogService->getLogs(['id' => $id])->firstOrFail();

        return Inertia::render('ActivityLogs/Show', [
            'log' => $log,
        ]);
    }

    /**
     * Export logs to CSV.
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'user_id',
            'action',
            'route_name',
            'start_date',
            'end_date',
            'successful',
            'with_errors',
        ]);

        $query = $this->activityLogService->getLogs($filters);
        $logs = $query->with('user')->get();

        $filename = 'activity_logs_'.now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID',
                'User',
                'Action',
                'Route',
                'URL',
                'Method',
                'IP',
                'Status Code',
                'Execution Time (ms)',
                'Controller',
                'Description',
                'Date',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? 'Anonymous',
                    $log->action,
                    $log->route_name ?? 'N/A',
                    $log->url,
                    $log->method,
                    $log->ip_address,
                    $log->status_code ?? 'N/A',
                    $log->execution_time ?? 'N/A',
                    $log->controller ?? 'N/A',
                    $log->description,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clean old logs.
     */
    public function clean(Request $request)
    {
        $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $deletedCount = $this->activityLogService->cleanOldLogs($request->days);

        return redirect()->back()->with('success', "{$deletedCount} old logs have been deleted.");
    }

    /**
     * Get log statistics.
     */
    private function getStats(): array
    {
        $totalLogs = $this->activityLogService->getLogs()->count();
        $todayLogs = $this->activityLogService->getLogs([
            'start_date' => now()->startOfDay()->toDateTimeString(),
            'end_date' => now()->endOfDay()->toDateTimeString(),
        ])->count();

        $errorLogs = $this->activityLogService->getLogs(['with_errors' => true])->count();
        $successLogs = $this->activityLogService->getLogs(['successful' => true])->count();
        $redirectsLogs = $this->activityLogService->getLogs(['redirects' => true])->count();

        return [
            'total' => $totalLogs,
            'today' => $todayLogs,
            'errors' => $errorLogs,
            'success' => $successLogs,
            'redirects' => $redirectsLogs,
        ];
    }

    /**
     * Get filter options.
     */
    private function getFilterOptions(): array
    {
        $actions = ActivityLog::query()
            ->select('action')
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->values();

        $routes = ActivityLog::query()
            ->select('route_name')
            ->whereNotNull('route_name')
            ->distinct()
            ->orderBy('route_name')
            ->pluck('route_name')
            ->values();

        $users = ActivityLog::query()
            ->select('user_id')
            ->whereNotNull('user_id')
            ->with('user:id,name')
            ->get()
            ->pluck('user.name', 'user.id')
            ->filter()
            ->sort()
            ->unique();

        return [
            'actions' => $actions,
            'routes' => $routes,
            'users' => $users,
        ];
    }
}
