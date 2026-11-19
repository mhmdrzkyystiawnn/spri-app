<?php

namespace Database\Factories;

use App\Enums\VerificationStatus;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Verification>
 */
class VerificationFactory extends Factory
{
    protected $model = Verification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $docTypes = [
            'SPRI Baru (Paspor Biasa 48 Hal)',
            'SPRI Baru (Paspor Elektronik)',
            'Penggantian SPRI Habis Masa Berlaku',
            'Penggantian SPRI Halaman Penuh',
            'Penggantian SPRI Hilang/Rusak',
        ];

        $status = fake()->randomElement(VerificationStatus::cases());

        return [
            'registration_code' => 'SPRI-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'applicant_name' => fake()->name(),
            'document_type' => fake()->randomElement($docTypes),
            'status' => $status,
            'public_notes' => $status === VerificationStatus::NEEDS_REVISION
                ? 'Harap lengkapi fotokopi Kartu Keluarga terbaru.'
                : ($status === VerificationStatus::REJECTED ? 'Data lampiran tidak sesuai dengan identitas kependudukan.' : null),
            'admin_notes' => 'Catatan internal loket: telah dicek kesesuaian dokumen oleh sistem simulasi.',
            'verified_at' => $status === VerificationStatus::APPROVED ? now()->subHours(rand(1, 48)) : null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * State untuk permohonan berstatus approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VerificationStatus::APPROVED,
            'verified_at' => now(),
            'public_notes' => 'Dokumen lengkap dan valid. Menunggu jadwal pengambilan.',
        ]);
    }

    /**
     * State untuk permohonan berstatus pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VerificationStatus::PENDING,
            'verified_at' => null,
            'public_notes' => null,
        ]);
    }
}
