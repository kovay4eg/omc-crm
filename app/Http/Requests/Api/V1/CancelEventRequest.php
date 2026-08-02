<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CancelEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'editor', 'content'], true);
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:5000'],
            'cancel_public' => ['sometimes', 'boolean'],
        ];
    }
}
