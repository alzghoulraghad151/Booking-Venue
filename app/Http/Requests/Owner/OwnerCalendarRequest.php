<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OwnerCalendarRequest extends FormRequest
{
    protected $redirectRoute = 'owner.calendar.index';

    public function authorize(): bool
    {
        return $this->user()?->role === 'venue_owner' && $this->user()->status === 'active';
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2099-12-31'],
            'view' => ['nullable', Rule::in(['day', 'week', 'month'])],
            'space_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
