<?php

namespace App\Http\Requests\Owner;

use App\Models\Space;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $space = $this->route('space');

        return $space instanceof Space
            && $this->user()?->role === 'venue_owner'
            && $this->user()?->status === 'active'
            && $space->venue()->where('owner_id', $this->user()->getKey())->exists();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
            'hourly_rate' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
