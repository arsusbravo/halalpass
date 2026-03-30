<?php

namespace App\Http\Requests;

use App\Models\SjphDocument;
use Illuminate\Foundation\Http\FormRequest;

class SaveSjphSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'criterion' => 'required|string|in:' . implode(',', SjphDocument::CRITERIA),
            'data' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'criterion.in' => 'Kriteria SJPH tidak valid. Harus salah satu dari: ' . implode(', ', SjphDocument::CRITERIA),
            'data.required' => 'Data kriteria tidak boleh kosong.',
        ];
    }
}