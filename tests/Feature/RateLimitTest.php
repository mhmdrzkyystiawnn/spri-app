<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_status_check_is_throttled_after_10_attempts(): void
    {
        // Bersihkan rate limiter sebelum uji coba
        RateLimiter::clear('status-check:127.0.0.1');

        for ($i = 1; $i <= 10; $i++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
                ->post('/cek-status', [
                    'registration_code' => 'SPRI-2026-TEST' . $i,
                ]);

            $this->assertNotEquals(429, $response->getStatusCode(), "Request #{$i} should not be throttled");
        }

        // Permintaan ke-11 harus diblokir dengan kode status 429
        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/cek-status', [
                'registration_code' => 'SPRI-2026-TEST11',
            ]);

        $response->assertStatus(429);
    }
}
