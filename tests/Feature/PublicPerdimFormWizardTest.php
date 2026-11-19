<?php

namespace Tests\Feature;

use App\Services\PerdimDocumentStorageService;
use Database\Seeders\DocumentRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PublicPerdimFormWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DocumentRequirementSeeder::class);
    }

    /**
     * Helper untuk membuat token berkas sementara valid.
     */
    protected function createTempDocToken(string $docType): string
    {
        $storage = app(PerdimDocumentStorageService::class);
        $header = "%PDF-1.4\n%test\n";
        $file = UploadedFile::fake()->createWithContent("{$docType}.pdf", $header.str_repeat('A', 1024));
        $stored = $storage->storeTemp($file, $docType, session()->getId());

        return $stored['token'];
    }

    public function test_form_displays_six_step_navigation_elements(): void
    {
        $response = $this->get(route('public.perdim.create'));

        $response->assertStatus(200);
        $response->assertSee('Langkah 1');
        $response->assertSee('Jenis SPRI');
        $response->assertSee('Langkah 2');
        $response->assertSee('Identitas');
        $response->assertSee('Langkah 3');
        $response->assertSee('Keluarga');
        $response->assertSee('Langkah 4');
        $response->assertSee('Darurat');
        $response->assertSee('Langkah 5');
        $response->assertSee('Dokumen');
        $response->assertSee('Langkah 6');
        $response->assertSee('Pratinjau');
    }

    public function test_form_contains_reactive_dropzone_and_declaration_containers(): void
    {
        $response = $this->get(route('public.perdim.create'));

        $response->assertStatus(200);
        // Step 5 Containers
        $response->assertSee('id="step-pane-5"', false);
        $response->assertSee('Dokumen Persyaratan Digital');
        $response->assertSee('id="category-banner-adult"', false);
        $response->assertSee('id="category-banner-child"', false);
        $response->assertSee('data-doc-type="ktp_pemohon"', false);
        $response->assertSee('data-doc-type="kartu_keluarga"', false);
        $response->assertSee('data-doc-type="akta_kelahiran"', false);
        $response->assertSee('data-doc-type="buku_nikah_pemohon"', false);
        $response->assertSee('data-doc-type="paspor_lama"', false);
        $response->assertSee('data-doc-type="ktp_ayah"', false);
        $response->assertSee('data-doc-type="ktp_ibu"', false);
        $response->assertSee('data-doc-type="buku_nikah_orang_tua"', false);

        // Step 6 Containers
        $response->assertSee('id="step-pane-6"', false);
        $response->assertSee('id="review-documents-list"', false);
        $response->assertSee('id="declaration-clause-text"', false);
        $response->assertSee('name="declaration_accepted"', false);
    }

    public function test_successful_application_submission_with_uploaded_documents_from_wizard(): void
    {
        $tokenKtp = $this->createTempDocToken('ktp_pemohon');
        $tokenKk = $this->createTempDocToken('kartu_keluarga');
        $tokenAkta = $this->createTempDocToken('akta_kelahiran');

        $payload = [
            'application_type' => 'baru',
            'passport_type' => 'paspor_biasa_48',
            'full_name' => 'Aditya Pratama',
            'alias_name' => null,
            'gender' => 'L',
            'height_cm' => 175,
            'birth_place' => 'Surabaya',
            'birth_date' => '1998-05-12',
            'nik' => '3578011205980002',
            'identity_number' => null,
            'identity_issued_place' => 'Surabaya',
            'identity_issued_date' => '2020-06-01',
            'identity_expiry_date' => null,
            'occupation' => 'Teknisi Perangkat Lunak',
            'indonesia_address' => 'Jl. Pemuda No. 10, Surabaya',
            'indonesia_phone' => '081234567891',
            'email' => 'aditya.pratama@example.com',
            'marital_status' => 'tidak_kawin',
            'mother_name' => 'Endang Rahayu',
            'mother_nationality' => 'WNI',
            'mother_birth_place' => 'Surabaya',
            'mother_birth_date' => '1970-03-15',
            'father_name' => 'Bambang Pratama',
            'father_nationality' => 'WNI',
            'father_birth_place' => 'Malang',
            'father_birth_date' => '1967-11-20',
            'father_address' => 'Jl. Pemuda No. 10, Surabaya',
            'father_phone' => '081398765431',
            'emergency_id_name' => 'Citra Pratama',
            'emergency_id_relationship' => 'Saudari Kandung',
            'emergency_id_address' => 'Jl. Pemuda No. 10, Surabaya',
            'emergency_id_phone' => '081299887766',
            'declaration_accepted' => '1',
            'document_tokens' => [$tokenKtp, $tokenKk, $tokenAkta],
            'document_types' => ['ktp_pemohon', 'kartu_keluarga', 'akta_kelahiran'],
        ];

        $response = $this->post(route('public.perdim.store'), $payload);

        $response->assertRedirect(route('public.perdim.receipt'));
        $this->assertDatabaseHas('perdim_applications', [
            'full_name' => 'Aditya Pratama',
            'nik' => '3578011205980002',
        ]);

        $this->assertDatabaseHas('perdim_documents', [
            'document_type' => 'ktp_pemohon',
        ]);
        $this->assertDatabaseHas('perdim_documents', [
            'document_type' => 'kartu_keluarga',
        ]);
        $this->assertDatabaseHas('perdim_documents', [
            'document_type' => 'akta_kelahiran',
        ]);
    }
}
