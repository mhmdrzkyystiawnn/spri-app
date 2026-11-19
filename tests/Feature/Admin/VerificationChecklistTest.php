<?php

namespace Tests\Feature\Admin;

use App\Models\DocumentRequirement;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationChecklist;
use Database\Seeders\DocumentRequirementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationChecklistTest extends TestCase
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

    public function test_verification_creation_automatically_initializes_checklist(): void
    {
        $response = $this->actingAs($this->verifikator)->post(route('admin.verifications.store'), [
            'registration_code' => 'SPRI-CHK-001',
            'applicant_name' => 'Pemohon Checklist Baru',
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'status' => 'pending',
            'public_notes' => 'Menunggu verifikasi fisik berkas.',
        ]);

        $response->assertRedirect();
        $verification = Verification::where('registration_code', 'SPRI-CHK-001')->firstOrFail();

        $activeReqs = DocumentRequirement::where('document_type', 'SPRI Baru (Paspor Biasa 48 Hal)')
            ->where('is_active', true)
            ->get();

        $this->assertCount($activeReqs->count(), $verification->checklists);

        foreach ($verification->checklists as $checklist) {
            $this->assertFalse($checklist->is_checked);
            $this->assertNull($checklist->checked_by);
            $this->assertNull($checklist->checked_at);
        }
    }

    public function test_verifikator_can_update_checklist_items_with_actor_and_timestamp(): void
    {
        $verification = Verification::factory()->create([
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
        ]);

        // Auto initialize on show or via service
        $this->actingAs($this->verifikator)->get(route('admin.verifications.show', $verification));
        $checklists = $verification->fresh()->checklists;
        $firstItem = $checklists->first();

        $updatePayload = [
            'items' => [
                $firstItem->id => [
                    'is_checked' => '1',
                    'notes' => 'KTP fisik asli sudah diperiksa dan cocok.',
                ],
            ],
        ];

        $response = $this->actingAs($this->verifikator)
            ->patch(route('admin.verifications.checklist.update', $verification), $updatePayload);

        $response->assertRedirect(route('admin.verifications.show', $verification));
        $response->assertSessionHas('status');

        $firstItem->refresh();
        $this->assertTrue($firstItem->is_checked);
        $this->assertSame($this->verifikator->id, $firstItem->checked_by);
        $this->assertNotNull($firstItem->checked_at);
        $this->assertSame('KTP fisik asli sudah diperiksa dan cocok.', $firstItem->notes);
    }

    public function test_admin_can_update_checklist_items(): void
    {
        $verification = Verification::factory()->create([
            'document_type' => 'Penggantian SPRI Habis Masa Berlaku',
        ]);

        $this->actingAs($this->admin)->get(route('admin.verifications.show', $verification));
        $item = $verification->fresh()->checklists->first();

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.verifications.checklist.update', $verification), [
                'items' => [
                    $item->id => [
                        'is_checked' => '1',
                        'notes' => 'Diverifikasi langsung oleh Admin loket.',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.verifications.show', $verification));
        $this->assertTrue($item->fresh()->is_checked);
        $this->assertSame($this->admin->id, $item->fresh()->checked_by);
    }

    public function test_pimpinan_is_forbidden_from_updating_checklist(): void
    {
        $verification = Verification::factory()->create([
            'document_type' => 'Penggantian SPRI Habis Masa Berlaku',
        ]);

        $this->actingAs($this->admin)->get(route('admin.verifications.show', $verification));
        $item = $verification->fresh()->checklists->first();

        $response = $this->actingAs($this->pimpinan)
            ->patch(route('admin.verifications.checklist.update', $verification), [
                'items' => [
                    $item->id => [
                        'is_checked' => '1',
                        'notes' => 'Pimpinan mencoba mengubah checklist.',
                    ],
                ],
            ]);

        $response->assertForbidden();
        $this->assertFalse($item->fresh()->is_checked);
    }

    public function test_pimpinan_can_view_dossier_checklist_in_read_only_mode(): void
    {
        $verification = Verification::factory()->create([
            'document_type' => 'Penggantian SPRI Habis Masa Berlaku',
        ]);

        $response = $this->actingAs($this->pimpinan)->get(route('admin.verifications.show', $verification));

        $response->assertOk();
        $response->assertSee('Checklist Kelengkapan Dokumen Fisik');
        $response->assertSee('Mode pemantauan (Read-Only)');
        $response->assertDontSee('Simpan Perubahan Checklist');
    }

    public function test_updating_checklist_rejects_invalid_or_unrelated_checklist_ids(): void
    {
        $verificationA = Verification::factory()->create([
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
        ]);
        $verificationB = Verification::factory()->create([
            'document_type' => 'Penggantian SPRI Habis Masa Berlaku',
        ]);

        $this->actingAs($this->admin)->get(route('admin.verifications.show', $verificationA));
        $this->actingAs($this->admin)->get(route('admin.verifications.show', $verificationB));

        $itemB = $verificationB->fresh()->checklists->first();

        // Mencoba kirim checklist milik verificationB ke endpoint verificationA
        $response = $this->actingAs($this->verifikator)
            ->patch(route('admin.verifications.checklist.update', $verificationA), [
                'items' => [
                    $itemB->id => [
                        'is_checked' => '1',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_updating_verification_document_type_syncs_checklists_safely(): void
    {
        $oldType = 'Penggantian SPRI Halaman Penuh';
        $newType = 'Penggantian SPRI Hilang/Rusak';

        $verification = Verification::factory()->create([
            'document_type' => $oldType,
        ]);

        $this->actingAs($this->admin)->get(route('admin.verifications.show', $verification));
        $oldChecklistsCount = $verification->fresh()->checklists->count();

        // Ubah jenis dokumen verifikasi
        $response = $this->actingAs($this->verifikator)->put(route('admin.verifications.update', $verification), [
            'registration_code' => $verification->registration_code,
            'applicant_name' => $verification->applicant_name,
            'document_type' => $newType,
            'status' => 'in_review',
            'public_notes' => 'Catatan verifikasi dokumen.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $verification->refresh();

        $this->assertSame($newType, $verification->document_type);
        // Persyaratan baru ter-attach tanpa merusak checklist lama
        $this->assertGreaterThan($oldChecklistsCount, $verification->checklists->count());
    }

    public function test_legacy_verification_without_checklists_auto_initializes_on_show(): void
    {
        // Buat verifikasi langsung di DB tanpa checklist
        $verification = Verification::factory()->create([
            'document_type' => 'Surat Perjalanan Laksana Paspor (SPLP)',
        ]);
        $this->assertEmpty($verification->checklists);

        // Buka halaman show
        $response = $this->actingAs($this->verifikator)->get(route('admin.verifications.show', $verification));

        $response->assertOk();
        $this->assertNotEmpty($verification->fresh()->checklists);
        $response->assertSee('SPLP-IDENTITAS');
    }
}
