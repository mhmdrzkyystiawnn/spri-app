<?php

namespace Tests\Feature;

use App\Enums\VerificationStatus;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_creating_verification_records_initial_status_history(): void
    {
        $this->actingAs($this->admin)->post('/admin/verifications', [
            'registration_code' => 'SPRI-2026-HIST01',
            'applicant_name' => 'Fulan bin Fulan',
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'status' => 'pending',
            'public_notes' => 'Menunggu antrean loket.',
            'admin_notes' => 'Catatan admin loket 1.',
        ]);

        $verification = Verification::where('registration_code', 'SPRI-2026-HIST01')->firstOrFail();

        $this->assertDatabaseHas('verification_status_histories', [
            'verification_id' => $verification->id,
            'from_status' => null,
            'to_status' => 'pending',
            'changed_by' => $this->admin->id,
        ]);

        $this->assertCount(1, $verification->statusHistories);
        $this->assertSame(VerificationStatus::PENDING, $verification->statusHistories->first()->to_status);
        $this->assertNull($verification->statusHistories->first()->from_status);
    }

    public function test_updating_status_creates_transition_history(): void
    {
        $verification = Verification::factory()->pending()->create([
            'registration_code' => 'SPRI-2026-HIST02',
            'created_by' => $this->admin->id,
        ]);

        // Status awal sudah ada 1 riwayat
        $this->assertCount(1, $verification->fresh()->statusHistories);

        // Petugas melakukan transisi status ke in_review
        $this->actingAs($this->admin)->put("/admin/verifications/{$verification->id}", [
            'registration_code' => 'SPRI-2026-HIST02',
            'applicant_name' => $verification->applicant_name,
            'document_type' => $verification->document_type,
            'status' => 'in_review',
            'public_notes' => 'Sedang dalam pemeriksaan verifikator.',
            'admin_notes' => 'Berkas fisik cocok.',
        ]);

        $verification->refresh();
        $this->assertCount(2, $verification->statusHistories);

        $latestHistory = $verification->statusHistories->last();
        $this->assertSame(VerificationStatus::PENDING, $latestHistory->from_status);
        $this->assertSame(VerificationStatus::IN_REVIEW, $latestHistory->to_status);
        $this->assertSame($this->admin->id, $latestHistory->changed_by);
        $this->assertSame('Sedang dalam pemeriksaan verifikator.', $latestHistory->note);
    }

    public function test_updating_verification_without_status_change_does_not_create_duplicate_history(): void
    {
        $verification = Verification::factory()->pending()->create([
            'registration_code' => 'SPRI-2026-HIST03',
            'created_by' => $this->admin->id,
        ]);

        $initialCount = $verification->fresh()->statusHistories->count();
        $this->assertSame(1, $initialCount);

        // Update hanya nama pemohon dan catatan internal, status tetap pending
        $this->actingAs($this->admin)->put("/admin/verifications/{$verification->id}", [
            'registration_code' => 'SPRI-2026-HIST03',
            'applicant_name' => 'Nama Pemohon Terkoreksi',
            'document_type' => $verification->document_type,
            'status' => 'pending', // Sama persis
            'public_notes' => $verification->public_notes,
            'admin_notes' => 'Catatan internal diperbarui tanpa ubah status.',
        ]);

        $verification->refresh();

        // Jumlah riwayat status harus tetap 1 (tidak duplikat)
        $this->assertCount(1, $verification->statusHistories);
    }

    public function test_admin_can_view_status_timeline_on_verification_show_page(): void
    {
        $verification = Verification::factory()->pending()->create([
            'registration_code' => 'SPRI-2026-HIST04',
            'created_by' => $this->admin->id,
        ]);

        // Ubah status ke approved
        $this->actingAs($this->admin)->put("/admin/verifications/{$verification->id}", [
            'registration_code' => 'SPRI-2026-HIST04',
            'applicant_name' => $verification->applicant_name,
            'document_type' => $verification->document_type,
            'status' => 'approved',
            'public_notes' => 'Verifikasi dokumen disetujui lengkap.',
            'admin_notes' => 'Lengkap.',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/verifications/{$verification->id}");

        $response->assertOk();
        $response->assertSee('Kronologi Riwayat Status (Status Timeline)');
        $response->assertSee('Status Awal');
        $response->assertSee('Menunggu');
        $response->assertSee('Disetujui');
        $response->assertSee('Verifikasi dokumen disetujui lengkap.');
    }

    public function test_public_status_check_does_not_leak_status_history_or_timeline(): void
    {
        $verification = Verification::factory()->pending()->create([
            'registration_code' => 'SPRI-2026-HIST05',
            'created_by' => $this->admin->id,
        ]);

        // Ubah status dengan catatan internal
        $this->actingAs($this->admin)->put("/admin/verifications/{$verification->id}", [
            'registration_code' => 'SPRI-2026-HIST05',
            'applicant_name' => $verification->applicant_name,
            'document_type' => $verification->document_type,
            'status' => 'in_review',
            'public_notes' => 'Catatan publik umum.',
            'admin_notes' => 'RAHASIA_INTERNAL_TIMELINE_NOTE',
        ]);

        // Cek halaman publik
        $response = $this->post('/cek-status', [
            'registration_code' => 'SPRI-2026-HIST05',
        ]);

        $response->assertOk();
        $response->assertDontSee('Kronologi Riwayat Status');
        $response->assertDontSee('Status Awal');
        $response->assertDontSee('RAHASIA_INTERNAL_TIMELINE_NOTE');
        $response->assertDontSee('changed_by');
    }
}
