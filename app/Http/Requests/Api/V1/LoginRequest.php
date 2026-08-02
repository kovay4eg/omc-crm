<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:120'],
            'platform' => ['nullable', 'string', 'max:50'],
            'device_model' => ['nullable', 'string', 'max:120'],
            'os_version' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'two_factor_code' => ['nullable', 'string', 'min:6', 'max:32'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Вкажіть електронну адресу.',
            'email.email' => 'Вкажіть коректну електронну адресу.',
            'password.required' => 'Вкажіть пароль.',
            'device_name.required' => 'Не вдалося визначити пристрій.',
        ];
    }
}
