<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\VerificationController;
use App\Http\Requests\VerificationSearchRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationSearchRequestTest extends TestCase
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

        $this->app['router']->get('/test-verification-search-request', function (VerificationSearchRequest $request) {
            return response()->json([
                'all' => $request->all(),
                'validated' => $request->validated(),
            ]);
        })->middleware('web');
    }

    public function test_admin_is_authorized_to_search(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request');

        $response->assertOk();
    }

    public function test_verifikator_is_authorized_to_search(): void
    {
        $response = $this->actingAs($this->verifikator)->getJson('/test-verification-search-request');

        $response->assertOk();
    }

    public function test_pimpinan_is_authorized_to_search(): void
    {
        $response = $this->actingAs($this->pimpinan)->getJson('/test-verification-search-request');

        $response->assertOk();
    }

    public function test_inactive_user_is_forbidden(): void
    {
        $response = $this->actingAs($this->inactiveUser)->getJson('/test-verification-search-request');

        $response->assertForbidden();
    }

    public function test_unauthenticated_guest_is_forbidden_by_request_authorization(): void
    {
        $response = $this->getJson('/test-verification-search-request');

        $response->assertForbidden();
    }

    public function test_empty_query_parameters_are_normalized_to_null(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query([
            'search' => '   ',
            'status' => '',
            'document_type' => '',
            'date_from' => '',
            'date_to' => '',
            'completeness' => '',
        ]));

        $response->assertOk();
        $validated = $response->json('validated');

        $this->assertNull($validated['search']);
        $this->assertNull($validated['status']);
        $this->assertNull($validated['document_type']);
        $this->assertNull($validated['date_from']);
        $this->assertNull($validated['date_to']);
        $this->assertNull($validated['completeness']);
    }

    public function test_valid_search_parameters_pass_validation(): void
    {
        $payload = [
            'search' => '  Budi Santoso  ',
            'status' => 'approved',
            'document_type' => VerificationController::DOCUMENT_TYPES[0],
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-10',
            'completeness' => 'complete',
        ];

        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query($payload));

        $response->assertOk();
        $validated = $response->json('validated');

        $this->assertSame('Budi Santoso', $validated['search']);
        $this->assertSame('approved', $validated['status']);
        $this->assertSame(VerificationController::DOCUMENT_TYPES[0], $validated['document_type']);
        $this->assertSame('2026-11-01', $validated['date_from']);
        $this->assertSame('2026-11-10', $validated['date_to']);
        $this->assertSame('complete', $validated['completeness']);
    }

    public function test_search_exceeding_100_characters_fails(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query([
            'search' => str_repeat('a', 101),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['search']);
    }

    public function test_invalid_status_fails(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query([
            'status' => 'invalid_status_value',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    public function test_invalid_document_type_fails(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query([
            'document_type' => 'Dokumen Luar Angkasa',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['document_type']);
    }

    public function test_invalid_date_from_format_fails(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query([
            'date_from' => '01-11-2026',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date_from']);
    }

    public function test_invalid_date_to_format_fails(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query([
            'date_to' => '2026/11/10',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date_to']);
    }

    public function test_date_to_earlier_than_date_from_fails(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query([
            'date_from' => '2026-11-15',
            'date_to' => '2026-11-10',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date_to']);
    }

    public function test_date_to_equal_to_date_from_passes(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query([
            'date_from' => '2026-11-10',
            'date_to' => '2026-11-10',
        ]));

        $response->assertOk();
    }

    public function test_invalid_completeness_fails(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query([
            'completeness' => 'partial',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['completeness']);
    }

    public function test_arbitrary_extra_fields_like_nik_are_stripped_from_validated(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/test-verification-search-request?' . http_build_query([
            'search' => 'Budi',
            'nik' => '3201234567890001',
            'sort' => 'desc',
        ]));

        $response->assertOk();
        $validated = $response->json('validated');

        $this->assertArrayNotHasKey('nik', $validated);
        $this->assertArrayNotHasKey('sort', $validated);
        $this->assertSame('Budi', $validated['search']);
    }
}
