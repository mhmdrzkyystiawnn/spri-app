<?php

namespace Tests\Unit;

use App\Models\Verification;
use App\Services\RegistrationCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class RegistrationCodeGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private RegistrationCodeGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new RegistrationCodeGenerator;
    }

    public function test_generates_code_with_valid_regex_format(): void
    {
        $code = $this->generator->generate();

        $this->assertMatchesRegularExpression('/^SPRI-[0-9]{4}-[A-Z0-9]{6}$/', $code);
        $this->assertStringStartsWith('SPRI-'.date('Y').'-', $code);
        $this->assertSame(16, strlen($code));
    }

    public function test_generates_code_with_specified_year(): void
    {
        $code = $this->generator->generate(2030);

        $this->assertMatchesRegularExpression('/^SPRI-2030-[A-Z0-9]{6}$/', $code);
        $this->assertStringStartsWith('SPRI-2030-', $code);
    }

    public function test_generates_unique_codes_in_multiple_calls(): void
    {
        $codes = [];
        for ($i = 0; $i < 20; $i++) {
            $codes[] = $this->generator->generate();
        }

        $this->assertCount(20, array_unique($codes));
    }

    public function test_retries_when_code_collision_occurs(): void
    {
        // Buat mock generator yang menghasilkan kode duplikat pada percobaan pertama
        $mockGenerator = new class extends RegistrationCodeGenerator
        {
            private int $attemptCount = 0;

            protected function generateRandomSegment(int $length = 6): string
            {
                $this->attemptCount++;
                if ($this->attemptCount === 1) {
                    return 'COLLID';
                }

                return 'UNIQUE';
            }
        };

        // Buat record verification yang bertabrakan dengan attempt pertama
        $collidingCode = 'SPRI-'.date('Y').'-COLLID';
        Verification::factory()->create([
            'registration_code' => $collidingCode,
        ]);

        $code = $mockGenerator->generate();

        $this->assertSame('SPRI-'.date('Y').'-UNIQUE', $code);
    }

    public function test_throws_exception_when_max_attempts_exceeded(): void
    {
        // Buat mock generator yang selalu menghasilkan kode yang sama
        $mockGenerator = new class extends RegistrationCodeGenerator
        {
            protected function generateRandomSegment(int $length = 6): string
            {
                return 'ALWAYS';
            }
        };

        $collidingCode = 'SPRI-'.date('Y').'-ALWAYS';
        Verification::factory()->create([
            'registration_code' => $collidingCode,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Gagal menghasilkan nomor registrasi unik setelah 3 percobaan.');

        $mockGenerator->generate(null, 3);
    }
}
