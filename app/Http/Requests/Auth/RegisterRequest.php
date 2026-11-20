<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
           'password' => [
    'required',
    'string',
    'confirmed',
    Password::min(8)->mixedCase()->symbols(),
    'max:64',
],
            'phone' => ['bail', 'required', 'string', 'regex:/\A07[789][0-9]{7}\z/'],
        ];
    }

    public function messages(): array
{
    return [
        'phone.regex' => 'Enter a 10-digit phone number starting with 079, 078, or 077.',
    ];
}
}