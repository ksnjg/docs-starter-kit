<?php

namespace App\Http\Controllers;

use App\Models\FeedbackResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'page_id' => ['required', 'exists:pages,id'],
            'feedback_form_id' => ['nullable', 'exists:feedback_forms,id'],
            'is_helpful' => ['required', 'boolean'],
            'form_data' => ['nullable', 'array'],
        ]);

        FeedbackResponse::create([
            'page_id' => $validated['page_id'],
            'feedback_form_id' => $validated['feedback_form_id'] ?? null,
            'is_helpful' => $validated['is_helpful'],
            'form_data' => $validated['form_data'] ?? [],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Thank you for your feedback!');
    }
}
