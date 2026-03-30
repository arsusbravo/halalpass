<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:simple,composite',
            'code' => 'nullable|string|max:30',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'origin_country' => 'nullable|string|max:2',
            'brand' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'category' => 'required|in:bahan_baku,bahan_tambahan,bahan_penolong',
            'specifications' => 'nullable|array',
            'notes' => 'nullable|string',

            // Nested children for composite ingredients
            'children' => 'nullable|array',
            'children.*.name' => 'required|string|max:255',
            'children.*.type' => 'nullable|in:simple,composite',
            'children.*.code' => 'nullable|string|max:30',
            'children.*.supplier_id' => 'nullable|exists:suppliers,id',
            'children.*.origin_country' => 'nullable|string|max:2',
            'children.*.brand' => 'nullable|string|max:255',
            'children.*.manufacturer' => 'nullable|string|max:255',
            'children.*.category' => 'nullable|in:bahan_baku,bahan_tambahan,bahan_penolong',
            'children.*.specifications' => 'nullable|array',
            'children.*.notes' => 'nullable|string',
            'halal_risk_level' => 'nullable|in:no_risk,low_risk,medium_risk,high_risk',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Jenis bahan harus simple atau composite.',
            'category.in' => 'Kategori harus bahan_baku, bahan_tambahan, atau bahan_penolong.',
            'supplier_id.exists' => 'Pemasok tidak ditemukan.',
        ];
    }
}