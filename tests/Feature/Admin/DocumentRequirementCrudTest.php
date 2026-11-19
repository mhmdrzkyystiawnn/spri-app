<?php

namespace Tests\Feature\Admin;

use App\Models\DocumentRequirement;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationChecklist;
use Database\Seeders\DocumentRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentRequirementCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $verifikator;
    private User $pimpinan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->verifikator = User::factory()->verifikator()->create();
        $this->pimpinan = User::factory()->pimpinan()->create();

        $this->seed(DocumentRequirementSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.document-requirements.index'));
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_document_requirements_index_and_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.document-requirements.index'));
        $response->assertOk();
        $response->assertSee('Master Persyaratan Dokumen SPRI');
        $response->assertSee('Tambah Persyaratan');

        // Filter document type
        $filterResponse = $this->actingAs($this->admin)->get(route('admin.document-requirements.index', [
            'document_type' => 'Penggantian SPRI Hilang/Rusak',
        ]));
        $filterResponse->assertOk();
        $filterResponse->assertSee('HILANG-STPLK');
    }

    public function test_admin_can_create_document_requirement(): void
    {
        $payload = [
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'code' => 'BARU-IJAZAH',
            'name' => 'Ijazah Terakhir Asli',
            'is_mandatory' => '1',
            'is_active' => '1',
            'sort_order' => 5,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.document-requirements.store'), $payload);

        $response->assertRedirect(route('admin.document-requirements.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('document_requirements', [
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'code' => 'BARU-IJAZAH',
            'name' => 'Ijazah Terakhir Asli',
            'is_mandatory' => true,
            'is_active' => true,
            'sort_order' => 5,
        ]);
    }

    public function test_admin_can_view_document_requirement_detail(): void
    {
        $requirement = DocumentRequirement::firstOrFail();

        $response = $this->actingAs($this->admin)->get(route('admin.document-requirements.show', $requirement));

        $response->assertOk();
        $response->assertSee($requirement->code);
        $response->assertSee($requirement->name);
    }

    public function test_admin_can_update_document_requirement(): void
    {
        $requirement = DocumentRequirement::where('code', 'BARU-KTP')->firstOrFail();

        $response = $this->actingAs($this->admin)->put(route('admin.document-requirements.update', $requirement), [
            'document_type' => $requirement->document_type,
            'code' => $requirement->code,
            'name' => 'KTP-el Asli Terverifikasi Disdukcapil',
            'is_mandatory' => '1',
            'is_active' => '1',
            'sort_order' => 10,
        ]);

        $response->assertRedirect(route('admin.document-requirements.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('document_requirements', [
            'id' => $requirement->id,
            'name' => 'KTP-el Asli Terverifikasi Disdukcapil',
            'sort_order' => 10,
        ]);
    }

    public function test_admin_can_toggle_active_status(): void
    {
        $requirement = DocumentRequirement::where('code', 'BARU-REKOM')->firstOrFail();
        $initialState = $requirement->is_active;

        $response = $this->actingAs($this->admin)->patch(route('admin.document-requirements.toggle-active', $requirement));
        $response->assertRedirect(route('admin.document-requirements.index'));

        $this->assertEquals(! $initialState, $requirement->fresh()->is_active);
    }

    public function test_destroy_deactivates_when_requirement_is_referenced_by_checklists(): void
    {
        $requirement = DocumentRequirement::where('code', 'BARU-KTP')->firstOrFail();

        // Buat verifikasi dan checklist referensi
        $verification = Verification::factory()->create([
            'document_type' => $requirement->document_type,
        ]);

        VerificationChecklist::create([
            'verification_id' => $verification->id,
            'document_requirement_id' => $requirement->id,
            'is_checked' => true,
        ]);

        // Coba delete
        $response = $this->actingAs($this->admin)->delete(route('admin.document-requirements.destroy', $requirement));
        $response->assertRedirect(route('admin.document-requirements.index'));
        $response->assertSessionHas('status');

        // Requirement tidak dihapus fisik, tapi dinonaktifkan
        $this->assertDatabaseHas('document_requirements', [
            'id' => $requirement->id,
            'is_active' => false,
        ]);
    }

    public function test_destroy_deletes_when_requirement_is_not_referenced(): void
    {
        $requirement = DocumentRequirement::create([
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'code' => 'TEMP-DOK',
            'name' => 'Dokumen Sementara',
            'is_mandatory' => false,
            'is_active' => true,
            'sort_order' => 99,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.document-requirements.destroy', $requirement));
        $response->assertRedirect(route('admin.document-requirements.index'));

        $this->assertDatabaseMissing('document_requirements', [
            'id' => $requirement->id,
        ]);
    }

    public function test_validation_rejects_duplicate_code_for_same_document_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.document-requirements.store'), [
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'code' => 'BARU-KTP', // Sudah ada di seeder
            'name' => 'Duplikat KTP',
            'is_mandatory' => '1',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_validation_allows_same_code_for_different_document_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.document-requirements.store'), [
            'document_type' => 'Penggantian SPRI Halaman Penuh',
            'code' => 'BARU-KTP', // Kode sama tapi untuk jenis permohonan berbeda
            'name' => 'KTP Khusus Halaman Penuh',
            'is_mandatory' => '1',
        ]);

        $response->assertRedirect(route('admin.document-requirements.index'));
        $this->assertDatabaseHas('document_requirements', [
            'document_type' => 'Penggantian SPRI Halaman Penuh',
            'code' => 'BARU-KTP',
        ]);
    }

    public function test_validation_rejects_unlisted_document_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.document-requirements.store'), [
            'document_type' => 'Jenis Paspor Ilegal / Tidak Terdaftar',
            'code' => 'ILEGAL-01',
            'name' => 'Dokumen Palsu',
        ]);

        $response->assertSessionHasErrors('document_type');
    }

    public function test_verifikator_is_forbidden_from_all_document_requirements_routes(): void
    {
        $requirement = DocumentRequirement::firstOrFail();

        $this->actingAs($this->verifikator)->get(route('admin.document-requirements.index'))->assertForbidden();
        $this->actingAs($this->verifikator)->get(route('admin.document-requirements.create'))->assertForbidden();
        $this->actingAs($this->verifikator)->post(route('admin.document-requirements.store'), [
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'code' => 'VRF-HACK',
            'name' => 'Hacked',
        ])->assertForbidden();
        $this->actingAs($this->verifikator)->get(route('admin.document-requirements.show', $requirement))->assertForbidden();
        $this->actingAs($this->verifikator)->get(route('admin.document-requirements.edit', $requirement))->assertForbidden();
        $this->actingAs($this->verifikator)->put(route('admin.document-requirements.update', $requirement), [
            'name' => 'Hacked Name',
        ])->assertForbidden();
        $this->actingAs($this->verifikator)->patch(route('admin.document-requirements.toggle-active', $requirement))->assertForbidden();
        $this->actingAs($this->verifikator)->delete(route('admin.document-requirements.destroy', $requirement))->assertForbidden();
    }

    public function test_pimpinan_is_forbidden_from_all_document_requirements_routes(): void
    {
        $requirement = DocumentRequirement::firstOrFail();

        $this->actingAs($this->pimpinan)->get(route('admin.document-requirements.index'))->assertForbidden();
        $this->actingAs($this->pimpinan)->get(route('admin.document-requirements.create'))->assertForbidden();
        $this->actingAs($this->pimpinan)->post(route('admin.document-requirements.store'), [
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'code' => 'PMP-HACK',
            'name' => 'Hacked',
        ])->assertForbidden();
        $this->actingAs($this->pimpinan)->delete(route('admin.document-requirements.destroy', $requirement))->assertForbidden();
    }
}
