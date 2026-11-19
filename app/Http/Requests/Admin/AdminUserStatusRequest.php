<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin'
            && $this->user()?->status === 'active';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:active,suspended'],
        ];
    }
}
