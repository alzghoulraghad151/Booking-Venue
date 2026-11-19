<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'venue_owner'
            && $this->user()?->status === 'active';
    }

    public function rules(): array
    {
        return [
            'venue_id' => [
                'required',
                'integer',
                Rule::exists('venues', 'id')->where('owner_id', $this->user()->getKey()),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
            'hourly_rate' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
