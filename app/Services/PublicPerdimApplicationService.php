<?php

namespace App\Services;

use App\Enums\VerificationStatus;
use App\Models\Verification;
use Illuminate\Support\Facades\DB;

class PublicPerdimApplicationService
{
    public function __construct(
        protected RegistrationCodeGenerator $codeGenerator,
        protected VerificationChecklistService $checklistService,
        protected ?PerdimDocumentStorageService $documentStorageService = null,
    ) {
        $this->documentStorageService ??= app(PerdimDocumentStorageService::class);
    }

    /**
     * Memetakan jenis dokumen resmi (document_type) untuk tabel verifications
     * berdasarkan jenis permohonan, jenis paspor, dan alasan penggantian.
     */
    public function resolveDocumentType(string $applicationType, string $passportType, ?string $replacementReason = null): string
    {
        if ($applicationType === 'penggantian') {
            return match ($replacementReason) {
                'habis_masa_berlaku' => 'Penggantian SPRI Habis Masa Berlaku',
                'halaman_penuh' => 'Penggantian SPRI Halaman Penuh',
                'hilang', 'rusak' => 'Penggantian SPRI Hilang/Rusak',
                default => 'Penggantian SPRI Habis Masa Berlaku',
            };
        }

        if ($passportType === 'splp') {
            return 'Surat Perjalanan Laksana Paspor (SPLP)';
        }

        if (in_array($passportType, [
            'paspor_elektronik_48',
            'elektronik_48',
            'elektronik_lembar_polikarbonat',
            'paspor_elektronik_polikarbonat',
        ], true)) {
            return 'SPRI Baru (Paspor Elektronik 48 Hal)';
        }

        return 'SPRI Baru (Paspor Biasa 48 Hal)';
    }

    /**
     * Menyimpan data permohonan PERDIM 14 secara transaksional atomik:
     * 1. Menghasilkan registration_code unik
     * 2. Memetakan document_type
     * 3. Membuat record Verification induk dengan status PENDING
     * 4. Membuat record child PerdimApplication dengan timestamp declaration_accepted_at
     * 5. Menginisialisasi checklist berkas fisik secara idempoten
     *
     * @param  array<string, mixed>  $data  Data payload yang telah tervalidasi
     */
    public function submit(array $data): Verification
    {
        return DB::transaction(function () use ($data) {
            $registrationCode = $this->codeGenerator->generate();

            $documentType = $this->resolveDocumentType(
                $data['application_type'],
                $data['passport_type'],
                $data['replacement_reason'] ?? null
            );

            $verification = Verification::create([
                'registration_code' => $registrationCode,
                'applicant_name' => $data['full_name'],
                'document_type' => $documentType,
                'status' => VerificationStatus::PENDING,
                'public_notes' => 'Permohonan mandiri melalui Portal Publik PERDIM 14. Berkas menunggu verifikasi fisik di meja layanan.',
                'admin_notes' => null,
                'created_by' => null,
            ]);

            $applicationAttributes = $data;
            unset($applicationAttributes['document_tokens']);
            $applicationAttributes['verification_id'] = $verification->id;
            $applicationAttributes['declaration_accepted'] = true;
            $applicationAttributes['declaration_accepted_at'] = now();

            $application = $verification->perdimApplication()->create($applicationAttributes);

            if (! empty($data['document_tokens']) && is_array($data['document_tokens'])) {
                $this->documentStorageService->finalizeDocuments($application, $data['document_tokens']);
            }

            // Inisialisasi otomatis checklist persyaratan dokumen fisik sesuai jenis dokumen
            $this->checklistService->initializeForVerification($verification);

            return $verification->load(['perdimApplication.documents', 'checklists.requirement']);
        });
    }
}
