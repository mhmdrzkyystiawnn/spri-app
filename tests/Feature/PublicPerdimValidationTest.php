<?php

namespace Tests\Feature;

use App\Models\PerdimApplication;
use App\Services\PerdimDocumentClassificationService;
use App\Services\PerdimDocumentStorageService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicPerdimValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    /**
     * Payload dasar yang valid untuk pengujian.
     *
     * @param  array<string, mixed>  $overrides
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

            // Seksi 2: Identitas Diri Pemohon (Laki-laki, Lahir 17 Agustus 1995)
            'full_name' => 'Ahmad Fauzi',
            'alias_name' => null,
            'gender' => 'L',
            'height_cm' => 170,
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-08-17',
            'nik' => '3171011708950001',
            'identity_number' => null,
            'identity_issued_place' => 'Jakarta Pusat',
            'identity_issued_date' => '2020-01-15',
            'identity_expiry_date' => null,
            'occupation' => 'Pegawai Swasta',
            'workplace_address' => 'Jl. Thamrin No. 1, Jakarta Pusat',
            'office_phone' => '0213901234',
            'indonesia_address' => 'Jl. Kebon Kacang No. 15, Tanah Abang, Jakarta Pusat',
            'indonesia_phone' => '081234567890',
            'overseas_address' => null,
            'overseas_phone' => null,
            'email' => 'ahmad.fauzi@example.com',
            'marital_status' => 'kawin',

            // Seksi 3: Data Keluarga
            'mother_name' => 'Siti Nurhaliza',
            'mother_nationality' => 'WNI',
            'mother_birth_place' => 'Bogor',
            'mother_birth_date' => '1970-05-10',

            'father_name' => 'Fauzi Abdullah',
            'father_nationality' => 'WNI',
            'father_birth_place' => 'Jakarta',
            'father_birth_date' => '1967-03-12',
            'father_address' => 'Jl. Kebon Kacang No. 15, Jakarta Pusat',
            'father_phone' => '081299887766',

            'spouse_name' => 'Rina Marlina',
            'spouse_nationality' => 'WNI',
            'spouse_birth_place' => 'Bandung',
            'spouse_birth_date' => '1996-09-20',
            'spouse_address' => 'Jl. Kebon Kacang No. 15, Jakarta Pusat',

            // Seksi 4: Kontak Darurat & Pernyataan
            'change_details' => null,
            'emergency_id_name' => 'Bambang Fauzi',
            'emergency_id_relationship' => 'Kakak Kandung',
            'emergency_id_address' => 'Jl. Cempaka Putih No. 4, Jakarta Pusat',
            'emergency_id_phone' => '081388776655',
            'emergency_overseas_name' => null,
            'emergency_overseas_address' => null,
            'emergency_overseas_phone' => null,

            'declaration_accepted' => '1',
        ], $overrides);
    }

    /**
     * Helper untuk mengunggah dummy file ke temp storage dan mengembalikan tokennya.
     */
    protected function createTempDocToken(string $docType): string
    {
        $header = "%PDF-1.4\n%test\n";
        $file = UploadedFile::fake()->createWithContent("{$docType}.pdf", $header.str_repeat('A', 1024));
        $storage = app(PerdimDocumentStorageService::class);
        $res = $storage->storeTemp($file, $docType, 'test-session-id');

        return $res['token'];
    }

    public function test_valid_male_nik_passes_validation(): void
    {
        $payload = $this->validPayload([
            'gender' => 'L',
            'birth_date' => '1995-08-17',
            'nik' => '3171011708950001',
        ]);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_valid_female_nik_with_plus_40_passes_validation(): void
    {
        $payload = $this->validPayload([
            'full_name' => 'Siti Fatimah',
            'gender' => 'P',
            'birth_date' => '1998-11-25',
            // 25 + 40 = 65 -> 651198
            'nik' => '3201016511980002',
            'spouse_name' => 'Fajar Santoso',
            'spouse_nationality' => 'WNI',
        ]);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_nik_rejected_when_not_16_digits(): void
    {
        $payload = $this->validPayload(['nik' => '31710117089500']); // 14 digit

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);
    }

    public function test_nik_rejected_when_containing_non_numeric(): void
    {
        $payload = $this->validPayload(['nik' => '317101170895000A']);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);
    }

    public function test_nik_rejected_when_month_segment_is_invalid(): void
    {
        $payload = $this->validPayload(['nik' => '3171011713950001']); // Bulan 13

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);
    }

    public function test_nik_rejected_when_female_nik_used_for_male_applicant(): void
    {
        // Tanggal 17 Agustus 1995, tapi dimasukkan 57 (seperti wanita) untuk pemohon pria
        $payload = $this->validPayload([
            'gender' => 'L',
            'birth_date' => '1995-08-17',
            'nik' => '3171015708950001',
        ]);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);
    }

    public function test_nik_rejected_when_male_nik_used_for_female_applicant(): void
    {
        // Tanggal 17 Agustus 1995, pemohon wanita tapi NIK tidak ditambah 40 (17 bukan 57)
        $payload = $this->validPayload([
            'gender' => 'P',
            'birth_date' => '1995-08-17',
            'nik' => '3171011708950001',
        ]);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);
    }

    public function test_nik_rejected_when_birth_date_segment_does_not_match_dob(): void
    {
        // Tanggal lahir di form 1995-08-17, di NIK 180895
        $payload = $this->validPayload([
            'gender' => 'L',
            'birth_date' => '1995-08-17',
            'nik' => '3171011808950001',
        ]);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nik']);
    }

    public function test_anomalous_email_ndsanknsad_is_rejected(): void
    {
        $payload = $this->validPayload(['email' => 'ndsanknsad']);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_anomalous_phone_aaaaa_is_rejected(): void
    {
        $payload = $this->validPayload(['indonesia_phone' => 'aaaaa']);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['indonesia_phone']);
    }

    public function test_identity_issued_date_before_birth_date_is_rejected(): void
    {
        $payload = $this->validPayload([
            'birth_date' => '1995-08-17',
            'nik' => '3171011708950001',
            'identity_issued_date' => '1990-01-01', // 5 tahun sebelum lahir
        ]);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['identity_issued_date']);
    }

    public function test_identity_issued_date_in_future_is_rejected(): void
    {
        $payload = $this->validPayload([
            'identity_issued_date' => now()->addYear()->format('Y-m-d'),
        ]);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['identity_issued_date']);
    }

    public function test_height_cm_boundary_validation(): void
    {
        // Terlalu pendek (< 30 cm)
        $responseLow = $this->postJson(route('public.perdim.store'), $this->validPayload(['height_cm' => 25]));
        $responseLow->assertStatus(422)->assertJsonValidationErrors(['height_cm']);

        // Terlalu tinggi (> 250 cm)
        $responseHigh = $this->postJson(route('public.perdim.store'), $this->validPayload(['height_cm' => 255]));
        $responseHigh->assertStatus(422)->assertJsonValidationErrors(['height_cm']);

        // Batas bawah pas (30 cm)
        $responseExactLow = $this->postJson(route('public.perdim.store'), $this->validPayload(['height_cm' => 30]));
        $responseExactLow->assertStatus(200);

        // Batas atas pas (250 cm)
        $responseExactHigh = $this->postJson(route('public.perdim.store'), $this->validPayload(['height_cm' => 250]));
        $responseExactHigh->assertStatus(200);
    }

    public function test_anomalous_address_aknakna_is_rejected_too_short(): void
    {
        $payload = $this->validPayload(['indonesia_address' => 'aknakna']); // 7 karakter (< 10)

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['indonesia_address']);
    }

    public function test_compatibility_belum_kawin_normalized_to_tidak_kawin(): void
    {
        $payload = $this->validPayload([
            'marital_status' => 'belum_kawin',
            'spouse_name' => null,
            'spouse_nationality' => null,
        ]);

        $response = $this->postJson(route('public.perdim.store'), $payload);

        $response->assertStatus(200);

        $application = PerdimApplication::latest('id')->first();
        $this->assertEquals('tidak_kawin', $application->marital_status);
    }

    public function test_classification_service_determines_category_a_and_b_correctly(): void
    {
        $service = app(PerdimDocumentClassificationService::class);

        // Usia 25 tahun, belum kawin -> Kategori A (Dewasa)
        $this->assertEquals(
            PerdimDocumentClassificationService::CATEGORY_ADULT,
            $service->determineCategory(Carbon::now()->subYears(25), 'tidak_kawin')
        );

        // Usia 16 tahun, kawin -> Kategori A (Dewasa)
        $this->assertEquals(
            PerdimDocumentClassificationService::CATEGORY_ADULT,
            $service->determineCategory(Carbon::now()->subYears(16), 'kawin')
        );

        // Usia 15 tahun, belum kawin -> Kategori B (Anak)
        $this->assertEquals(
            PerdimDocumentClassificationService::CATEGORY_CHILD,
            $service->determineCategory(Carbon::now()->subYears(15), 'tidak_kawin')
        );

        // Usia 15 tahun, status 'belum_kawin' (alias) -> Kategori B (Anak)
        $this->assertEquals(
            PerdimDocumentClassificationService::CATEGORY_CHILD,
            $service->determineCategory(Carbon::now()->subYears(15), 'belum_kawin')
        );
    }

    public function test_category_a_document_validation_enforced(): void
    {
        // Pemohon dewasa tanpa dokumen KTP Pemohon -> error
        $tokenKk = $this->createTempDocToken('kartu_keluarga');
        $tokenAkta = $this->createTempDocToken('akta_kelahiran');

        $payload = $this->validPayload([
            'document_tokens' => [$tokenKk, $tokenAkta], // kurang ktp_pemohon
        ]);

        $response = $this->postJson(route('public.perdim.store'), $payload);
        $response->assertStatus(422)->assertJsonValidationErrors(['document_tokens']);

        // Pemohon dewasa dengan dokumen lengkap (KTP, KK, Akta) -> sukses
        $tokenKtp = $this->createTempDocToken('ktp_pemohon');
        $payloadComplete = $this->validPayload([
            'document_tokens' => [$tokenKtp, $tokenKk, $tokenAkta],
        ]);

        $responseComplete = $this->postJson(route('public.perdim.store'), $payloadComplete);
        $responseComplete->assertStatus(200);
    }

    public function test_category_b_document_validation_enforced_including_special_cases(): void
    {
        // Buat pemohon anak (usia 10 tahun, lahir 10 Agustus 2016)
        $birthDate = Carbon::now()->subYears(10)->format('Y-m-d');
        $dobCarbon = Carbon::parse($birthDate);
        $nik = sprintf('317101%s%s%s0001', $dobCarbon->format('d'), $dobCarbon->format('m'), $dobCarbon->format('y'));

        $childPayload = $this->validPayload([
            'birth_date' => $birthDate,
            'nik' => $nik,
            'marital_status' => 'belum_kawin',
            'spouse_name' => null,
            'spouse_nationality' => null,
            'height_cm' => 125,
            'occupation' => 'Pelajar',
        ]);

        // 1. Dokumen Anak tidak lengkap (hanya KK dan Akta, tidak ada identitas orang tua/buku nikah)
        $tokenKk = $this->createTempDocToken('kartu_keluarga');
        $tokenAkta = $this->createTempDocToken('akta_kelahiran');

        $resIncomplete = $this->postJson(route('public.perdim.store'), array_merge($childPayload, [
            'document_tokens' => [$tokenKk, $tokenAkta],
        ]));
        $resIncomplete->assertStatus(422)->assertJsonValidationErrors(['document_tokens']);

        // 2. Dokumen Anak normal lengkap (KK, Akta, KTP Ayah, Buku Nikah Ortu)
        $tokenKtpAyah = $this->createTempDocToken('ktp_ayah');
        $tokenBukuNikahOrtu = $this->createTempDocToken('buku_nikah_orang_tua');

        $resNormal = $this->postJson(route('public.perdim.store'), array_merge($childPayload, [
            'document_tokens' => [$tokenKk, $tokenAkta, $tokenKtpAyah, $tokenBukuNikahOrtu],
        ]));
        $resNormal->assertStatus(200);

        // 3. Kasus Khusus Orang Tua Meninggal (KK, Akta, Akta Kematian Orang Tua)
        $tokenKk2 = $this->createTempDocToken('kartu_keluarga');
        $tokenAkta2 = $this->createTempDocToken('akta_kelahiran');
        $tokenAktaKematian = $this->createTempDocToken('akta_kematian_orang_tua');
        $resDeceased = $this->postJson(route('public.perdim.store'), array_merge($childPayload, [
            'document_tokens' => [$tokenKk2, $tokenAkta2, $tokenAktaKematian],
        ]));
        $resDeceased->assertStatus(200);

        // 4. Kasus Khusus Perwalian (KK, Akta, KTP Wali, Penetapan Pengadilan Wali)
        $tokenKk3 = $this->createTempDocToken('kartu_keluarga');
        $tokenAkta3 = $this->createTempDocToken('akta_kelahiran');
        $tokenKtpWali = $this->createTempDocToken('ktp_wali');
        $tokenPenetapanWali = $this->createTempDocToken('penetapan_pengadilan_wali');

        $resWali = $this->postJson(route('public.perdim.store'), array_merge($childPayload, [
            'document_tokens' => [$tokenKk3, $tokenAkta3, $tokenKtpWali, $tokenPenetapanWali],
        ]));
        $resWali->assertStatus(200);
    }
}
