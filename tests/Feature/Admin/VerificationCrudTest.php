<?php

namespace Tests\Feature\Admin;

use App\Enums\VerificationStatus;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_verifications_list_and_search(): void
    {
        Verification::factory()->create([
            'registration_code' => 'SPRI-2026-SEARCH1',
            'applicant_name' => 'Budi Santoso',
            'status' => VerificationStatus::PENDING,
        ]);

        Verification::factory()->create([
            'registration_code' => 'SPRI-2026-SEARCH2',
            'applicant_name' => 'Siti Aminah',
            'status' => VerificationStatus::APPROVED,
        ]);

        // List all
        $response = $this->actingAs($this->admin)->get('/admin/verifications');
        $response->assertOk();
        $response->assertSee('SPRI-2026-SEARCH1');
        $response->assertSee('SPRI-2026-SEARCH2');

        // Search by code
        $searchResponse = $this->actingAs($this->admin)->get('/admin/verifications?search=SEARCH1');
        $searchResponse->assertSee('SPRI-2026-SEARCH1');
        $searchResponse->assertDontSee('SPRI-2026-SEARCH2');

        // Filter by status
        $filterResponse = $this->actingAs($this->admin)->get('/admin/verifications?status=approved');
        $filterResponse->assertSee('SPRI-2026-SEARCH2');
        $filterResponse->assertDontSee('SPRI-2026-SEARCH1');
    }

    public function test_admin_can_create_verification(): void
    {
        $payload = [
            'registration_code' => 'SPRI-2026-NEW001',
            'applicant_name' => 'Ahmad Dahlan',
            'document_type' => 'SPRI Baru (Paspor Elektronik 48 Hal)',
            'status' => 'pending',
            'public_notes' => 'Menunggu verifikasi fisik.',
            'admin_notes' => 'Catatan internal: dokumen lengkap di loket 1.',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/verifications', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('verifications', [
            'registration_code' => 'SPRI-2026-NEW001',
            'applicant_name' => 'Ahmad Dahlan',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_duplicate_registration_code_is_rejected(): void
    {
        Verification::factory()->create([
            'registration_code' => 'SPRI-2026-DUPLICATE',
        ]);

        $payload = [
            'registration_code' => 'SPRI-2026-DUPLICATE',
            'applicant_name' => 'Nama Lain',
            'document_type' => 'SPRI Baru',
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/verifications/create')
            ->post('/admin/verifications', $payload);

        $response->assertRedirect('/admin/verifications/create');
        $response->assertSessionHasErrors('registration_code');
    }

    public function test_public_notes_required_when_status_needs_revision_or_rejected(): void
    {
        $payload = [
            'registration_code' => 'SPRI-2026-REV001',
            'applicant_name' => 'Dewi Sartika',
            'document_type' => 'SPRI Baru',
            'status' => 'needs_revision',
            'public_notes' => '',
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/verifications/create')
            ->post('/admin/verifications', $payload);

        $response->assertSessionHasErrors('public_notes');
    }

    public function test_admin_can_view_verification_detail_including_admin_notes(): void
    {
        $verification = Verification::factory()->create([
            'admin_notes' => 'RAHASIA: Pemohon memiliki catatan verifikasi khusus.',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/verifications/{$verification->id}");

        $response->assertOk();
        $response->assertSee('RAHASIA: Pemohon memiliki catatan verifikasi khusus.');
    }

    public function test_admin_can_update_verification_and_approving_sets_verified_at(): void
    {
        $verification = Verification::factory()->pending()->create([
            'verified_at' => null,
        ]);

        $updatePayload = [
            'registration_code' => $verification->registration_code,
            'applicant_name' => 'Nama Pemohon Diperbarui',
            'document_type' => $verification->document_type,
            'status' => 'approved',
            'public_notes' => 'Telah diverifikasi dan disetujui.',
            'admin_notes' => 'Verifikasi selesai.',
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/verifications/{$verification->id}", $updatePayload);

        $response->assertRedirect("/admin/verifications/{$verification->id}");

        $verification->refresh();
        $this->assertSame('Nama Pemohon Diperbarui', $verification->applicant_name);
        $this->assertSame(VerificationStatus::APPROVED, $verification->status);
        $this->assertNotNull($verification->verified_at);
    }

    public function test_admin_can_soft_delete_verification(): void
    {
        $verification = Verification::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/verifications/{$verification->id}");

        $response->assertRedirect('/admin/verifications');
        $this->assertSoftDeleted('verifications', [
            'id' => $verification->id,
        ]);
    }
}
