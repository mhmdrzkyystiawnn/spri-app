<?php

namespace Tests\Unit;

use App\Models\PerdimApplication;
use App\Models\Verification;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerdimApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_perdim_application_via_factory(): void
    {
        $perdim = PerdimApplication::factory()->create([
            'full_name' => 'Ahmad Dahlan',
            'nik' => '3171012304900001',
            'application_type' => 'baru',
            'passport_type' => 'paspor_elektronik_48',
        ]);

        $this->assertDatabaseHas('perdim_applications', [
            'id' => $perdim->id,
            'full_name' => 'Ahmad Dahlan',
            'nik' => '3171012304900001',
            'application_type' => 'baru',
            'passport_type' => 'paspor_elektronik_48',
        ]);

        $this->assertInstanceOf(PerdimApplication::class, $perdim);
    }

    public function test_belongs_to_verification_relation(): void
    {
        $verification = Verification::factory()->create([
            'registration_code' => 'SPRI-2026-PERDIM01',
        ]);

        $perdim = PerdimApplication::factory()->create([
            'verification_id' => $verification->id,
        ]);

        $this->assertInstanceOf(Verification::class, $perdim->verification);
        $this->assertEquals($verification->id, $perdim->verification->id);
        $this->assertEquals('SPRI-2026-PERDIM01', $perdim->verification->registration_code);
    }

    public function test_verification_has_one_perdim_application_relation(): void
    {
        $verification = Verification::factory()->create();
        $perdim = PerdimApplication::factory()->create([
            'verification_id' => $verification->id,
            'full_name' => 'Siti Fatimah',
        ]);

        $this->assertInstanceOf(PerdimApplication::class, $verification->perdimApplication);
        $this->assertEquals($perdim->id, $verification->perdimApplication->id);
        $this->assertEquals('Siti Fatimah', $verification->perdimApplication->full_name);
    }

    public function test_casts_dates_and_attributes_properly(): void
    {
        $perdim = PerdimApplication::factory()->create([
            'birth_date' => '1995-05-12',
            'identity_issued_date' => '2020-01-15',
            'identity_expiry_date' => '2025-01-15',
            'mother_birth_date' => '1965-08-20',
            'father_birth_date' => '1960-03-10',
            'spouse_birth_date' => '1997-11-25',
            'declaration_accepted_at' => '2026-11-13 10:00:00',
            'declaration_accepted' => 1,
            'height_cm' => '172',
            'change_details' => [
                [
                    'field' => 'Nama Pemohon',
                    'old_value' => 'Nama Lama',
                    'new_value' => 'Nama Baru',
                    'reason' => 'Penetapan Pengadilan',
                ],
            ],
        ]);

        // Verifikasi Carbon dates
        $this->assertInstanceOf(CarbonInterface::class, $perdim->birth_date);
        $this->assertEquals('1995-05-12', $perdim->birth_date->format('Y-m-d'));

        $this->assertInstanceOf(CarbonInterface::class, $perdim->identity_issued_date);
        $this->assertEquals('2020-01-15', $perdim->identity_issued_date->format('Y-m-d'));

        $this->assertInstanceOf(CarbonInterface::class, $perdim->identity_expiry_date);
        $this->assertEquals('2025-01-15', $perdim->identity_expiry_date->format('Y-m-d'));

        $this->assertInstanceOf(CarbonInterface::class, $perdim->mother_birth_date);
        $this->assertEquals('1965-08-20', $perdim->mother_birth_date->format('Y-m-d'));

        $this->assertInstanceOf(CarbonInterface::class, $perdim->father_birth_date);
        $this->assertEquals('1960-03-10', $perdim->father_birth_date->format('Y-m-d'));

        $this->assertInstanceOf(CarbonInterface::class, $perdim->spouse_birth_date);
        $this->assertEquals('1997-11-25', $perdim->spouse_birth_date->format('Y-m-d'));

        $this->assertInstanceOf(CarbonInterface::class, $perdim->declaration_accepted_at);

        // Verifikasi tipe data boolean, integer, dan array
        $this->assertIsBool($perdim->declaration_accepted);
        $this->assertTrue($perdim->declaration_accepted);

        $this->assertIsInt($perdim->height_cm);
        $this->assertSame(172, $perdim->height_cm);

        $this->assertIsArray($perdim->change_details);
        $this->assertEquals('Nama Pemohon', $perdim->change_details[0]['field']);
    }

    public function test_cascade_delete_when_verification_deleted(): void
    {
        $verification = Verification::factory()->create();
        $perdim = PerdimApplication::factory()->create([
            'verification_id' => $verification->id,
        ]);

        $this->assertDatabaseHas('perdim_applications', ['id' => $perdim->id]);

        // Bersihkan logs agar tidak memicu FK pada observer/tabel lain saat force delete
        $verification->auditLogs()->delete();
        $verification->statusHistories()->delete();

        // Parent force delete murni tanpa event observer harus memicu DB cascade delete pada child
        Verification::withoutEvents(function () use ($verification) {
            $verification->forceDelete();
        });

        $this->assertDatabaseMissing('perdim_applications', ['id' => $perdim->id]);
    }

    public function test_factory_states(): void
    {
        // State replacement
        $replacement = PerdimApplication::factory()->replacement('halaman_penuh')->create();
        $this->assertEquals('penggantian', $replacement->application_type);
        $this->assertEquals('halaman_penuh', $replacement->replacement_reason);

        // State change
        $change = PerdimApplication::factory()->change('perubahan_nama')->create();
        $this->assertEquals('perubahan', $change->application_type);
        $this->assertEquals('perubahan_nama', $change->change_reason);
        $this->assertIsArray($change->change_details);

        // State withOverseasData
        $overseas = PerdimApplication::factory()->withOverseasData()->create();
        $this->assertNotNull($overseas->overseas_address);
        $this->assertNotNull($overseas->overseas_phone);
        $this->assertNotNull($overseas->emergency_overseas_name);
        $this->assertNotNull($overseas->emergency_overseas_address);
        $this->assertNotNull($overseas->emergency_overseas_phone);
    }

    public function test_unique_verification_id_constraint(): void
    {
        $verification = Verification::factory()->create();

        PerdimApplication::factory()->create([
            'verification_id' => $verification->id,
        ]);

        $this->expectException(QueryException::class);

        // Mencoba membuat record PERDIM kedua untuk verifikasi yang sama harus gagal
        PerdimApplication::factory()->create([
            'verification_id' => $verification->id,
        ]);
    }
}
