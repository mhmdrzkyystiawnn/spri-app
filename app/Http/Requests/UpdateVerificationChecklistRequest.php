<?php

namespace App\Http\Requests;

use App\Models\Verification;
use App\Models\VerificationChecklist;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVerificationChecklistRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki wewenang untuk request ini.
     */
    public function authorize(): bool
    {
        /** @var Verification|null $verification */
        $verification = $this->route('verification');

        if (! $verification) {
            return false;
        }

        $user = $this->user();

        if (! $user || ! $user->is_active) {
            return false;
        }

        return $user->can('updateForVerification', [VerificationChecklist::class, $verification]);
    }

    /**
     * Aturan validasi pembaruan checklist berkas.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.is_checked' => ['nullable', 'boolean'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Daftar kelengkapan dokumen persyaratan wajib dikirimkan.',
            'items.array' => 'Format data checklist tidak valid.',
            'items.*.is_checked.boolean' => 'Nilai status kelengkapan dokumen harus berupa boolean.',
            'items.*.notes.max' => 'Catatan verifikator untuk setiap dokumen maksimal 1000 karakter.',
        ];
    }
}
