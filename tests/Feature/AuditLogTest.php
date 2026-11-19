<?php

namespace Tests\Feature;

use App\Enums\VerificationStatus;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_audit_log_records_creation_of_verification(): void
    {
        $this->actingAs($this->admin)->post('/admin/verifications', [
            'registration_code' => 'SPRI-2026-AUDIT01',
            'applicant_name' => 'Bambang Soedirman',
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'status' => 'pending',
            'public_notes' => 'Menunggu verifikasi loket.',
            'admin_notes' => 'Catatan awal loket.',
        ]);

        $verification = Verification::where('registration_code', 'SPRI-2026-AUDIT01')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'verification_id' => $verification->id,
            'user_id' => $this->admin->id,
            'action' => 'created',
        ]);

        $log = AuditLog::where('verification_id', $verification->id)->where('action', 'created')->first();
        $this->assertNull($log->before);
        $this->assertIsArray($log->after);
        $this->assertSame('SPRI-2026-AUDIT01', $log->after['registration_code']);
        $this->assertSame('Bambang Soedirman', $log->after['applicant_name']);
    }

    public function test_audit_log_records_status_change_with_diff(): void
    {
        $verification = Verification::factory()->pending()->create([
            'registration_code' => 'SPRI-2026-AUDIT02',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)->put("/admin/verifications/{$verification->id}", [
            'registration_code' => 'SPRI-2026-AUDIT02',
            'applicant_name' => $verification->applicant_name,
            'document_type' => $verification->document_type,
            'status' => 'approved',
            'public_notes' => 'Verifikasi selesai dan disetujui.',
            'admin_notes' => $verification->admin_notes,
        ]);

        $log = AuditLog::where('verification_id', $verification->id)
            ->where('action', 'status_changed')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertSame(VerificationStatus::PENDING->value, $log->before['status']);
        $this->assertSame(VerificationStatus::APPROVED->value, $log->after['status']);
    }

    public function test_audit_log_records_soft_deletion(): void
    {
        $verification = Verification::factory()->create([
            'registration_code' => 'SPRI-2026-AUDIT03',
        ]);

        $this->actingAs($this->admin)->delete("/admin/verifications/{$verification->id}");

        $log = AuditLog::where('verification_id', $verification->id)
            ->where('action', 'deleted')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertNotNull($log->after['deleted_at']);
    }
}
