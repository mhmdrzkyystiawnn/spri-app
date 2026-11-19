<?php

namespace Tests\Feature\Admin;

use App\Enums\VerificationStatus;
use App\Models\PerdimApplication;
use App\Models\User;
use App\Models\Verification;
use Database\Seeders\DocumentRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPerdimIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $verifikator;

    private User $pimpinan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DocumentRequirementSeeder::class);

        $this->admin = User::factory()->admin()->create();
        $this->verifikator = User::factory()->verifikator()->create();
        $this->pimpinan = User::factory()->pimpinan()->create();
    }

    public function test_admin_and_verifikator_can_view_perdim14_dossier_panel(): void
    {
        $verification = Verification::factory()->create([
            'registration_code' => 'SPRI-2026-ADM001',
            'applicant_name' => 'Budi Santoso',
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'status' => VerificationStatus::PENDING,
        ]);

        $perdim = PerdimApplication::factory()->create([
            'verification_id' => $verification->id,
            'full_name' => 'Budi Santoso',
            'nik' => '3171011708950001',
            'mother_name' => 'Siti Aminah',
            'father_name' => 'Ahmad Santoso',
            'emergency_id_name' => 'Rendra Santoso',
            'declaration_accepted' => true,
        ]);

        // Verifikasi sebagai Petugas Verifikator
        $response = $this->actingAs($this->verifikator)->get(route('admin.verifications.show', $verification));

        $response->assertOk();
        $response->assertSee('Rincian Formulir PERDIM 14 (Pendaftaran Mandiri)');
        $response->assertSee('Seksi 1: Data Permohonan &amp; Identitas Diri Pemohon', false);
        $response->assertSee('Budi Santoso');
        $response->assertSee('3171011708950001');
        $response->assertSee('Siti Aminah');
        $response->assertSee('Ahmad Santoso');
        $response->assertSee('Rendra Santoso');
        $response->assertSee('Pernyataan Integritas Disetujui Pemohon');

        // Verifikasi tombol mutasi aktif untuk verifikator
        $response->assertSee('Edit Berkas');

        // Verifikasi sebagai Petugas Admin
        $adminResponse = $this->actingAs($this->admin)->get(route('admin.verifications.show', $verification));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Rincian Formulir PERDIM 14 (Pendaftaran Mandiri)');
        $adminResponse->assertSee('Hapus Berkas');
    }

    public function test_legacy_verification_without_perdim_application_renders_safely(): void
    {
        // Berkas manual yang didaftarkan langsung di loket (tanpa relasi perdimApplication)
        $legacyVerification = Verification::factory()->create([
            'registration_code' => 'SPRI-2026-LEGACY',
            'applicant_name' => 'Dewi Sartika',
            'status' => VerificationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->verifikator)->get(route('admin.verifications.show', $legacyVerification));

        $response->assertOk();
        $response->assertSee('Permohonan Langsung / Legacy');
        $response->assertSee('Berkas ini didaftarkan langsung oleh petugas loket atau tidak memiliki formulir digital PERDIM 14.');
        $response->assertDontSee('Rincian Formulir PERDIM 14 (Pendaftaran Mandiri)');
    }

    public function test_pimpinan_can_view_perdim14_dossier_in_read_only_mode(): void
    {
        $verification = Verification::factory()->create([
            'registration_code' => 'SPRI-2026-PIMP01',
            'applicant_name' => 'Indra Permana',
            'status' => VerificationStatus::IN_REVIEW,
        ]);

        PerdimApplication::factory()->create([
            'verification_id' => $verification->id,
            'full_name' => 'Indra Permana',
            'nik' => '3271011102920002',
            'declaration_accepted' => true,
        ]);

        $response = $this->actingAs($this->pimpinan)->get(route('admin.verifications.show', $verification));

        $response->assertOk();
        // Pimpinan dapat melihat dossier lengkap
        $response->assertSee('Rincian Formulir PERDIM 14 (Pendaftaran Mandiri)');
        $response->assertSee('Indra Permana');
        $response->assertSee('3271011102920002');

        // Kepatuhan RBAC: Pimpinan bersikap strictly read-only
        $response->assertDontSee('Edit Berkas & Status');
        $response->assertDontSee('Hapus Berkas');
        $response->assertDontSee('Simpan Checklist');
    }

    public function test_unauthenticated_user_cannot_access_verification_show(): void
    {
        $verification = Verification::factory()->create();

        $response = $this->get(route('admin.verifications.show', $verification));
        $response->assertRedirect(route('login'));
    }
}
