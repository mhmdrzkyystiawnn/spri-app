<?php

namespace App\Http\Requests;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Admin\VerificationController;
use App\Models\Verification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerificationSearchRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki wewenang untuk request pencarian ini.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Verification::class) ?? false;
    }

    /**
     * Persiapkan dan bersihkan parameter sebelum validasi.
     * Melakukan trim pada string dan mengubah string kosong menjadi null.
     */
    protected function prepareForValidation(): void
    {
        $fields = ['search', 'status', 'document_type', 'date_from', 'date_to', 'completeness'];
        $normalized = [];

        foreach ($fields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                if (is_string($value)) {
                    $trimmed = trim($value);
                    $normalized[$field] = $trimmed === '' ? null : $trimmed;
                } else {
                    $normalized[$field] = $value;
                }
            }
        }

        $this->merge($normalized);
    }

    /**
     * Aturan validasi filter pencarian berkas verifikasi.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(VerificationStatus::values())],
            'document_type' => ['nullable', 'string', Rule::in(VerificationController::DOCUMENT_TYPES)],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'completeness' => ['nullable', 'string', Rule::in(['complete', 'incomplete'])],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'search.string' => 'Parameter pencarian harus berupa teks.',
            'search.max' => 'Kata kunci pencarian tidak boleh melebihi 100 karakter.',
            'status.in' => 'Status verifikasi yang dipilih tidak valid.',
            'document_type.in' => 'Jenis permohonan SPRI yang dipilih tidak valid.',
            'date_from.date_format' => 'Format tanggal awal harus YYYY-MM-DD.',
            'date_to.date_format' => 'Format tanggal akhir harus YYYY-MM-DD.',
            'date_to.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
            'completeness.in' => 'Filter kelengkapan berkas harus bernilai complete atau incomplete.',
        ];
    }
}
