<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['navigation', 'group', 'document'])],
            'icon' => ['nullable', 'string', 'max:50'],
            'content' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'parent_id' => ['nullable', 'exists:pages,id'],
            'is_default' => ['boolean'],
            'is_expanded' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The page title is required.',
            'slug.unique' => 'This slug is already in use.',
            'status.in' => 'The status must be draft, published, or archived.',
        ];
    }
}
