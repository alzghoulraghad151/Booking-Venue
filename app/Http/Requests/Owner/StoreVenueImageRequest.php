<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenueImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('venue'));
    }

    public function rules(): array
    {
         return [
        'images' => ['required', 'array', 'min:1', 'max:3'],
        'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ];
    }
}
