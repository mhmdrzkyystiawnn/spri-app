<?php

namespace App\Services;

use App\Models\DocumentRequirement;
use App\Models\Verification;
use App\Models\VerificationChecklist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VerificationChecklistService
{
    /**
     * Inisialisasi daftar checklist dokumen fisik untuk suatu berkas verifikasi.
     * Bersifat idempoten: aman dipanggil berulang kali tanpa menciptakan duplikasi.
     *
     * @return Collection<int, VerificationChecklist>
     */
    public function initializeForVerification(Verification $verification): Collection
    {
        return DB::transaction(function () use ($verification) {
            $activeRequirements = DocumentRequirement::query()
                ->active()
                ->forDocumentType($verification->document_type)
                ->ordered()
                ->get();

            foreach ($activeRequirements as $requirement) {
                VerificationChecklist::firstOrCreate(
                    [
                        'verification_id' => $verification->id,
                        'document_requirement_id' => $requirement->id,
                    ],
                    [
                        'is_checked' => false,
                        'notes' => null,
                        'checked_by' => null,
                        'checked_at' => null,
                    ]
                );
            }

            return $verification->checklists()
                ->with('requirement')
                ->get();
        });
    }

    /**
     * Sinkronisasi persyaratan saat jenis permohonan SPRI diubah pada verifikasi.
     * Persyaratan dokumen jenis baru akan ditambahkan tanpa menghapus riwayat sebelumnya.
     *
     * @return Collection<int, VerificationChecklist>
     */
    public function syncForDocumentType(Verification $verification, string $newDocumentType): Collection
    {
        return DB::transaction(function () use ($verification, $newDocumentType) {
            if ($verification->document_type !== $newDocumentType) {
                $verification->update(['document_type' => $newDocumentType]);
            }

            return $this->initializeForVerification($verification);
        });
    }

    /**
     * Menghitung statistik pemenuhan checklist untuk verifikasi berkas.
     *
     * @return array{total: int, checked: int, mandatory_total: int, mandatory_checked: int, is_complete: bool}
     */
    public function getCompletionStats(Verification $verification): array
    {
        $checklists = $verification->checklists()->with('requirement')->get();

        $total = $checklists->count();
        $checked = $checklists->where('is_checked', true)->count();

        $mandatoryChecklists = $checklists->filter(fn ($c) => $c->requirement?->is_mandatory);
        $mandatoryTotal = $mandatoryChecklists->count();
        $mandatoryChecked = $mandatoryChecklists->where('is_checked', true)->count();

        $isComplete = ($mandatoryTotal > 0) ? ($mandatoryChecked === $mandatoryTotal) : ($total > 0 && $checked === $total);

        return [
            'total' => $total,
            'checked' => $checked,
            'mandatory_total' => $mandatoryTotal,
            'mandatory_checked' => $mandatoryChecked,
            'is_complete' => $isComplete,
        ];
    }
}
