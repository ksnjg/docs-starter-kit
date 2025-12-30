<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackForm extends Model
{
    protected $fillable = [
        'name',
        'trigger_type',
        'fields',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function responses(): HasMany
    {
        return $this->hasMany(FeedbackResponse::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPositive($query)
    {
        return $query->whereIn('trigger_type', ['positive', 'always']);
    }

    public function scopeForNegative($query)
    {
        return $query->whereIn('trigger_type', ['negative', 'always']);
    }

    public function isForPositive(): bool
    {
        return in_array($this->trigger_type, ['positive', 'always']);
    }

    public function isForNegative(): bool
    {
        return in_array($this->trigger_type, ['negative', 'always']);
    }
}
