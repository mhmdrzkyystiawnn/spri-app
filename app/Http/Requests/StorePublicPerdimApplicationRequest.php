<?php

namespace App\Http\Requests;

use App\Rules\NikRule;
use App\Services\PerdimDocumentClassificationService;
use App\Services\PerdimDocumentStorageService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePublicPerdimApplicationRequest extends FormRequest
{
    /**
     * Tentukan otorisasi permintaan (publik dapat mengakses).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sanitasi dan normalisasi payload sebelum proses validasi.
     */
    protected function prepareForValidation(): void
    {
        // Bersihkan dan buang parameter terproteksi yang mungkin diselundupkan
        $prohibitedParams = [
            'registration_code',
            'status',
            'admin_notes',
            'created_by',
            'verification_id',
            'verified_at',
            'declaration_accepted_at',
        ];

        foreach ($prohibitedParams as $param) {
            $this->request->remove($param);
            $this->query->remove($param);
        }

        $sanitized = [];

        // Normalisasi trim untuk semua string input umum
        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim($value);
            }
        }

        // Normalisasi huruf kapital pada enum / kode
        if (isset($sanitized['gender'])) {
            $sanitized['gender'] = strtoupper($sanitized['gender']);
        }
        if (isset($sanitized['mother_nationality'])) {
            $sanitized['mother_nationality'] = strtoupper($sanitized['mother_nationality']);
        }
        if (isset($sanitized['father_nationality'])) {
            $sanitized['father_nationality'] = strtoupper($sanitized['father_nationality']);
        }
        if (isset($sanitized['spouse_nationality'])) {
            $sanitized['spouse_nationality'] = strtoupper($sanitized['spouse_nationality']);
        }
        if (isset($sanitized['application_type'])) {
            $sanitized['application_type'] = strtolower($sanitized['application_type']);
        }
        if (isset($sanitized['passport_type'])) {
            $sanitized['passport_type'] = strtolower($sanitized['passport_type']);
        }
        if (isset($sanitized['replacement_reason'])) {
            $sanitized['replacement_reason'] = strtolower($sanitized['replacement_reason']);
        }
        if (isset($sanitized['change_reason'])) {
            $sanitized['change_reason'] = strtolower($sanitized['change_reason']);
        }
        if (isset($sanitized['marital_status'])) {
            $status = strtolower($sanitized['marital_status']);
            if ($status === 'belum_kawin') {
                $status = 'tidak_kawin';
            }
            $sanitized['marital_status'] = $status;
        }

        // Normalisasi email ke lowercase
        if (isset($sanitized['email'])) {
            $sanitized['email'] = strtolower($sanitized['email']);
        }

        // Normalisasi format nomor telepon (menghilangkan spasi internal berlebih)
        $phoneFields = [
            'indonesia_phone',
            'office_phone',
            'overseas_phone',
            'father_phone',
            'emergency_id_phone',
            'emergency_overseas_phone',
        ];

        foreach ($phoneFields as $phoneField) {
            if (! empty($sanitized[$phoneField])) {
                $sanitized[$phoneField] = preg_replace('/\s+/', ' ', $sanitized[$phoneField]);
            }
        }

        $this->merge($sanitized);
    }

    /**
     * Aturan validasi menyeluruh untuk 44 field dari 4 Seksi PERDIM 14.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Seksi 1: Permohonan & SPRI
            'application_type' => ['required', 'string', Rule::in(['baru', 'penggantian', 'perubahan'])],
            'passport_type' => [
                'required',
                'string',
                Rule::in([
                    'paspor_biasa_48',
                    'paspor_elektronik_48',
                    'paspor_24',
                    'splp',
                    'elektronik_lembar_polikarbonat',
                    'paspor_elektronik_polikarbonat',
                ]),
            ],
            'replacement_reason' => [
                'nullable',
                'string',
                'required_if:application_type,penggantian',
                Rule::in(['habis_masa_berlaku', 'halaman_penuh', 'hilang', 'rusak', 'lainnya']),
            ],
            'change_reason' => [
                'nullable',
                'string',
                'required_if:application_type,perubahan',
                'max:80',
            ],

            // Seksi 2: Identitas Diri Pemohon
            'full_name' => ['required', 'string', 'max:255'],
            'alias_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'string', Rule::in(['L', 'P'])],
            'height_cm' => ['required', 'integer', 'min:30', 'max:250'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'date_format:Y-m-d', 'before:today'],
            'nik' => ['nullable', 'string', new NikRule, 'required_without:identity_number'],
            'identity_number' => ['nullable', 'string', 'max:50', 'required_without:nik'],
            'identity_issued_place' => ['required', 'string', 'max:100'],
            'identity_issued_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:birth_date',
                'before_or_equal:today',
            ],
            'identity_expiry_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:identity_issued_date'],
            'occupation' => ['required', 'string', 'max:100'],
            'workplace_address' => ['nullable', 'string', 'max:500'],
            'office_phone' => ['nullable', 'string', 'max:30'],
            'indonesia_address' => ['required', 'string', 'min:10', 'max:500'],
            'indonesia_phone' => ['required', 'string', 'regex:/^(\+628|628|08)[0-9]{8,13}$/'],
            'overseas_address' => ['nullable', 'string', 'max:500'],
            'overseas_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'string', 'email:rfc,filter', 'max:150'],
            'marital_status' => ['required', 'string', Rule::in(['kawin', 'tidak_kawin', 'belum_kawin', 'cerai_mati', 'cerai_hidup'])],

            // Seksi 3: Data Keluarga
            'mother_name' => ['required', 'string', 'max:150'],
            'mother_nationality' => ['required', 'string', 'max:50'],
            'mother_birth_place' => ['required', 'string', 'max:100'],
            'mother_birth_date' => ['required', 'date', 'date_format:Y-m-d'],
            'father_name' => ['required', 'string', 'max:150'],
            'father_nationality' => ['required', 'string', 'max:50'],
            'father_birth_place' => ['required', 'string', 'max:100'],
            'father_birth_date' => ['required', 'date', 'date_format:Y-m-d'],
            'father_address' => ['required', 'string', 'max:500'],
            'father_phone' => ['required', 'string', 'max:30'],
            'spouse_name' => ['nullable', 'string', 'max:150', 'required_if:marital_status,kawin'],
            'spouse_nationality' => ['nullable', 'string', 'max:50', 'required_if:marital_status,kawin'],
            'spouse_birth_place' => ['nullable', 'string', 'max:100'],
            'spouse_birth_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'spouse_address' => ['nullable', 'string', 'max:500'],

            // Seksi 4: Perubahan Data, Kontak Darurat & Pernyataan
            'change_details' => ['nullable'],
            'emergency_id_name' => ['required', 'string', 'max:150'],
            'emergency_id_relationship' => ['required', 'string', 'max:50'],
            'emergency_id_address' => ['required', 'string', 'max:500'],
            'emergency_id_phone' => ['required', 'string', 'max:30'],
            'emergency_overseas_name' => ['nullable', 'string', 'max:150'],
            'emergency_overseas_address' => ['nullable', 'string', 'max:500'],
            'emergency_overseas_phone' => ['nullable', 'string', 'max:30'],
            'declaration_accepted' => ['required', 'accepted'],

            // Dokumen Unggahan Persyaratan (Staged Upload)
            'document_tokens' => ['nullable', 'array'],
            'document_tokens.*' => ['string'],
            'document_types' => ['nullable', 'array'],
            'document_types.*' => ['string'],
        ];
    }

    /**
     * Pesan validasi Bahasa Indonesia yang ramah bagi pemohon publik.
     */
    public function messages(): array
    {
        return [
            // Seksi 1
            'application_type.required' => 'Jenis permohonan wajib dipilih.',
            'application_type.in' => 'Pilihan jenis permohonan tidak valid.',
            'passport_type.required' => 'Jenis paspor yang diminta wajib dipilih.',
            'passport_type.in' => 'Pilihan jenis paspor tidak valid.',
            'replacement_reason.required_if' => 'Alasan penggantian paspor wajib dipilih untuk jenis permohonan Penggantian.',
            'replacement_reason.in' => 'Alasan penggantian paspor tidak valid.',
            'change_reason.required_if' => 'Alasan perubahan data paspor wajib diisi untuk jenis permohonan Perubahan.',

            // Seksi 2
            'full_name.required' => 'Nama lengkap pemohon sesuai KTP/identitas wajib diisi.',
            'full_name.max' => 'Nama lengkap maksimal 255 karakter.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
            'height_cm.required' => 'Tinggi badan wajib diisi.',
            'height_cm.integer' => 'Tinggi badan harus berupa angka.',
            'height_cm.min' => 'Tinggi badan minimal 30 cm.',
            'height_cm.max' => 'Tinggi badan maksimal 250 cm.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.date_format' => 'Format tanggal lahir harus YYYY-MM-DD.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'nik.required_without' => 'Salah satu dari NIK atau Nomor Identitas Lainnya wajib diisi.',
            'identity_number.required_without' => 'Salah satu dari Nomor Identitas Lainnya atau NIK wajib diisi.',
            'identity_issued_place.required' => 'Tempat penerbitan identitas wajib diisi.',
            'identity_issued_date.required' => 'Tanggal penerbitan identitas wajib diisi.',
            'identity_issued_date.after_or_equal' => 'Tanggal penerbitan identitas tidak boleh sebelum tanggal lahir pemohon.',
            'identity_issued_date.before_or_equal' => 'Tanggal penerbitan identitas tidak boleh di masa depan.',
            'identity_expiry_date.after_or_equal' => 'Masa berlaku identitas harus sama atau setelah tanggal penerbitan.',
            'occupation.required' => 'Pekerjaan saat ini wajib diisi.',
            'indonesia_address.required' => 'Alamat tempat tinggal di Indonesia wajib diisi.',
            'indonesia_address.min' => 'Alamat tempat tinggal di Indonesia minimal 10 karakter bermakna.',
            'indonesia_phone.required' => 'Nomor telepon/seluler di Indonesia wajib diisi.',
            'indonesia_phone.regex' => 'Nomor telepon/seluler di Indonesia harus berformat valid (diawali 08 atau +628 dengan panjang 10-15 digit angka).',
            'email.required' => 'Alamat email aktif wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'marital_status.required' => 'Status perkawinan wajib dipilih.',
            'marital_status.in' => 'Pilihan status perkawinan tidak valid.',

            // Seksi 3
            'mother_name.required' => 'Nama ibu kandung wajib diisi.',
            'mother_nationality.required' => 'Kewarganegaraan ibu wajib diisi.',
            'mother_birth_place.required' => 'Tempat lahir ibu wajib diisi.',
            'mother_birth_date.required' => 'Tanggal lahir ibu wajib diisi.',
            'father_name.required' => 'Nama ayah kandung wajib diisi.',
            'father_nationality.required' => 'Kewarganegaraan ayah wajib diisi.',
            'father_birth_place.required' => 'Tempat lahir ayah wajib diisi.',
            'father_birth_date.required' => 'Tanggal lahir ayah wajib diisi.',
            'father_address.required' => 'Alamat ayah kandung wajib diisi.',
            'father_phone.required' => 'Nomor telepon ayah kandung wajib diisi.',
            'spouse_name.required_if' => 'Nama pasangan (suami/istri) wajib diisi untuk status perkawinan Kawin.',
            'spouse_nationality.required_if' => 'Kewarganegaraan pasangan wajib diisi untuk status perkawinan Kawin.',

            // Seksi 4
            'emergency_id_name.required' => 'Nama kontak darurat di Indonesia wajib diisi.',
            'emergency_id_relationship.required' => 'Hubungan dengan kontak darurat di Indonesia wajib diisi.',
            'emergency_id_address.required' => 'Alamat kontak darurat di Indonesia wajib diisi.',
            'emergency_id_phone.required' => 'Nomor telepon kontak darurat di Indonesia wajib diisi.',
            'declaration_accepted.required' => 'Pernyataan keabsahan data wajib disetujui.',
            'declaration_accepted.accepted' => 'Anda harus mencentang dan menyetujui pernyataan keabsahan data sebelum mengirim formulir.',
        ];
    }

    /**
     * Validasi lanjutan: kecocokan dokumen persyaratan terhadap Kategori A (Dewasa) vs B (Anak).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if (! empty($this->input('document_tokens')) || ! empty($this->input('document_types'))) {
                $classificationService = app(PerdimDocumentClassificationService::class);
                $storageService = app(PerdimDocumentStorageService::class);

                $birthDate = $this->input('birth_date');
                $maritalStatus = $this->input('marital_status');

                if ($birthDate && $maritalStatus) {
                    $category = $classificationService->determineCategory($birthDate, $maritalStatus);

                    $uploadedTypes = [];

                    if (! empty($this->input('document_types')) && is_array($this->input('document_types'))) {
                        $uploadedTypes = array_merge($uploadedTypes, $this->input('document_types'));
                    }

                    if (! empty($this->input('document_tokens')) && is_array($this->input('document_tokens'))) {
                        foreach ($this->input('document_tokens') as $token) {
                            $meta = $storageService->getTempMetadata((string) $token);
                            if ($meta && ! empty($meta['document_type'])) {
                                $uploadedTypes[] = $meta['document_type'];
                            }
                        }
                    }

                    $result = $classificationService->validateUploadedDocuments($category, $uploadedTypes);
                    if (! $result['is_valid']) {
                        foreach ($result['errors'] as $error) {
                            $validator->errors()->add('document_tokens', $error);
                        }
                    }
                }
            }
        });
    }
}
