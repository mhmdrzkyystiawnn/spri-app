<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationChecklist;

class VerificationChecklistPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar checklist.
     */
    public function viewAny(User $user): bool
    {
        return $user->canAccessAdmin();
    }

    /**
     * Tentukan apakah pengguna dapat melihat checklist tertentu.
     */
    public function view(User $user, VerificationChecklist $checklist): bool
    {
        return $user->canAccessAdmin();
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui item checklist.
     * Hanya Admin dan Verifikator yang berwenang, Pimpinan hanya dapat melihat (read-only).
     */
    public function update(User $user, VerificationChecklist $checklist): bool
    {
        return ($user->isAdmin() || $user->isVerifikator()) && $user->is_active;
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui kumpulan checklist pada berkas verifikasi.
     */
    public function updateForVerification(User $user, Verification $verification): bool
    {
        return ($user->isAdmin() || $user->isVerifikator()) && $user->is_active;
    }
}
