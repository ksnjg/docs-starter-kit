<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'feedback_form_id',
        'is_helpful',
        'form_data',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'is_helpful' => 'boolean',
            'form_data' => 'array',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function feedbackForm(): BelongsTo
    {
        return $this->belongsTo(FeedbackForm::class);
    }

    public function scopeHelpful($query)
    {
        return $query->where('is_helpful', true);
    }

    public function scopeNotHelpful($query)
    {
        return $query->where('is_helpful', false);
    }

    public function isHelpful(): bool
    {
        return $this->is_helpful === true;
    }

    public function isNotHelpful(): bool
    {
        return $this->is_helpful === false;
    }
}
