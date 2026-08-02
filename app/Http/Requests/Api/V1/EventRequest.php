<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
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
        $registrationEnabled = $this->has('has_registration_button')
            ? $this->boolean('has_registration_button')
            : (bool) $event?->has_registration_button;
        $registrationType = $this->input('registration_type', $event?->registration_type);

        $eventDateRules = [...$required, 'date'];

        if (! $this->user()?->isAdmin()) {
            $eventDateRules[] = $creating ? 'after_or_equal:now' : 'prohibited';
        }

        return [
            'title' => [...$required, 'string', 'max:255'],
            'description' => [...$required, 'string'],
            'event_date' => $eventDateRules,
            'notify_email' => ['nullable', 'email', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_image' => ['sometimes', 'boolean'],
            'smm_title' => ['nullable', 'string', 'max:255'],
            'smm_description' => ['nullable', 'string', 'max:200'],
            'smm_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_smm_image' => ['sometimes', 'boolean'],
            'has_registration_button' => [...$required, 'boolean'],
            'registration_type' => [
                Rule::requiredIf($registrationEnabled),
                'nullable',
                Rule::in(['none', 'internal', 'external']),
            ],
            'google_form_url' => [
                Rule::requiredIf($registrationEnabled && $registrationType === 'external'),
                'nullable',
                'url:http,https',
                'max:2048',
            ],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'show_available_slots' => ['sometimes', 'boolean'],
            'status' => [...$required, Rule::in(['draft', 'published'])],
        ];
    }
}
