<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminUserIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],

            'role' => ['nullable', 'in:organizer,venue_owner,admin'],

            'status' => ['nullable', 'in:active,suspended'],

            'sort' => ['nullable', 'in:name,email,role,status,created_at'],

            'direction' => ['nullable', 'in:asc,desc'],

            'per_page' => ['nullable', 'in:5,10,20,50'],
        ];
    }
}
