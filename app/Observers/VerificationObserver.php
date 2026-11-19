<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Verification;
use App\Models\VerificationStatusHistory;
use Illuminate\Support\Facades\Auth;

class VerificationObserver
{
    /**
     * Handle the Verification "created" event.
     */
    public function created(Verification $verification): void
    {
        $after = $this->extractTrackedAttributes($verification);

        AuditLog::create([
            'user_id' => Auth::id() ?? $verification->created_by,
            'verification_id' => $verification->id,
            'action' => 'created',
            'before' => null,
            'after' => $after,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        VerificationStatusHistory::create([
            'verification_id' => $verification->id,
            'from_status' => null,
            'to_status' => $verification->status instanceof \BackedEnum ? $verification->status->value : $verification->status,
            'note' => $verification->public_notes ?? $verification->admin_notes ?? 'Pendaftaran berkas permohonan ke antrean verifikasi.',
            'changed_by' => Auth::id() ?? $verification->created_by,
            'changed_at' => now(),
        ]);
    }

    /**
     * Handle the Verification "updated" event.
     */
    public function updated(Verification $verification): void
    {
        $original = $verification->getOriginal();
        $changes = $verification->getChanges();

        // Singkirkan updated_at dari log jika itu satu-satunya perubahan
        unset($changes['updated_at']);
        if (empty($changes)) {
            return;
        }

        $before = [];
        $after = [];

        foreach (array_keys($changes) as $key) {
            $before[$key] = $original[$key] ?? null;
            $after[$key] = $verification->getAttribute($key);

            // Handle enum serialization
            if ($before[$key] instanceof \BackedEnum) {
                $before[$key] = $before[$key]->value;
            }
            if ($after[$key] instanceof \BackedEnum) {
                $after[$key] = $after[$key]->value;
            }
        }

        $action = array_key_exists('status', $changes) ? 'status_changed' : 'updated';

        AuditLog::create([
            'user_id' => Auth::id(),
            'verification_id' => $verification->id,
            'action' => $action,
            'before' => $before,
            'after' => $after,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        // Catat riwayat status hanya jika status benar-benar berubah
        if (array_key_exists('status', $changes)) {
            $fromStatus = $original['status'] instanceof \BackedEnum ? $original['status']->value : ($original['status'] ?? null);
            $toStatus = $verification->status instanceof \BackedEnum ? $verification->status->value : $verification->status;

            if ($fromStatus !== $toStatus) {
                VerificationStatusHistory::create([
                    'verification_id' => $verification->id,
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'note' => $verification->public_notes ?? $verification->admin_notes,
                    'changed_by' => Auth::id() ?? $verification->created_by,
                    'changed_at' => now(),
                ]);
            }
        }
    }

    /**
     * Handle the Verification "deleted" event.
     */
    public function deleted(Verification $verification): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'verification_id' => $verification->id,
            'action' => 'deleted',
            'before' => ['status' => $verification->status instanceof \BackedEnum ? $verification->status->value : $verification->status],
            'after' => ['deleted_at' => now()->toIso8601String()],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Handle the Verification "restored" event.
     */
    public function restored(Verification $verification): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'verification_id' => $verification->id,
            'action' => 'restored',
            'before' => ['deleted_at' => $verification->getOriginal('deleted_at')],
            'after' => ['deleted_at' => null],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Ekstraksi atribut yang dilacak untuk audit.
     */
    protected function extractTrackedAttributes(Verification $verification): array
    {
        return [
            'registration_code' => $verification->registration_code,
            'applicant_name' => $verification->applicant_name,
            'document_type' => $verification->document_type,
            'status' => $verification->status instanceof \BackedEnum ? $verification->status->value : $verification->status,
            'public_notes' => $verification->public_notes,
            'admin_notes' => $verification->admin_notes,
            'verified_at' => $verification->verified_at?->toIso8601String(),
        ];
    }
}
