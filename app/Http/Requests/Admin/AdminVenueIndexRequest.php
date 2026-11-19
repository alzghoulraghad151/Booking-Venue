<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminVenueIndexRequest extends FormRequest
{
    protected $redirectRoute = 'admin.venues.index';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                Rule::in(['pending', 'approved', 'rejected', 'inactive']),
            ],
        ];
    }
}