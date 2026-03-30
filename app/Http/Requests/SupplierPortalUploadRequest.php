<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierPortalUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Token validation handled in controller
    }

    public function rules(): array
    {
        return [
            'ingredient_id' => 'required|exists:ingredients,id',
            'sh_number' => 'required|string|max:255',
            'issuing_body' => 'required|in:LPH,MUI,FOREIGN_HCB',
            'issuing_body_name' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'required|date|after:today',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'File sertifikat halal wajib diunggah.',
            'document.mimes' => 'File harus berformat PDF, JPG, atau PNG.',
            'document.max' => 'Ukuran file maksimal 10MB.',
            'expiry_date.after' => 'Tanggal kadaluarsa harus di masa depan.',
        ];
    }
}