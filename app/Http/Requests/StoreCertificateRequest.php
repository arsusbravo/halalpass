<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'ingredient_id.exists' => 'Bahan tidak ditemukan.',
            'issuing_body.in' => 'Lembaga penerbit harus LPH, MUI, atau FOREIGN_HCB.',
            'expiry_date.after' => 'Tanggal kadaluarsa harus di masa depan.',
            'document.mimes' => 'File sertifikat harus berformat PDF, JPG, atau PNG.',
            'document.max' => 'Ukuran file maksimal 10MB.',
        ];
    }
}