<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if (! empty(config('turnstile.secret_key'))) {
            return array_merge(parent::rules(), [
                'turnstile_token' => ['required', 'string', 'turnstile'],
            ]);
        }

        return parent::rules();
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        if (! empty(config('turnstile.secret_key'))) {
            return array_merge(parent::messages(), [
                'turnstile_token.required' => 'El token de captcha es obligatorio.',
            ]);
        }

        return parent::messages();
    }
}
