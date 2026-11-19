<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadTempPerdimDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'max:50'],
            'file' => [
                'required',
                'file',
                'max:2048', // Maksimum 2 MB (2048 KB)
                'mimes:jpg,jpeg,png,pdf',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_type.required' => 'Jenis dokumen wajib dipilih.',
            'document_type.string' => 'Jenis dokumen harus berupa teks.',
            'document_type.max' => 'Jenis dokumen maksimal 50 karakter.',
            'file.required' => 'Berkas dokumen wajib diunggah.',
            'file.file' => 'Berkas yang diunggah harus berupa file yang valid.',
            'file.max' => 'Ukuran berkas dokumen tidak boleh melebihi 2 MB.',
            'file.mimes' => 'Format berkas harus berupa JPG, PNG, atau PDF.',
        ];
    }
}
