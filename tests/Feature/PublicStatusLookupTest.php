<?php

namespace Tests\Feature;

use App\Enums\VerificationStatus;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicStatusLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_status_page_is_accessible(): void
    {
        $response = $this->get('/cek-status');

        $response->assertOk();
        $response->assertSee('Lacak Status Permohonan SPRI');
    }

    public function test_valid_lookup_displays_masked_applicant_name_and_safe_data(): void
    {
        $verification = Verification::factory()->create([
            'registration_code' => 'SPRI-2026-X7K9P2',
            'applicant_name' => 'Muhammad Rizky',
            'document_type' => 'SPRI Baru (Paspor Elektronik 48 Hal)',
            'status' => VerificationStatus::APPROVED,
            'public_notes' => 'Silakan hadir pada jadwal sesi foto & biometrik.',
            'admin_notes' => 'CATATAN_INTERNAL_RAHASIA_LOKET_123',
        ]);

        $response = $this->post('/cek-status', [
            'registration_code' => 'SPRI-2026-X7K9P2',
        ]);

        $response->assertOk();
        $response->assertSee('SPRI-2026-X7K9P2');
        $response->assertSee('Disetujui');
        $response->assertSee('Silakan hadir pada jadwal sesi foto & biometrik.');

        // Pastikan nama pemohon disamarkan (tidak memunculkan nama lengkap secara utuh)
        $response->assertDontSee('Muhammad Rizky');
        $response->assertSee('M****** R****');

        // SANGAT KRUSIAL: Catatan internal dan ID pembuat tidak boleh bocor
        $response->assertDontSee('CATATAN_INTERNAL_RAHASIA_LOKET_123');
        $response->assertDontSee("created_by");
    }

    public function test_lookup_normalizes_lowercase_input_to_uppercase(): void
    {
        Verification::factory()->create([
            'registration_code' => 'SPRI-2026-M3W1Q8',
            'status' => VerificationStatus::IN_REVIEW,
        ]);

        $response = $this->post('/cek-status', [
            'registration_code' => 'spri-2026-m3w1q8', // huruf kecil
        ]);

        $response->assertOk();
        $response->assertSee('SPRI-2026-M3W1Q8');
        $response->assertSee('Sedang Diverifikasi');
    }

    public function test_unknown_code_returns_generic_not_found_message(): void
    {
        $response = $this->post('/cek-status', [
            'registration_code' => 'SPRI-9999-NOTFOUND',
        ]);

        $response->assertOk();
        $response->assertSee('Berkas Registrasi Tidak Ditemukan');
        $response->assertSee('SPRI-9999-NOTFOUND');
    }

    public function test_soft_deleted_record_is_hidden_from_public_lookup(): void
    {
        $verification = Verification::factory()->create([
            'registration_code' => 'SPRI-2026-DELETED',
            'status' => VerificationStatus::APPROVED,
        ]);

        $verification->delete(); // Soft delete

        $response = $this->post('/cek-status', [
            'registration_code' => 'SPRI-2026-DELETED',
        ]);

        $response->assertOk();
        $response->assertSee('Berkas Registrasi Tidak Ditemukan');
        $response->assertDontSee('Disetujui');
    }

    public function test_invalid_code_format_fails_validation(): void
    {
        $response = $this->from('/cek-status')->post('/cek-status', [
            'registration_code' => 'KODE DENGAN SPASI & KARAKTER ILEGAL @#$',
        ]);

        $response->assertRedirect('/cek-status');
        $response->assertSessionHasErrors('registration_code');
    }

    public function test_output_is_properly_html_escaped_against_xss(): void
    {
        Verification::factory()->create([
            'registration_code' => 'SPRI-2026-XSS001',
            'applicant_name' => '<script>alert("xss")</script>',
            'public_notes' => '<b>Catatan tebal</b> <img src=x onerror=alert(1)>',
            'status' => VerificationStatus::APPROVED,
        ]);

        $response = $this->post('/cek-status', [
            'registration_code' => 'SPRI-2026-XSS001',
        ]);

        $response->assertOk();
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
        $response->assertSee('&lt;b&gt;Catatan tebal&lt;/b&gt;', false);
    }
}
