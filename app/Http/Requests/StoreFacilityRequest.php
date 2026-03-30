<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'pic_name' => 'nullable|string|max:255',
            'production_capacity' => 'nullable|string',
            'sjph_status' => 'nullable|in:not_started,in_progress,completed,approved',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}