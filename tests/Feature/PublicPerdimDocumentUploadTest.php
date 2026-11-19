<?php

namespace Tests\Feature;

use App\Models\PerdimApplication;
use App\Models\PerdimDocument;
use App\Models\Verification;
use App\Services\PerdimDocumentStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicPerdimDocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    /**
     * Helper untuk membuat berkas dummy dengan magic bytes yang valid.
     */
    protected function createValidFile(string $name, string $type, int $sizeBytes = 2048): UploadedFile
    {
        $header = match ($type) {
            'jpg', 'jpeg' => "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00`\x00`\x00\x00",
            'png' => "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR",
            'pdf' => "%PDF-1.4\n%âãÏÓ\n",
            default => 'DUMMY_CONTENT',
        };

        $content = $header.str_repeat('X', max(0, $sizeBytes - strlen($header)));

        return UploadedFile::fake()->createWithContent($name, $content);
    }

    public function test_can_upload_valid_jpg_document_successfully(): void
    {
        $file = $this->createValidFile('ktp_pemohon.jpg', 'jpg', 102400); // 100 KB

        $response = $this->postJson(route('public.perdim.upload-temp'), [
            'document_type' => 'ktp_pemohon',
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Berkas berhasil diunggah sementara.',
                'data' => [
                    'document_type' => 'ktp_pemohon',
                    'original_name' => 'ktp_pemohon.jpg',
                ],
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['token']);
        $this->assertNotEmpty($data['hash_sha256']);
        $this->assertEquals(64, strlen($data['hash_sha256']));
        $this->assertArrayNotHasKey('file_path', $data); // Pastikan filesystem path tidak diekspos
    }

    public function test_can_upload_valid_png_document_successfully(): void
    {
        $file = $this->createValidFile('kartu_keluarga.png', 'png', 150000); // 150 KB

        $response = $this->postJson(route('public.perdim.upload-temp'), [
            'document_type' => 'kartu_keluarga',
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'document_type' => 'kartu_keluarga',
                    'original_name' => 'kartu_keluarga.png',
                ],
            ]);
    }

    public function test_can_upload_valid_pdf_document_successfully(): void
    {
        $file = $this->createValidFile('akta_kelahiran.pdf', 'pdf', 300000); // 300 KB

        $response = $this->postJson(route('public.perdim.upload-temp'), [
            'document_type' => 'akta_kelahiran',
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'document_type' => 'akta_kelahiran',
                    'original_name' => 'akta_kelahiran.pdf',
                ],
            ]);
    }

    public function test_upload_fails_when_file_exceeds_2mb(): void
    {
        // 2.5 MB (melebihi limit 2 MB = 2048 KB)
        $file = $this->createValidFile('dokumen_besar.pdf', 'pdf', 2600 * 1024);

        $response = $this->postJson(route('public.perdim.upload-temp'), [
            'document_type' => 'akta_kelahiran',
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_fails_when_file_is_empty_zero_bytes(): void
    {
        $file = UploadedFile::fake()->createWithContent('kosong.pdf', '');

        $response = $this->postJson(route('public.perdim.upload-temp'), [
            'document_type' => 'akta_kelahiran',
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_fails_when_magic_bytes_mismatch_extension_spoofing(): void
    {
        // Berkas teks biasa yang sengaja dinamai .pdf
        $file = UploadedFile::fake()->createWithContent('palsu.pdf', 'Ini bukan berkas PDF asli');

        $response = $this->postJson(route('public.perdim.upload-temp'), [
            'document_type' => 'akta_kelahiran',
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_fails_when_extension_not_in_allowlist(): void
    {
        $file = UploadedFile::fake()->create('skrip_berbahaya.sh', 50, 'application/x-sh');

        $response = $this->postJson(route('public.perdim.upload-temp'), [
            'document_type' => 'akta_kelahiran',
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_upload_fails_when_document_type_is_missing(): void
    {
        $file = $this->createValidFile('ktp.jpg', 'jpg', 50000);

        $response = $this->postJson(route('public.perdim.upload-temp'), [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['document_type']);
    }

    public function test_can_delete_temp_upload_using_token(): void
    {
        $file = $this->createValidFile('hapus_saya.pdf', 'pdf', 50000);

        $uploadResponse = $this->postJson(route('public.perdim.upload-temp'), [
            'document_type' => 'paspor_lama',
            'file' => $file,
        ]);

        $uploadResponse->assertStatus(200);
        $token = $uploadResponse->json('data.token');

        $deleteResponse = $this->deleteJson(route('public.perdim.delete-temp'), [
            'token' => $token,
        ]);

        $deleteResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Berkas sementara berhasil dihapus.',
            ]);
    }

    public function test_delete_temp_upload_fails_with_invalid_token(): void
    {
        $response = $this->deleteJson(route('public.perdim.delete-temp'), [
            'token' => 'invalid-uuid-token',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Berkas sementara tidak ditemukan.',
            ]);
    }

    public function test_finalize_documents_moves_files_and_creates_records(): void
    {
        $storageService = app(PerdimDocumentStorageService::class);

        // Upload 2 dokumen sementara
        $file1 = $this->createValidFile('ktp.jpg', 'jpg', 50000);
        $file2 = $this->createValidFile('kk.pdf', 'pdf', 80000);

        $upload1 = $this->postJson(route('public.perdim.upload-temp'), [
            'document_type' => 'ktp_pemohon',
            'file' => $file1,
        ]);
        $upload2 = $this->postJson(route('public.perdim.upload-temp'), [
            'document_type' => 'kartu_keluarga',
            'file' => $file2,
        ]);

        $token1 = $upload1->json('data.token');
        $token2 = $upload2->json('data.token');

        // Buat data Verification & PerdimApplication
        $verification = Verification::factory()->create([
            'registration_code' => 'SPRI-2026-FIN001',
        ]);
        $application = PerdimApplication::factory()->create([
            'verification_id' => $verification->id,
        ]);

        // Finalisasi berkas
        $finalized = $storageService->finalizeDocuments($application, [$token1, $token2], session()->getId());

        $this->assertCount(2, $finalized);
        $this->assertDatabaseHas('perdim_documents', [
            'perdim_application_id' => $application->id,
            'document_type' => 'ktp_pemohon',
            'original_name' => 'ktp.jpg',
        ]);
        $this->assertDatabaseHas('perdim_documents', [
            'perdim_application_id' => $application->id,
            'document_type' => 'kartu_keluarga',
            'original_name' => 'kk.pdf',
        ]);

        // Verifikasi relasi Eloquent
        $this->assertCount(2, $application->refresh()->documents);
        $doc = $application->documents->first();
        $this->assertStringContainsString('SPRI-2026-FIN001', $doc->file_path);
        $this->assertTrue(Storage::disk('private')->exists($doc->file_path));
    }

    public function test_cascade_delete_removes_perdim_documents_when_application_is_deleted(): void
    {
        $verification = Verification::factory()->create();
        $application = PerdimApplication::factory()->create([
            'verification_id' => $verification->id,
        ]);

        PerdimDocument::factory()->create([
            'perdim_application_id' => $application->id,
            'document_type' => 'ktp_pemohon',
        ]);
        PerdimDocument::factory()->create([
            'perdim_application_id' => $application->id,
            'document_type' => 'kartu_keluarga',
        ]);

        $this->assertEquals(2, $application->documents()->count());

        // Hapus perdim application
        $application->delete();

        // Pastikan dokumen anak terhapus otomatis via cascade constraint
        $this->assertEquals(0, PerdimDocument::where('perdim_application_id', $application->id)->count());
    }
}
