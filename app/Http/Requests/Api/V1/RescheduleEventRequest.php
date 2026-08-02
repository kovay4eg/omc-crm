<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'editor', 'content'], true);
    }

    public function rules(): array
    {
        return [
            'new_date' => ['required', 'date', 'after_or_equal:now'],
            'reason' => ['required', 'string', 'max:5000'],
            'reschedule_public' => ['sometimes', 'boolean'],
        ];
    }
}
