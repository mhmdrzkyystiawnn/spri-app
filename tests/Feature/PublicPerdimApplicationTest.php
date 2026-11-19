<?php

namespace Tests\Feature;

use App\Enums\VerificationStatus;
use App\Models\DocumentRequirement;
use App\Models\PerdimApplication;
use App\Models\Verification;
use App\Services\PublicPerdimApplicationService;
use App\Services\VerificationChecklistService;
use Database\Seeders\DocumentRequirementSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPerdimApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DocumentRequirementSeeder::class);
    }

    /**
     * Helper untuk menghasilkan payload permohonan PERDIM 14 yang lengkap dan valid.
     *
     * @return array<string, mixed>
     */
    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            // Seksi 1: Permohonan & SPRI
            'application_type' => 'baru',
            'passport_type' => 'paspor_biasa_48',
            'replacement_reason' => null,
            'change_reason' => null,

            // Seksi 2: Identitas Diri Pemohon
            'full_name' => 'Budi Santoso',
            'alias_name' => 'Budi S',
            'gender' => 'L',
            'height_cm' => 172,
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-08-17',
            'nik' => '3171011708950001',
            'identity_number' => null,
            'identity_issued_place' => 'Jakarta Pusat',
            'identity_issued_date' => '2020-01-15',
            'identity_expiry_date' => null,
            'occupation' => 'Karyawan Swasta',
            'workplace_address' => 'Jl. Jend. Sudirman Kav 21, Jakarta Selatan',
            'office_phone' => '0215551234',
            'indonesia_address' => 'Jl. Kebon Sirih No. 12, Menteng, Jakarta Pusat',
            'indonesia_phone' => '081234567890',
            'overseas_address' => null,
            'overseas_phone' => null,
            'email' => 'budi.santoso@example.com',
            'marital_status' => 'kawin',

            // Seksi 3: Data Keluarga
            'mother_name' => 'Siti Aminah',
            'mother_nationality' => 'WNI',
            'mother_birth_place' => 'Bandung',
            'mother_birth_date' => '1968-04-12',

            'father_name' => 'Ahmad Santoso',
            'father_nationality' => 'WNI',
            'father_birth_place' => 'Cirebon',
            'father_birth_date' => '1965-02-20',
            'father_address' => 'Jl. Sukajadi No. 45, Bandung',
            'father_phone' => '081398765432',

            'spouse_name' => 'Dewi Lestari',
            'spouse_nationality' => 'WNI',
            'spouse_birth_place' => 'Semarang',
            'spouse_birth_date' => '1996-11-25',
            'spouse_address' => 'Jl. Kebon Sirih No. 12, Menteng, Jakarta Pusat',

            // Seksi 4: Kontak Darurat & Pernyataan
            'change_details' => null,
            'emergency_id_name' => 'Rendra Santoso',
            'emergency_id_relationship' => 'Saudara Kandung',
            'emergency_id_address' => 'Jl. Anggrek No. 8, Jakarta Barat',
            'emergency_id_phone' => '081288776655',
            'emergency_overseas_name' => null,
            'emergency_overseas_address' => null,
            'emergency_overseas_phone' => null,

            'declaration_accepted' => '1',
        ], $overrides);
    }

    public function test_public_can_access_perdim_create_form(): void
    {
        $response = $this->get(route('public.perdim.create'));

        $response->assertStatus(200);
        $response->assertSee('Permohonan Surat Perjalanan Republik Indonesia');
        $response->assertSee('Formulir Resmi PERDIM 14');
        $response->assertSee('Seksi 1: Jenis Permohonan');
    }

    public function test_validation_errors_when_submitting_empty_form(): void
    {
        $response = $this->post(route('public.perdim.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'application_type',
            'passport_type',
            'full_name',
            'gender',
            'height_cm',
            'birth_place',
            'birth_date',
            'identity_issued_place',
            'identity_issued_date',
            'occupation',
            'indonesia_address',
            'indonesia_phone',
            'email',
            'marital_status',
            'mother_name',
            'father_name',
            'emergency_id_name',
            'declaration_accepted',
        ]);
    }

    public function test_validation_fails_when_neither_nik_nor_identity_number_provided(): void
    {
        $payload = $this->validPayload([
            'nik' => null,
            'identity_number' => null,
        ]);

        $response = $this->post(route('public.perdim.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nik', 'identity_number']);
    }

    public function test_validation_passes_when_only_nik_provided(): void
    {
        $payload = $this->validPayload([
            'nik' => '3171011708950001',
            'identity_number' => null,
        ]);

        $response = $this->post(route('public.perdim.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_validation_fails_when_nik_is_not_16_digits(): void
    {
        $payload = $this->validPayload([
            'nik' => '12345678', // Kurang dari 16 digit
            'identity_number' => null,
        ]);

        $response = $this->post(route('public.perdim.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nik']);
    }

    public function test_validation_passes_when_only_identity_number_provided(): void
    {
        $payload = $this->validPayload([
            'nik' => null,
            'identity_number' => 'PASSPORT-OLD-998822',
        ]);

        $response = $this->post(route('public.perdim.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_conditional_validation_for_replacement_application(): void
    {
        // 1. Gagal jika jenis permohonan penggantian tapi alasan tidak diisi
        $invalidPayload = $this->validPayload([
            'application_type' => 'penggantian',
            'replacement_reason' => null,
        ]);

        $response = $this->post(route('public.perdim.store'), $invalidPayload);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['replacement_reason']);

        // 2. Berhasil jika alasan penggantian diisi valid
        $validPayload = $this->validPayload([
            'application_type' => 'penggantian',
            'replacement_reason' => 'habis_masa_berlaku',
        ]);

        $response = $this->post(route('public.perdim.store'), $validPayload);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_conditional_validation_for_change_application(): void
    {
        // 1. Gagal jika permohonan perubahan tapi change_reason kosong
        $invalidPayload = $this->validPayload([
            'application_type' => 'perubahan',
            'change_reason' => null,
        ]);

        $response = $this->post(route('public.perdim.store'), $invalidPayload);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['change_reason']);

        // 2. Berhasil jika change_reason diisi
        $validPayload = $this->validPayload([
            'application_type' => 'perubahan',
            'change_reason' => 'Perubahan Nama Berdasarkan Penetapan Pengadilan',
        ]);

        $response = $this->post(route('public.perdim.store'), $validPayload);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_conditional_validation_for_married_applicant(): void
    {
        // 1. Gagal jika status kawin tapi data pasangan kosong
        $invalidPayload = $this->validPayload([
            'marital_status' => 'kawin',
            'spouse_name' => null,
            'spouse_nationality' => null,
        ]);

        $response = $this->post(route('public.perdim.store'), $invalidPayload);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['spouse_name', 'spouse_nationality']);

        // 2. Berhasil jika status tidak kawin dan data pasangan dikosongkan
        $validPayload = $this->validPayload([
            'marital_status' => 'tidak_kawin',
            'spouse_name' => null,
            'spouse_nationality' => null,
        ]);

        $response = $this->post(route('public.perdim.store'), $validPayload);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    public function test_declaration_must_be_accepted(): void
    {
        $payload = $this->validPayload([
            'declaration_accepted' => '0',
        ]);

        $response = $this->post(route('public.perdim.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['declaration_accepted']);
    }

    public function test_successful_submission_creates_records_and_initializes_checklist(): void
    {
        $payload = $this->validPayload();

        $response = $this->post(route('public.perdim.store'), $payload);

        $response->assertRedirect(route('public.perdim.receipt'));
        $response->assertSessionHas('submitted_code');

        $code = session('submitted_code');
        $this->assertNotNull($code);
        $this->assertMatchesRegularExpression('/^SPRI-[0-9]{4}-[A-Z0-9]{6}$/', $code);

        // Verifikasi record pada tabel verifications
        $verification = Verification::query()->where('registration_code', $code)->first();
        $this->assertNotNull($verification);
        $this->assertSame('Budi Santoso', $verification->applicant_name);
        $this->assertSame('SPRI Baru (Paspor Biasa 48 Hal)', $verification->document_type);
        $this->assertSame(VerificationStatus::PENDING, $verification->status);
        $this->assertNull($verification->created_by);
        $this->assertStringContainsString('Portal Publik PERDIM 14', $verification->public_notes);

        // Verifikasi record pada tabel perdim_applications
        $perdimApp = $verification->perdimApplication;
        $this->assertNotNull($perdimApp);
        $this->assertSame('baru', $perdimApp->application_type);
        $this->assertSame('paspor_biasa_48', $perdimApp->passport_type);
        $this->assertSame('3171011708950001', $perdimApp->nik);
        $this->assertTrue($perdimApp->declaration_accepted);
        $this->assertNotNull($perdimApp->declaration_accepted_at);

        // Verifikasi relasi checklist fisik terisi otomatis
        $checklists = $verification->checklists;
        $this->assertNotEmpty($checklists);
        $this->assertSame(
            DocumentRequirement::where('document_type', 'SPRI Baru (Paspor Biasa 48 Hal)')->count(),
            $checklists->count()
        );
    }

    public function test_prohibited_parameters_are_stripped_and_cannot_be_injected(): void
    {
        $payload = $this->validPayload([
            'registration_code' => 'SPRI-2020-HACKED',
            'status' => VerificationStatus::APPROVED->value,
            'admin_notes' => 'Manipulasi izin disetujui langsung dari luar',
            'created_by' => 9999,
        ]);

        $response = $this->post(route('public.perdim.store'), $payload);

        $response->assertRedirect(route('public.perdim.receipt'));

        $assignedCode = session('submitted_code');
        $this->assertNotSame('SPRI-2020-HACKED', $assignedCode);

        $verification = Verification::where('registration_code', $assignedCode)->first();
        $this->assertNotNull($verification);
        $this->assertSame(VerificationStatus::PENDING, $verification->status);
        $this->assertNull($verification->admin_notes);
        $this->assertNull($verification->created_by);
    }

    public function test_transactional_rollback_when_exception_occurs(): void
    {
        // Mock checklist service agar melempar exception di tengah proses transaksi
        $mockChecklist = $this->mock(VerificationChecklistService::class);
        $mockChecklist->shouldReceive('initializeForVerification')
            ->once()
            ->andThrow(new Exception('Simulasi kegagalan inisialisasi checklist'));

        $service = app(PublicPerdimApplicationService::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Simulasi kegagalan inisialisasi checklist');

        try {
            $service->submit($this->validPayload());
        } finally {
            // Pastikan tidak ada data yang tersimpan sebagian (atomik rollback)
            $this->assertSame(0, Verification::count());
            $this->assertSame(0, PerdimApplication::count());
        }
    }

    public function test_rate_limiting_throttles_excessive_submissions(): void
    {
        $payload = $this->validPayload();

        // Lakukan 10 permohonan pertama (limit: 10 per minute)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post(route('public.perdim.store'), $payload);
            $response->assertStatus(302);
        }

        // Permohonan ke-11 harus diblokir oleh throttle middleware (HTTP 429)
        $response = $this->post(route('public.perdim.store'), $payload);
        $response->assertStatus(429);
    }

    public function test_receipt_page_displays_details_from_session_and_code_query(): void
    {
        $payload = $this->validPayload();
        $this->post(route('public.perdim.store'), $payload);

        $code = session('submitted_code');

        // 1. Akses via session aktif (menampilkan masked PII dan rincian berkas)
        $response = $this->get(route('public.perdim.receipt'));
        $response->assertStatus(200);
        $response->assertSee($code);
        $response->assertSee('B*** S******');
        $response->assertSee('3171************');
        $response->assertSee('Tanda Terima Permohonan SPRI');

        // 2. Akses via query param ?code=
        $responseWithQuery = $this->get(route('public.perdim.receipt', ['code' => $code]));
        $responseWithQuery->assertStatus(200);
        $responseWithQuery->assertSee($code);
        $responseWithQuery->assertSee('B*** S******');
        $responseWithQuery->assertSee('3171************');
    }

    public function test_wizard_five_steps_navigation_elements_present(): void
    {
        $response = $this->get(route('public.perdim.create'));

        $response->assertStatus(200);

        // Verifikasi 5 Tahap Stepper
        $response->assertSee('Langkah 1');
        $response->assertSee('Jenis SPRI');
        $response->assertSee('Langkah 2');
        $response->assertSee('Identitas');
        $response->assertSee('Langkah 3');
        $response->assertSee('Keluarga');
        $response->assertSee('Langkah 4');
        $response->assertSee('Darurat');
        $response->assertSee('Langkah 5');
        $response->assertSee('Pratinjau');

        // Verifikasi Tombol Aksi Wizard
        $response->assertSee('Sebelumnya');
        $response->assertSee('Lanjutkan ke Langkah Berikutnya');
        $response->assertSee('Kirim Permohonan Paspor (PERDIM 14)');
    }

    public function test_portal_navigation_links_present_on_public_views(): void
    {
        // 1. Cek tautan pendaftaran mandiri pada portal /cek-status
        $statusResponse = $this->get(route('status.show'));
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('Belum Memiliki Nomor Registrasi?');
        $statusResponse->assertSee('Isi Formulir PERDIM 14');
        $statusResponse->assertSee(route('public.perdim.create'));

        // 2. Cek navigasi header pada layout publik
        $statusResponse->assertSee('+ Permohonan Baru (PERDIM 14)');
    }

    public function test_receipt_page_displays_checklist_and_print_elements(): void
    {
        $payload = $this->validPayload();
        $this->post(route('public.perdim.store'), $payload);

        $code = session('submitted_code');
        $response = $this->get(route('public.perdim.receipt', ['code' => $code]));

        $response->assertStatus(200);
        $response->assertSee('window.print()', false);
        $response->assertSee('Cetak Lembar Tanda Terima');
        $response->assertSee('Daftar Berkas Fisik yang Wajib Dibawa ke Loket:');
        $response->assertSee('Wajib Asli');
        $response->assertSee('Pantau Status Permohonan');
    }
}
