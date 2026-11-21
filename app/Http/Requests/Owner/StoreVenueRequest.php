<?php

namespace App\Http\Requests\Owner;

use App\Models\Venue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255', Rule::in(Venue::addressOptions())],
            'description' => ['nullable', 'string', 'max:5000'],
            'images' => ['required', 'array', 'list', 'min:1', 'max:'.Venue::IMAGE_LIMIT],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'primary_image' => ['nullable', 'string', 'max:30'],
        ];
    }
}
