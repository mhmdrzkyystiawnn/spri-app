<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // ADMINISTRATOR TESTS
    // =========================================================================

    public function test_admin_has_full_access_to_verifications_and_user_management(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.verifications.index'));
        $response->assertOk();
        $response->assertSee('Kelola Pengguna');
        $response->assertSee('Daftarkan Berkas Baru');

        $usersResponse = $this->actingAs($admin)->get(route('admin.users.index'));
        $usersResponse->assertOk();
        $usersResponse->assertSee('Manajemen Petugas', false);
    }

    public function test_admin_can_create_new_user_with_hashed_password(): void
    {
        $admin = User::factory()->admin()->create();

        $userData = [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@imigrasi.go.id',
            'password' => 'SandiKuat123!',
            'role' => UserRole::VERIFIKATOR->value,
            'is_active' => '1',
        ];

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'email' => 'budi.santoso@imigrasi.go.id',
            'name' => 'Budi Santoso',
            'role' => UserRole::VERIFIKATOR->value,
            'is_active' => true,
        ]);

        $createdUser = User::where('email', 'budi.santoso@imigrasi.go.id')->first();
        $this->assertTrue(Hash::check('SandiKuat123!', $createdUser->password));
    }

    public function test_creating_user_validates_email_uniqueness_and_allowed_roles(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'existing@imigrasi.go.id', 'role' => UserRole::VERIFIKATOR]);

        // Duplikat email
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Duplikat User',
            'email' => 'existing@imigrasi.go.id',
            'password' => 'Password123!',
            'role' => UserRole::VERIFIKATOR->value,
        ]);
        $response->assertSessionHasErrors('email');

        // Role di luar allowlist
        $responseInvalidRole = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Invalid Role User',
            'email' => 'unique@imigrasi.go.id',
            'password' => 'Password123!',
            'role' => 'superadmin_hacker',
        ]);
        $responseInvalidRole->assertSessionHasErrors('role');
    }

    public function test_admin_can_update_user_role_and_toggle_active(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->verifikator()->create();

        // Update role to pimpinan
        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => 'Target Updated',
            'email' => $target->email,
            'role' => UserRole::PIMPINAN->value,
            'is_active' => '1',
        ]);
        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Target Updated',
            'role' => UserRole::PIMPINAN->value,
        ]);

        // Toggle active to inactive
        $toggleResponse = $this->actingAs($admin)->patch(route('admin.users.toggle-active', $target));
        $toggleResponse->assertRedirect(route('admin.users.index'));
        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_admin_cannot_delete_or_deactivate_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        // Attempt self-deletion is forbidden by policy
        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));
        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // Attempt self-deactivation returns error and keeps user active
        $toggleResponse = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.toggle-active', $admin));

        $toggleResponse->assertRedirect(route('admin.users.index'));
        $toggleResponse->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $admin = User::factory()->admin()->create();
        $otherUser = User::factory()->verifikator()->create();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $otherUser));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);
    }

    // =========================================================================
    // VERIFIKATOR TESTS
    // =========================================================================

    public function test_verifikator_can_view_create_and_update_verifications(): void
    {
        $verifikator = User::factory()->verifikator()->create();
        $verification = Verification::factory()->create(['status' => VerificationStatus::PENDING->value]);

        // Can access list
        $response = $this->actingAs($verifikator)->get(route('admin.verifications.index'));
        $response->assertOk();
        $response->assertDontSee('Kelola Pengguna');

        // Can access create
        $createResponse = $this->actingAs($verifikator)->get(route('admin.verifications.create'));
        $createResponse->assertOk();

        // Can store
        $storeResponse = $this->actingAs($verifikator)->post(route('admin.verifications.store'), [
            'registration_code' => 'SPRI-VRF-001',
            'applicant_name' => 'Pemohon Verifikator',
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'status' => VerificationStatus::IN_REVIEW->value,
            'admin_notes' => 'Catatan verifikator',
        ]);
        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('verifications', ['registration_code' => 'SPRI-VRF-001']);

        // Can access edit
        $editResponse = $this->actingAs($verifikator)->get(route('admin.verifications.edit', $verification));
        $editResponse->assertOk();

        // Can update
        $updateResponse = $this->actingAs($verifikator)->put(route('admin.verifications.update', $verification), [
            'registration_code' => $verification->registration_code,
            'applicant_name' => $verification->applicant_name,
            'document_type' => $verification->document_type,
            'status' => VerificationStatus::APPROVED->value,
        ]);
        $updateResponse->assertRedirect();
        $this->assertEquals(VerificationStatus::APPROVED, $verification->fresh()->status);
    }

    public function test_verifikator_cannot_delete_verification(): void
    {
        $verifikator = User::factory()->verifikator()->create();
        $verification = Verification::factory()->create();

        $response = $this->actingAs($verifikator)->delete(route('admin.verifications.destroy', $verification));

        $response->assertForbidden();
        $this->assertDatabaseHas('verifications', ['id' => $verification->id]);
    }

    public function test_verifikator_cannot_access_or_mutate_user_management(): void
    {
        $verifikator = User::factory()->verifikator()->create();
        $otherUser = User::factory()->create(['role' => UserRole::VERIFIKATOR]);

        // GET index
        $this->actingAs($verifikator)->get(route('admin.users.index'))->assertForbidden();

        // GET create
        $this->actingAs($verifikator)->get(route('admin.users.create'))->assertForbidden();

        // POST store
        $this->actingAs($verifikator)->post(route('admin.users.store'), [
            'name' => 'Illegal User',
            'email' => 'illegal@imigrasi.go.id',
            'password' => 'Password123!',
            'role' => UserRole::ADMIN->value,
        ])->assertForbidden();

        // GET edit
        $this->actingAs($verifikator)->get(route('admin.users.edit', $otherUser))->assertForbidden();

        // PUT update
        $this->actingAs($verifikator)->put(route('admin.users.update', $otherUser), [
            'name' => 'Hacked Name',
            'email' => $otherUser->email,
            'role' => UserRole::ADMIN->value,
        ])->assertForbidden();

        // PATCH toggle-active
        $this->actingAs($verifikator)->patch(route('admin.users.toggle-active', $otherUser))->assertForbidden();

        // DELETE destroy
        $this->actingAs($verifikator)->delete(route('admin.users.destroy', $otherUser))->assertForbidden();
    }

    // =========================================================================
    // PIMPINAN (KEPALA SEKSI) TESTS - READ ONLY
    // =========================================================================

    public function test_pimpinan_has_read_only_access_to_verifications_dashboard_and_show(): void
    {
        $pimpinan = User::factory()->pimpinan()->create();
        $verification = Verification::factory()->create();

        // Can view index
        $response = $this->actingAs($pimpinan)->get(route('admin.verifications.index'));
        $response->assertOk();
        $response->assertDontSee('Kelola Pengguna');
        $response->assertDontSee('Daftarkan Berkas Baru');

        // Can view detail
        $showResponse = $this->actingAs($pimpinan)->get(route('admin.verifications.show', $verification));
        $showResponse->assertOk();
        $showResponse->assertDontSee('Edit Berkas & Status');
        $showResponse->assertDontSee('Hapus Berkas');
    }

    public function test_pimpinan_cannot_create_update_or_delete_verifications(): void
    {
        $pimpinan = User::factory()->pimpinan()->create();
        $verification = Verification::factory()->create();

        // Create form is forbidden
        $this->actingAs($pimpinan)->get(route('admin.verifications.create'))->assertForbidden();

        // Direct POST is forbidden
        $this->actingAs($pimpinan)->post(route('admin.verifications.store'), [
            'registration_code' => 'SPRI-PMP-999',
            'applicant_name' => 'Illegal Mutate',
            'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
            'status' => VerificationStatus::PENDING->value,
        ])->assertForbidden();

        // Edit form is forbidden
        $this->actingAs($pimpinan)->get(route('admin.verifications.edit', $verification))->assertForbidden();

        // Direct PUT is forbidden
        $this->actingAs($pimpinan)->put(route('admin.verifications.update', $verification), [
            'registration_code' => $verification->registration_code,
            'applicant_name' => 'Updated by Pimpinan',
            'document_type' => $verification->document_type,
            'status' => VerificationStatus::APPROVED->value,
        ])->assertForbidden();

        // Direct DELETE is forbidden
        $this->actingAs($pimpinan)->delete(route('admin.verifications.destroy', $verification))->assertForbidden();
    }

    public function test_pimpinan_cannot_access_user_management(): void
    {
        $pimpinan = User::factory()->pimpinan()->create();

        $this->actingAs($pimpinan)->get(route('admin.users.index'))->assertForbidden();
    }

    // =========================================================================
    // INACTIVE USER LOCKOUT TESTS
    // =========================================================================

    public function test_inactive_admin_is_forbidden_from_admin_area(): void
    {
        $inactiveAdmin = User::factory()->admin()->inactive()->create();

        $response = $this->actingAs($inactiveAdmin)->get(route('admin.verifications.index'));
        $response->assertForbidden();
    }

    public function test_inactive_verifikator_is_forbidden_from_admin_area(): void
    {
        $inactiveVerifikator = User::factory()->verifikator()->inactive()->create();

        $response = $this->actingAs($inactiveVerifikator)->get(route('admin.verifications.index'));
        $response->assertForbidden();
    }

    public function test_inactive_pimpinan_is_forbidden_from_admin_area(): void
    {
        $inactivePimpinan = User::factory()->pimpinan()->inactive()->create();

        $response = $this->actingAs($inactivePimpinan)->get(route('admin.verifications.index'));
        $response->assertForbidden();
    }
}
