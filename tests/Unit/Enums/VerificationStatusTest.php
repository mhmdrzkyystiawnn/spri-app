<?php

namespace Tests\Unit\Enums;

use App\Enums\VerificationStatus;
use PHPUnit\Framework\TestCase;

class VerificationStatusTest extends TestCase
{
    public function test_enum_has_all_expected_cases(): void
    {
        $cases = array_map(fn ($c) => $c->value, VerificationStatus::cases());

        $this->assertEqualsCanonicalizing([
            'pending',
            'in_review',
            'needs_revision',
            'approved',
            'rejected',
        ], $cases);
    }

    public function test_enum_labels_are_in_indonesian(): void
    {
        $this->assertSame('Menunggu', VerificationStatus::PENDING->label());
        $this->assertSame('Sedang Diverifikasi', VerificationStatus::IN_REVIEW->label());
        $this->assertSame('Perlu Perbaikan', VerificationStatus::NEEDS_REVISION->label());
        $this->assertSame('Disetujui', VerificationStatus::APPROVED->label());
        $this->assertSame('Ditolak', VerificationStatus::REJECTED->label());
    }

    public function test_enum_public_labels_match(): void
    {
        $this->assertSame('Menunggu', VerificationStatus::PENDING->publicLabel());
        $this->assertSame('Disetujui', VerificationStatus::APPROVED->publicLabel());
    }

    public function test_enum_provides_descriptions_and_badge_classes(): void
    {
        foreach (VerificationStatus::cases() as $case) {
            $this->assertNotEmpty($case->description());
            $this->assertNotEmpty($case->badgeClasses());
        }
    }
}
