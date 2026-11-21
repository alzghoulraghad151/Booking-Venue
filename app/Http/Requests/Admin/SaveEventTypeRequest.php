<?php

namespace App\Http\Requests\Admin;

use App\Models\EventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEventTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin' && $this->user()->status === 'active';
    }

    public function rules(): array
    {
        $uniqueName = Rule::unique('event_types', 'name');
        $eventType = $this->route('eventType');

        if ($eventType instanceof EventType) {
            $uniqueName->ignore($eventType);
        }

        return [
            'name' => ['required', 'string', 'max:100', Rule::in(EventType::APPROVED_NAMES), $uniqueName],
            'description' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return ['name.in' => 'Choose one of the five approved event types.'];
    }
}
