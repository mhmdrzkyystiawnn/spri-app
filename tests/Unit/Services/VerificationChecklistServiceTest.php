<?php

namespace Tests\Unit\Services;

use App\Models\DocumentRequirement;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationChecklist;
use App\Services\VerificationChecklistService;
use Database\Seeders\DocumentRequirementSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationChecklistServiceTest extends TestCase
{
    use RefreshDatabase;

    private VerificationChecklistService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VerificationChecklistService();
        $this->seed(DocumentRequirementSeeder::class);
    }

    public function test_initialize_for_verification_creates_checklists_for_active_requirements(): void
    {
        $verification = Verification::factory()->create([
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
        ]);

        $checklists = $this->service->initializeForVerification($verification);

        $expectedCount = DocumentRequirement::where('document_type', 'SPRI Baru (Paspor Biasa 48 Hal)')
            ->where('is_active', true)
            ->count();

        $this->assertCount($expectedCount, $checklists);
        $this->assertEquals($expectedCount, $verification->checklists()->count());

        foreach ($checklists as $item) {
            $this->assertFalse($item->is_checked);
            $this->assertNull($item->checked_by);
            $this->assertNull($item->checked_at);
            $this->assertNotNull($item->requirement);
        }
    }

    public function test_initialize_for_verification_is_strictly_idempotent(): void
    {
        $verification = Verification::factory()->create([
            'document_type' => 'Penggantian SPRI Habis Masa Berlaku',
        ]);

        // First initialization
        $firstRun = $this->service->initializeForVerification($verification);
        $countAfterFirst = $verification->checklists()->count();

        // Second initialization (re-run)
        $secondRun = $this->service->initializeForVerification($verification);
        $countAfterSecond = $verification->checklists()->count();

        $this->assertEquals($countAfterFirst, $countAfterSecond);
        $this->assertCount($countAfterFirst, $secondRun);
    }

    public function test_initialize_ignores_inactive_requirements(): void
    {
        $documentType = 'Penggantian SPRI Halaman Penuh';

        // Deactivate one requirement
        $req = DocumentRequirement::where('document_type', $documentType)->first();
        $req->update(['is_active' => false]);

        $verification = Verification::factory()->create([
            'document_type' => $documentType,
        ]);

        $checklists = $this->service->initializeForVerification($verification);

        $this->assertFalse($checklists->contains('document_requirement_id', $req->id));
        $this->assertEquals(
            DocumentRequirement::where('document_type', $documentType)->where('is_active', true)->count(),
            $checklists->count()
        );
    }

    public function test_sync_for_document_type_retains_existing_and_adds_new(): void
    {
        $oldType = 'Penggantian SPRI Halaman Penuh';
        $newType = 'Penggantian SPRI Hilang/Rusak';

        $verification = Verification::factory()->create([
            'document_type' => $oldType,
        ]);

        $admin = User::factory()->admin()->create();

        // Inisialisasi awal
        $this->service->initializeForVerification($verification);
        $firstItem = $verification->checklists()->first();
        $firstItem->markChecked($admin, 'Fisik paspor lama sudah diserahkan')->save();

        // Ganti jenis dokumen menggunakan syncForDocumentType
        $syncedChecklists = $this->service->syncForDocumentType($verification, $newType);

        // Verifikasi tipe terupdate
        $this->assertEquals($newType, $verification->fresh()->document_type);

        // Checklist lama tetap tersimpan (audit preservation)
        $this->assertDatabaseHas('verification_checklists', [
            'id' => $firstItem->id,
            'is_checked' => true,
            'notes' => 'Fisik paspor lama sudah diserahkan',
        ]);

        // Checklist untuk tipe baru sudah terpasang
        $newReqs = DocumentRequirement::where('document_type', $newType)->where('is_active', true)->get();
        foreach ($newReqs as $newReq) {
            $this->assertTrue(
                $verification->fresh()->checklists->contains('document_requirement_id', $newReq->id)
            );
        }
    }

    public function test_get_completion_stats_calculates_accurately(): void
    {
        $verification = Verification::factory()->create([
            'document_type' => 'Penggantian SPRI Halaman Penuh', // Memiliki 2 mandatory requirements
        ]);

        $checklists = $this->service->initializeForVerification($verification);
        $stats = $this->service->getCompletionStats($verification);

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(0, $stats['checked']);
        $this->assertEquals(2, $stats['mandatory_total']);
        $this->assertEquals(0, $stats['mandatory_checked']);
        $this->assertFalse($stats['is_complete']);

        // Centang item pertama
        $admin = User::factory()->admin()->create();
        $checklists[0]->markChecked($admin)->save();

        $statsHalf = $this->service->getCompletionStats($verification);
        $this->assertEquals(1, $statsHalf['checked']);
        $this->assertFalse($statsHalf['is_complete']);

        // Centang item kedua
        $checklists[1]->markChecked($admin)->save();

        $statsComplete = $this->service->getCompletionStats($verification);
        $this->assertEquals(2, $statsComplete['checked']);
        $this->assertEquals(2, $statsComplete['mandatory_checked']);
        $this->assertTrue($statsComplete['is_complete']);
    }

    public function test_cascade_delete_verification_removes_associated_checklists(): void
    {
        $verification = Verification::factory()->create([
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
        ]);

        $this->service->initializeForVerification($verification);
        $checklistId = $verification->checklists()->first()->id;

        // Bersihkan audit logs dan status histories agar tidak memicu FK pada tabel lain
        $verification->auditLogs()->delete();
        $verification->statusHistories()->delete();

        // Force delete tanpa event observer agar menguji DB foreign key cascade murni
        Verification::withoutEvents(function () use ($verification) {
            $verification->forceDelete();
        });

        $this->assertDatabaseMissing('verification_checklists', ['id' => $checklistId]);
    }

    public function test_restrict_delete_document_requirement_when_referenced(): void
    {
        $verification = Verification::factory()->create([
            'document_type' => 'Surat Perjalanan Laksana Paspor (SPLP)',
        ]);

        $this->service->initializeForVerification($verification);
        $firstItem = $verification->checklists()->first();
        $requirement = $firstItem->requirement;

        $this->expectException(QueryException::class);
        $requirement->delete();
    }

    public function test_unique_composite_constraint_on_document_requirements(): void
    {
        $this->expectException(QueryException::class);

        DocumentRequirement::create([
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'code' => 'BARU-KTP', // Duplikat dari seeder
            'name' => 'Duplikat KTP',
            'is_mandatory' => true,
            'is_active' => true,
            'sort_order' => 99,
        ]);
    }

    public function test_unique_composite_constraint_on_verification_checklists(): void
    {
        $verification = Verification::factory()->create([
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
        ]);

        $req = DocumentRequirement::where('document_type', $verification->document_type)->first();

        VerificationChecklist::create([
            'verification_id' => $verification->id,
            'document_requirement_id' => $req->id,
        ]);

        $this->expectException(QueryException::class);

        // Mencoba duplikat pasangan yang sama
        VerificationChecklist::create([
            'verification_id' => $verification->id,
            'document_requirement_id' => $req->id,
        ]);
    }
}
