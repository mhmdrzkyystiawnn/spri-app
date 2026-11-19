<?php

namespace App\Http\Requests;

use App\Enums\VerificationStatus;
use App\Models\Verification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Verification::class) ?? false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('registration_code')) {
            $this->merge([
                'registration_code' => strtoupper(trim((string) $this->registration_code)),
            ]);
        }
        if ($this->has('applicant_name')) {
            $this->merge([
                'applicant_name' => trim((string) $this->applicant_name),
            ]);
        }
        if ($this->has('document_type')) {
            $this->merge([
                'document_type' => trim((string) $this->document_type),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registration_code' => [
                'required',
                'string',
                'alpha_dash',
                'max:32',
                'unique:verifications,registration_code',
            ],
            'applicant_name' => ['required', 'string', 'max:150'],
            'document_type' => ['required', 'string', 'max:80'],
            'status' => ['required', new Enum(VerificationStatus::class)],
            'public_notes' => [
                Rule::requiredIf(fn () => in_array($this->status, [
                    VerificationStatus::NEEDS_REVISION->value,
                    VerificationStatus::REJECTED->value,
                ])),
                'nullable',
                'string',
                'max:1000',
            ],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Pesan validasi Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'registration_code.required' => 'Nomor registrasi wajib diisi.',
            'registration_code.unique' => 'Nomor registrasi ini telah terdaftar sebelumnya.',
            'registration_code.alpha_dash' => 'Nomor registrasi hanya boleh memuat huruf, angka, tanda strip, dan garis bawah.',
            'registration_code.max' => 'Nomor registrasi tidak boleh melebihi 32 karakter.',
            'applicant_name.required' => 'Nama lengkap pemohon wajib diisi.',
            'applicant_name.max' => 'Nama pemohon tidak boleh lebih dari 150 karakter.',
            'document_type.required' => 'Jenis permohonan SPRI wajib dipilih.',
            'status.required' => 'Status verifikasi wajib ditentukan.',
            'public_notes.required' => 'Catatan publik wajib diisi jika status Perlu Perbaikan atau Ditolak agar pemohon memahami alasan keputusan.',
            'public_notes.max' => 'Catatan publik maksimal 1000 karakter.',
            'admin_notes.max' => 'Catatan internal admin maksimal 2000 karakter.',
        ];
    }
}
