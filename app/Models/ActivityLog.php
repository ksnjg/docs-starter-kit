<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'route_name',
        'url',
        'method',
        'ip_address',
        'real_ip',
        'user_agent',
        'ip_info',
        'request_data',
        'response_data',
        'status_code',
        'execution_time',
        'controller',
        'controller_action',
        'description',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ip_info' => 'array',
            'request_data' => 'array',
            'response_data' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include logs for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include logs for a specific action.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to only include logs for a specific route.
     */
    public function scopeForRoute($query, string $routeName)
    {
        return $query->where('route_name', $routeName);
    }

    /**
     * Scope a query to only include logs within a date range.
     */
    public function scopeInDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include logs with successful responses.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status_code', '>=', 200)->where('status_code', '<', 300);
    }

    /**
     * Scope a query to only include logs with error responses.
     */
    public function scopeWithErrors($query)
    {
        return $query->where('status_code', '>=', 400);
    }

    /**
     * Scope a query to only include logs with redirects responses.
     */
    public function scopeRedirects($query)
    {
        return $query->where('status_code', '>=', 300)->where('status_code', '<', 400);
    }
}
