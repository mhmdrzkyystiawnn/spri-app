<?php

namespace App\Http\Requests;

use App\Http\Controllers\Admin\VerificationController;
use App\Models\DocumentRequirement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequirementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var DocumentRequirement|null $requirement */
        $requirement = $this->route('document_requirement');

        return $requirement && ($this->user()?->can('update', $requirement) ?? false);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('document_type')) {
            $this->merge([
                'document_type' => trim((string) $this->document_type),
            ]);
        }
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->code)),
            ]);
        }
        if ($this->has('name')) {
            $this->merge([
                'name' => trim((string) $this->name),
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
        $requirement = $this->route('document_requirement');

        return [
            'document_type' => [
                'required',
                'string',
                Rule::in(VerificationController::DOCUMENT_TYPES),
            ],
            'code' => [
                'required',
                'string',
                'alpha_dash',
                'max:50',
                Rule::unique('document_requirements', 'code')
                    ->where('document_type', $this->document_type)
                    ->ignore($requirement),
            ],
            'name' => ['required', 'string', 'max:150'],
            'is_mandatory' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'document_type.required' => 'Jenis permohonan SPRI wajib dipilih.',
            'document_type.in' => 'Jenis permohonan SPRI tidak terdaftar dalam sistem.',
            'code.required' => 'Kode persyaratan wajib diisi.',
            'code.alpha_dash' => 'Kode persyaratan hanya boleh memuat huruf, angka, strip, dan garis bawah.',
            'code.max' => 'Kode persyaratan maksimal 50 karakter.',
            'code.unique' => 'Kode persyaratan ini sudah terdaftar untuk jenis permohonan yang dipilih.',
            'name.required' => 'Nama persyaratan dokumen wajib diisi.',
            'name.max' => 'Nama persyaratan dokumen maksimal 150 karakter.',
            'sort_order.integer' => 'Nomor urut harus berupa angka bulat.',
            'sort_order.min' => 'Nomor urut minimal 0.',
            'sort_order.max' => 'Nomor urut maksimal 9999.',
        ];
    }
}
