<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'facility_id' => 'required|exists:facilities,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:30',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,discontinued',

            // Ingredients with pivot data
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.percentage' => 'nullable|numeric|min:0|max:100',
            'ingredients.*.is_critical' => 'nullable|boolean',
            'ingredients.*.usage_purpose' => 'nullable|string|max:255',
            'ingredients.*.sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'facility_id.exists' => 'Fasilitas produksi tidak ditemukan.',
            'ingredients.*.ingredient_id.exists' => 'Salah satu bahan tidak ditemukan.',
        ];
    }
}