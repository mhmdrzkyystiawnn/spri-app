<?php

namespace Tests\Unit\Queries;

use App\Enums\VerificationStatus;
use App\Models\DocumentRequirement;
use App\Models\Verification;
use App\Models\VerificationChecklist;
use App\Queries\VerificationQueryFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationQueryFilterTest extends TestCase
{
    use RefreshDatabase;

    private VerificationQueryFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new VerificationQueryFilter();
    }

    public function test_empty_filters_returns_query_unmodified(): void
    {
        Verification::factory()->create(['applicant_name' => 'Pemohon Satu']);
        Verification::factory()->create(['applicant_name' => 'Pemohon Dua']);

        $query = Verification::query();
        $filtered = $this->filter->apply($query, []);

        $this->assertCount(2, $filtered->get());

        // Test with empty/null/whitespace inputs
        $query2 = Verification::query();
        $filtered2 = $this->filter->apply($query2, [
            'search' => '   ',
            'status' => null,
            'document_type' => '',
            'date_from' => null,
            'date_to' => '',
            'completeness' => null,
        ]);

        $this->assertCount(2, $filtered2->get());
    }

    public function test_filter_search_by_registration_code(): void
    {
        $v1 = Verification::factory()->create(['registration_code' => 'SPRI-202611-0001']);
        $v2 = Verification::factory()->create(['registration_code' => 'SPRI-202611-0002']);

        $results = $this->filter->apply(Verification::query(), ['search' => '0001'])->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($v1));
        $this->assertFalse($results->contains($v2));
    }

    public function test_filter_search_by_applicant_name(): void
    {
        $v1 = Verification::factory()->create(['applicant_name' => 'Budi Hartono']);
        $v2 = Verification::factory()->create(['applicant_name' => 'Siti Dewi Sartika']);

        $results = $this->filter->apply(Verification::query(), ['search' => 'Hartono'])->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($v1));
        $this->assertFalse($results->contains($v2));
    }

    public function test_filter_by_status(): void
    {
        $v1 = Verification::factory()->create(['status' => VerificationStatus::PENDING]);
        $v2 = Verification::factory()->create(['status' => VerificationStatus::APPROVED]);
        $v3 = Verification::factory()->create(['status' => VerificationStatus::REJECTED]);

        $results = $this->filter->apply(Verification::query(), ['status' => 'approved'])->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($v2));
    }

    public function test_filter_by_document_type(): void
    {
        $v1 = Verification::factory()->create(['document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)']);
        $v2 = Verification::factory()->create(['document_type' => 'Penggantian SPRI Halaman Penuh']);

        $results = $this->filter->apply(Verification::query(), [
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($v1));
        $this->assertFalse($results->contains($v2));
    }

    public function test_filter_by_date_range_both_bounds(): void
    {
        $v1 = Verification::factory()->create(['created_at' => '2026-11-01 10:00:00']);
        $v2 = Verification::factory()->create(['created_at' => '2026-11-05 14:30:00']);
        $v3 = Verification::factory()->create(['created_at' => '2026-11-10 18:00:00']);

        $results = $this->filter->apply(Verification::query(), [
            'date_from' => '2026-11-03',
            'date_to' => '2026-11-07',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($v2));
    }

    public function test_filter_by_date_range_single_day(): void
    {
        $v1 = Verification::factory()->create(['created_at' => '2026-11-05 08:15:00']);
        $v2 = Verification::factory()->create(['created_at' => '2026-11-05 23:45:00']);
        $v3 = Verification::factory()->create(['created_at' => '2026-11-06 00:01:00']);

        $results = $this->filter->apply(Verification::query(), [
            'date_from' => '2026-11-05',
            'date_to' => '2026-11-05',
        ])->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($v1));
        $this->assertTrue($results->contains($v2));
        $this->assertFalse($results->contains($v3));
    }

    public function test_filter_by_date_range_from_only(): void
    {
        $v1 = Verification::factory()->create(['created_at' => '2026-11-01 10:00:00']);
        $v2 = Verification::factory()->create(['created_at' => '2026-11-05 14:30:00']);
        $v3 = Verification::factory()->create(['created_at' => '2026-11-10 18:00:00']);

        $results = $this->filter->apply(Verification::query(), [
            'date_from' => '2026-11-05',
        ])->get();

        $this->assertCount(2, $results);
        $this->assertFalse($results->contains($v1));
        $this->assertTrue($results->contains($v2));
        $this->assertTrue($results->contains($v3));
    }

    public function test_filter_by_date_range_to_only(): void
    {
        $v1 = Verification::factory()->create(['created_at' => '2026-11-01 10:00:00']);
        $v2 = Verification::factory()->create(['created_at' => '2026-11-05 14:30:00']);
        $v3 = Verification::factory()->create(['created_at' => '2026-11-10 18:00:00']);

        $results = $this->filter->apply(Verification::query(), [
            'date_to' => '2026-11-05',
        ])->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($v1));
        $this->assertTrue($results->contains($v2));
        $this->assertFalse($results->contains($v3));
    }

    public function test_filter_completeness_complete_vs_incomplete(): void
    {
        $docType = 'SPRI Baru (Paspor Biasa 48 Hal)';

        $reqMandatory1 = DocumentRequirement::create([
            'document_type' => $docType,
            'code' => 'REQ-M-1',
            'name' => 'KTP Asli',
            'is_mandatory' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $reqMandatory2 = DocumentRequirement::create([
            'document_type' => $docType,
            'code' => 'REQ-M-2',
            'name' => 'Kartu Keluarga',
            'is_mandatory' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $reqOptional = DocumentRequirement::create([
            'document_type' => $docType,
            'code' => 'REQ-OPT-1',
            'name' => 'Surat Rekomendasi',
            'is_mandatory' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Dossier A: Lengkap (semua wajib tercentang, opsional belum tercentang tetap lengkap)
        $dossierA = Verification::factory()->create(['document_type' => $docType]);
        VerificationChecklist::create([
            'verification_id' => $dossierA->id,
            'document_requirement_id' => $reqMandatory1->id,
            'is_checked' => true,
        ]);
        VerificationChecklist::create([
            'verification_id' => $dossierA->id,
            'document_requirement_id' => $reqMandatory2->id,
            'is_checked' => true,
        ]);
        VerificationChecklist::create([
            'verification_id' => $dossierA->id,
            'document_requirement_id' => $reqOptional->id,
            'is_checked' => false,
        ]);

        // Dossier B: Belum Lengkap (ada syarat wajib yang belum tercentang)
        $dossierB = Verification::factory()->create(['document_type' => $docType]);
        VerificationChecklist::create([
            'verification_id' => $dossierB->id,
            'document_requirement_id' => $reqMandatory1->id,
            'is_checked' => true,
        ]);
        VerificationChecklist::create([
            'verification_id' => $dossierB->id,
            'document_requirement_id' => $reqMandatory2->id,
            'is_checked' => false,
        ]);

        // Dossier C: Belum Lengkap (tidak memiliki checklist sama sekali / legacy)
        $dossierC = Verification::factory()->create(['document_type' => $docType]);

        // Uji filter 'complete'
        $completeResults = $this->filter->apply(Verification::query(), [
            'completeness' => 'complete',
        ])->get();

        $this->assertCount(1, $completeResults);
        $this->assertTrue($completeResults->contains($dossierA));
        $this->assertFalse($completeResults->contains($dossierB));
        $this->assertFalse($completeResults->contains($dossierC));

        // Uji filter 'incomplete'
        $incompleteResults = $this->filter->apply(Verification::query(), [
            'completeness' => 'incomplete',
        ])->get();

        $this->assertCount(2, $incompleteResults);
        $this->assertFalse($incompleteResults->contains($dossierA));
        $this->assertTrue($incompleteResults->contains($dossierB));
        $this->assertTrue($incompleteResults->contains($dossierC));
    }

    public function test_filter_multiple_combinations(): void
    {
        $docType = 'SPRI Baru (Paspor Biasa 48 Hal)';

        $reqMandatory = DocumentRequirement::create([
            'document_type' => $docType,
            'code' => 'REQ-COMB-1',
            'name' => 'Syarat Utama',
            'is_mandatory' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Target match
        $target = Verification::factory()->create([
            'registration_code' => 'SPRI-MATCH-777',
            'applicant_name' => 'Agus Budiman',
            'document_type' => $docType,
            'status' => VerificationStatus::IN_REVIEW,
            'created_at' => '2026-11-05 10:00:00',
        ]);
        VerificationChecklist::create([
            'verification_id' => $target->id,
            'document_requirement_id' => $reqMandatory->id,
            'is_checked' => true,
        ]);

        // Candidate 2: nama beda
        $other1 = Verification::factory()->create([
            'registration_code' => 'SPRI-OTHER-111',
            'applicant_name' => 'Citra Lestari',
            'document_type' => $docType,
            'status' => VerificationStatus::IN_REVIEW,
            'created_at' => '2026-11-05 10:00:00',
        ]);
        VerificationChecklist::create([
            'verification_id' => $other1->id,
            'document_requirement_id' => $reqMandatory->id,
            'is_checked' => true,
        ]);

        // Candidate 3: status beda
        $other2 = Verification::factory()->create([
            'registration_code' => 'SPRI-MATCH-888',
            'applicant_name' => 'Agus Hendra',
            'document_type' => $docType,
            'status' => VerificationStatus::PENDING,
            'created_at' => '2026-11-05 10:00:00',
        ]);
        VerificationChecklist::create([
            'verification_id' => $other2->id,
            'document_requirement_id' => $reqMandatory->id,
            'is_checked' => true,
        ]);

        // Candidate 4: checklist belum lengkap
        $other3 = Verification::factory()->create([
            'registration_code' => 'SPRI-MATCH-999',
            'applicant_name' => 'Agus Pratama',
            'document_type' => $docType,
            'status' => VerificationStatus::IN_REVIEW,
            'created_at' => '2026-11-05 10:00:00',
        ]);
        VerificationChecklist::create([
            'verification_id' => $other3->id,
            'document_requirement_id' => $reqMandatory->id,
            'is_checked' => false,
        ]);

        $results = $this->filter->apply(Verification::query(), [
            'search' => 'Agus',
            'status' => 'in_review',
            'document_type' => $docType,
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-07',
            'completeness' => 'complete',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($target));
    }

    public function test_soft_deleted_records_are_never_included(): void
    {
        $v = Verification::factory()->create([
            'registration_code' => 'SPRI-DELETED-123',
            'applicant_name' => 'Pemohon Terhapus',
            'deleted_at' => now(),
        ]);

        $results = $this->filter->apply(Verification::query(), [
            'search' => 'DELETED',
        ])->get();

        $this->assertCount(0, $results);
    }
}
