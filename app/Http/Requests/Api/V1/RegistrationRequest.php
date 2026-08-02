<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'editor', 'content'], true);
    }

    public function rules(): array
    {
        $creating = $this->isMethod('post');
        $required = $creating ? ['required'] : ['sometimes'];
        $event = $this->route('event');
        $registration = $this->route('registration');

        return [
            'name' => [...$required, 'string', 'max:255'],
            'email' => [
                ...$required,
                'email',
                'max:255',
                Rule::unique('registrations')->where('event_id', $event->id)->ignore($registration?->id),
            ],
            'phone' => [
                ...$required,
                'string',
                'max:30',
                Rule::unique('registrations')->where('event_id', $event->id)->ignore($registration?->id),
            ],
        ];
    }
}
