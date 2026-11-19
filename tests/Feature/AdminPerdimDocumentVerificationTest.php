<?php

namespace Tests\Feature;

use App\Models\PerdimApplication;
use App\Models\PerdimDocument;
use App\Models\User;
use App\Models\Verification;
use Database\Seeders\DocumentRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPerdimDocumentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DocumentRequirementSeeder::class);
        Storage::fake('private');
    }

    /**
     * Helper untuk membuat berkas verifikasi beserta permohonan PERDIM dan dokumen fisik di disk privat.
     */
    protected function createVerificationWithDocument(
        array $userOverrides = [],
        array $appOverrides = [],
        string $docType = 'ktp_pemohon',
        string $fileName = 'ktp_pemohon.pdf',
        string $mime = 'application/pdf',
        string $content = "%PDF-1.4\n%SPRI Test Content\n%%EOF"
    ): array {
        $verification = Verification::factory()->create();

        $application = PerdimApplication::factory()->create(array_merge([
            'verification_id' => $verification->id,
            'birth_date' => '1995-08-17', // Dewasa (Kategori A)
            'marital_status' => 'kawin',
        ], $appOverrides));

        $filePath = "perdim_documents/{$verification->registration_code}/{$fileName}";
        Storage::disk('private')->put($filePath, $content);

        $document = PerdimDocument::factory()->create([
            'perdim_application_id' => $application->id,
            'document_type' => $docType,
            'original_name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => $mime,
            'file_size' => strlen($content),
            'hash_sha256' => hash('sha256', $content),
        ]);

        return [$verification, $application, $document];
    }

    public function test_unauthenticated_user_cannot_access_document_preview(): void
    {
        [$verification, , $document] = $this->createVerificationWithDocument();

        $response = $this->get(route('admin.verifications.documents.preview', [$verification, $document]));

        $response->assertRedirect(route('login'));
    }

    public function test_inactive_user_cannot_access_document_preview(): void
    {
        [$verification, , $document] = $this->createVerificationWithDocument();

        $inactiveUser = User::factory()->verifikator()->create(['is_active' => false]);

        $response = $this->actingAs($inactiveUser)->get(
            route('admin.verifications.documents.preview', [$verification, $document])
        );

        $response->assertStatus(403);
    }

    public function test_verifikator_can_view_secure_document_preview(): void
    {
        $fileContent = "%PDF-1.4\n%Verifikator Preview Test\n%%EOF";
        [$verification, , $document] = $this->createVerificationWithDocument(
            docType: 'ktp_pemohon',
            fileName: 'ktp_pemohon.pdf',
            mime: 'application/pdf',
            content: $fileContent
        );

        $verifikator = User::factory()->verifikator()->create();

        $response = $this->actingAs($verifikator)->get(
            route('admin.verifications.documents.preview', [$verification, $document])
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'inline; filename="ktp_pemohon.pdf"');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertEquals($fileContent, $response->streamedContent());
    }

    public function test_pimpinan_has_read_only_view_access_to_document_preview(): void
    {
        $fileContent = "%PDF-1.4\n%Pimpinan Read Only Preview\n%%EOF";
        [$verification, , $document] = $this->createVerificationWithDocument(
            docType: 'kartu_keluarga',
            fileName: 'kartu_keluarga.pdf',
            mime: 'application/pdf',
            content: $fileContent
        );

        $pimpinan = User::factory()->pimpinan()->create();

        $response = $this->actingAs($pimpinan)->get(
            route('admin.verifications.documents.preview', [$verification, $document])
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertEquals($fileContent, $response->streamedContent());
    }

    public function test_admin_can_view_document_preview(): void
    {
        $fileContent = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00`\x00`\x00\x00\xFF\xDB\x00C\x00";
        [$verification, , $document] = $this->createVerificationWithDocument(
            docType: 'paspor_lama',
            fileName: 'paspor_lama.jpg',
            mime: 'image/jpeg',
            content: $fileContent
        );

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(
            route('admin.verifications.documents.preview', [$verification, $document])
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
        $response->assertHeader('Content-Disposition', 'inline; filename="paspor_lama.jpg"');
        $this->assertEquals($fileContent, $response->streamedContent());
    }

    public function test_preview_fails_with_404_if_document_does_not_belong_to_verification(): void
    {
        // Berkas A
        [$verificationA] = $this->createVerificationWithDocument([], [], 'ktp_pemohon', 'ktp_a.pdf');

        // Berkas B dengan Dokumen B
        [, , $documentB] = $this->createVerificationWithDocument([], [], 'kartu_keluarga', 'kk_b.pdf');

        $verifikator = User::factory()->verifikator()->create();

        // Mencoba mengakses dokumen B melalui ID verifikasi A (IDOR attack scenario)
        $response = $this->actingAs($verifikator)->get(
            route('admin.verifications.documents.preview', [$verificationA, $documentB])
        );

        $response->assertStatus(404);
    }

    public function test_preview_fails_with_404_if_physical_file_missing_on_disk(): void
    {
        [$verification, , $document] = $this->createVerificationWithDocument();

        // Hapus berkas fisik dari storage privat
        Storage::disk('private')->delete($document->file_path);

        $verifikator = User::factory()->verifikator()->create();

        $response = $this->actingAs($verifikator)->get(
            route('admin.verifications.documents.preview', [$verification, $document])
        );

        $response->assertStatus(404);
    }

    public function test_admin_verification_show_displays_digital_documents_panel_with_adult_badge(): void
    {
        [$verification, $application, $document] = $this->createVerificationWithDocument(
            appOverrides: [
                'birth_date' => '1990-01-01',
                'marital_status' => 'kawin',
            ],
            docType: 'ktp_pemohon',
            fileName: 'ktp_pemohon_asli.pdf'
        );

        $verifikator = User::factory()->verifikator()->create();

        $response = $this->actingAs($verifikator)->get(
            route('admin.verifications.show', $verification)
        );

        $response->assertStatus(200);
        $response->assertSee('Dokumen Persyaratan Digital');
        $response->assertSee('Dewasa (Kategori A)');
        $response->assertSee('KTP Elektronik Pemohon');
        $response->assertSee('ktp_pemohon_asli.pdf');
        $response->assertSee(route('admin.verifications.documents.preview', [$verification, $document]));
        $response->assertSee('Pratinjau Berkas');
    }

    public function test_admin_verification_show_displays_digital_documents_panel_with_child_badge(): void
    {
        [$verification, $application, $document] = $this->createVerificationWithDocument(
            appOverrides: [
                'birth_date' => now()->subYears(10)->format('Y-m-d'), // 10 tahun (Anak)
                'marital_status' => 'tidak_kawin',
            ],
            docType: 'akta_kelahiran',
            fileName: 'akta_kelahiran_anak.pdf'
        );

        $verifikator = User::factory()->verifikator()->create();

        $response = $this->actingAs($verifikator)->get(
            route('admin.verifications.show', $verification)
        );

        $response->assertStatus(200);
        $response->assertSee('Dokumen Persyaratan Digital');
        $response->assertSee('Anak-Anak (Kategori B)');
        $response->assertSee('Akta Kelahiran');
        $response->assertSee('akta_kelahiran_anak.pdf');
    }

    public function test_admin_verification_show_displays_empty_state_when_no_documents_uploaded(): void
    {
        $verification = Verification::factory()->create();
        PerdimApplication::factory()->create([
            'verification_id' => $verification->id,
            'birth_date' => '1995-05-05',
            'marital_status' => 'tidak_kawin',
        ]);

        $verifikator = User::factory()->verifikator()->create();

        $response = $this->actingAs($verifikator)->get(
            route('admin.verifications.show', $verification)
        );

        $response->assertStatus(200);
        $response->assertSee('Dokumen Persyaratan Digital');
        $response->assertSee('Belum ada berkas digital yang diunggah untuk permohonan ini.');
    }
}
