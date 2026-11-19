<?php

namespace Tests\Feature\Admin;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Admin\VerificationController;
use App\Models\DocumentRequirement;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationChecklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $verifikator;
    private User $pimpinan;
    private User $inactiveUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->verifikator = User::factory()->verifikator()->create();
        $this->pimpinan = User::factory()->pimpinan()->create();
        $this->inactiveUser = User::factory()->inactive()->create();
    }

    public function test_admin_can_filter_by_search_keyword_code_and_name(): void
    {
        $v1 = Verification::factory()->create([
            'registration_code' => 'SPRI-2026-SEARCH1',
            'applicant_name' => 'Budi Santoso',
        ]);

        $v2 = Verification::factory()->create([
            'registration_code' => 'SPRI-2026-SEARCH2',
            'applicant_name' => 'Siti Aminah',
        ]);

        // Search by registration code
        $response1 = $this->actingAs($this->admin)->get(route('admin.verifications.index', ['search' => 'SEARCH1']));
        $response1->assertOk();
        $response1->assertSee('SPRI-2026-SEARCH1');
        $response1->assertDontSee('SPRI-2026-SEARCH2');

        // Search by applicant name
        $response2 = $this->actingAs($this->admin)->get(route('admin.verifications.index', ['search' => 'Aminah']));
        $response2->assertOk();
        $response2->assertSee('SPRI-2026-SEARCH2');
        $response2->assertDontSee('SPRI-2026-SEARCH1');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $v1 = Verification::factory()->create([
            'registration_code' => 'SPRI-STATUS-PENDING',
            'status' => VerificationStatus::PENDING,
        ]);

        $v2 = Verification::factory()->create([
            'registration_code' => 'SPRI-STATUS-APPROVED',
            'status' => VerificationStatus::APPROVED,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.verifications.index', ['status' => 'approved']));
        $response->assertOk();
        $response->assertSee('SPRI-STATUS-APPROVED');
        $response->assertDontSee('SPRI-STATUS-PENDING');
    }

    public function test_admin_can_filter_by_document_type(): void
    {
        $type1 = 'SPRI Baru (Paspor Biasa 48 Hal)';
        $type2 = 'Penggantian SPRI Halaman Penuh';

        $v1 = Verification::factory()->create([
            'registration_code' => 'SPRI-DOCTYPE-1',
            'document_type' => $type1,
        ]);

        $v2 = Verification::factory()->create([
            'registration_code' => 'SPRI-DOCTYPE-2',
            'document_type' => $type2,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.verifications.index', ['document_type' => $type1]));
        $response->assertOk();
        $response->assertSee('SPRI-DOCTYPE-1');
        $response->assertDontSee('SPRI-DOCTYPE-2');
    }

    public function test_admin_can_filter_by_date_range(): void
    {
        $v1 = Verification::factory()->create([
            'registration_code' => 'SPRI-DATE-EARLY',
            'created_at' => '2026-11-01 10:00:00',
        ]);

        $v2 = Verification::factory()->create([
            'registration_code' => 'SPRI-DATE-TARGET',
            'created_at' => '2026-11-05 14:00:00',
        ]);

        $v3 = Verification::factory()->create([
            'registration_code' => 'SPRI-DATE-LATE',
            'created_at' => '2026-11-10 18:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.verifications.index', [
            'date_from' => '2026-11-03',
            'date_to' => '2026-11-07',
        ]));

        $response->assertOk();
        $response->assertSee('SPRI-DATE-TARGET');
        $response->assertDontSee('SPRI-DATE-EARLY');
        $response->assertDontSee('SPRI-DATE-LATE');
    }

    public function test_admin_can_filter_by_completeness(): void
    {
        $docType = 'SPRI Baru (Paspor Biasa 48 Hal)';

        $mandatory = DocumentRequirement::create([
            'document_type' => $docType,
            'code' => 'REQ-SEARCH-M1',
            'name' => 'KTP Elektronik Wajib',
            'is_mandatory' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Dossier 1: Lengkap
        $vComplete = Verification::factory()->create([
            'registration_code' => 'SPRI-COMP-001',
            'document_type' => $docType,
        ]);
        VerificationChecklist::create([
            'verification_id' => $vComplete->id,
            'document_requirement_id' => $mandatory->id,
            'is_checked' => true,
        ]);

        // Dossier 2: Belum Lengkap (syarat wajib belum dicek)
        $vIncomplete1 = Verification::factory()->create([
            'registration_code' => 'SPRI-INCOMP-002',
            'document_type' => $docType,
        ]);
        VerificationChecklist::create([
            'verification_id' => $vIncomplete1->id,
            'document_requirement_id' => $mandatory->id,
            'is_checked' => false,
        ]);

        // Dossier 3: Belum Lengkap (tanpa checklist)
        $vIncomplete2 = Verification::factory()->create([
            'registration_code' => 'SPRI-INCOMP-003',
            'document_type' => $docType,
        ]);

        // Test filter 'complete'
        $responseComplete = $this->actingAs($this->admin)->get(route('admin.verifications.index', [
            'completeness' => 'complete',
        ]));
        $responseComplete->assertOk();
        $responseComplete->assertSee('SPRI-COMP-001');
        $responseComplete->assertDontSee('SPRI-INCOMP-002');
        $responseComplete->assertDontSee('SPRI-INCOMP-003');

        // Test filter 'incomplete'
        $responseIncomplete = $this->actingAs($this->admin)->get(route('admin.verifications.index', [
            'completeness' => 'incomplete',
        ]));
        $responseIncomplete->assertOk();
        $responseIncomplete->assertDontSee('SPRI-COMP-001');
        $responseIncomplete->assertSee('SPRI-INCOMP-002');
        $responseIncomplete->assertSee('SPRI-INCOMP-003');
    }

    public function test_admin_can_filter_with_multiple_combined_criteria(): void
    {
        $docType = 'SPRI Baru (Paspor Biasa 48 Hal)';

        $mandatory = DocumentRequirement::create([
            'document_type' => $docType,
            'code' => 'REQ-COMB-FEAT-1',
            'name' => 'KTP',
            'is_mandatory' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $target = Verification::factory()->create([
            'registration_code' => 'SPRI-COMB-MATCH',
            'applicant_name' => 'Fajar Nugraha',
            'document_type' => $docType,
            'status' => VerificationStatus::IN_REVIEW,
            'created_at' => '2026-11-05 10:00:00',
        ]);
        VerificationChecklist::create([
            'verification_id' => $target->id,
            'document_requirement_id' => $mandatory->id,
            'is_checked' => true,
        ]);

        $other = Verification::factory()->create([
            'registration_code' => 'SPRI-COMB-DIFF',
            'applicant_name' => 'Fajar Gumilang',
            'document_type' => $docType,
            'status' => VerificationStatus::PENDING,
            'created_at' => '2026-11-05 10:00:00',
        ]);
        VerificationChecklist::create([
            'verification_id' => $other->id,
            'document_requirement_id' => $mandatory->id,
            'is_checked' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.verifications.index', [
            'search' => 'Nugraha',
            'status' => 'in_review',
            'document_type' => $docType,
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-10',
            'completeness' => 'complete',
        ]));

        $response->assertOk();
        $response->assertSee('SPRI-COMB-MATCH');
        $response->assertDontSee('SPRI-COMB-DIFF');
    }

    public function test_pagination_preserves_query_string_filters(): void
    {
        // Buat 15 berkas dengan status pending
        for ($i = 1; $i <= 15; $i++) {
            Verification::factory()->create([
                'registration_code' => sprintf('SPRI-PAGE-%03d', $i),
                'status' => VerificationStatus::PENDING,
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.verifications.index', [
            'status' => 'pending',
            'search' => 'SPRI-PAGE',
        ]));

        $response->assertOk();
        // Cek bahwa link paginasi memuat parameter query string yang sedang aktif
        $response->assertSee('status=pending');
        $response->assertSee('search=SPRI-PAGE');
    }

    public function test_authorized_roles_can_access_filtered_index(): void
    {
        // Admin diizinkan
        $this->actingAs($this->admin)
            ->get(route('admin.verifications.index', ['status' => 'approved']))
            ->assertOk();

        // Verifikator diizinkan
        $this->actingAs($this->verifikator)
            ->get(route('admin.verifications.index', ['status' => 'approved']))
            ->assertOk();

        // Pimpinan diizinkan
        $this->actingAs($this->pimpinan)
            ->get(route('admin.verifications.index', ['status' => 'approved']))
            ->assertOk();
    }

    public function test_inactive_user_is_forbidden_from_filtered_index(): void
    {
        $this->actingAs($this->inactiveUser)
            ->get(route('admin.verifications.index', ['status' => 'approved']))
            ->assertForbidden();
    }

    public function test_unauthenticated_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.verifications.index', ['status' => 'approved']))
            ->assertRedirect(route('login'));
    }

    public function test_empty_state_displayed_when_no_records_match_filter(): void
    {
        Verification::factory()->create([
            'registration_code' => 'SPRI-EXIST-001',
            'applicant_name' => 'Warga Asli',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.verifications.index', [
            'search' => 'TIDAK_ADA_DI_MANAPUN_XYZ',
        ]));

        $response->assertOk();
        $response->assertSee('Tidak ada berkas verifikasi yang sesuai dengan kriteria filter.');
        $response->assertSee('Reset Semua Filter');
    }

    public function test_invalid_filter_input_redirects_with_session_errors(): void
    {
        $response = $this->actingAs($this->admin)->from(route('admin.verifications.index'))->get(route('admin.verifications.index', [
            'date_from' => '2026-11-15',
            'date_to' => '2026-11-10',
        ]));

        $response->assertRedirect(route('admin.verifications.index'));
        $response->assertSessionHasErrors(['date_to']);
    }
}
