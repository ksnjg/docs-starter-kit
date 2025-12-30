<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,mp4,webm,mp3,wav',
            ],
            'collection' => [
                'nullable',
                'string',
                'in:default,images,documents',
            ],
            'name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'The file size must not exceed 10MB.',
            'file.mimes' => 'The file must be an image, document, or media file.',
        ];
    }
}
