<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOwnerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the 'auth' and 'owner' middleware already protect the route
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            // Same format as registration: 079 / 078 / 077 followed by 7 digits.
            'phone' => ['required', 'string', 'regex:/\A07[789][0-9]{7}\z/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a 10-digit phone number starting with 079, 078, or 077.',
        ];
    }
}
