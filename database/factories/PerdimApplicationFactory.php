<?php

namespace Database\Factories;

use App\Models\PerdimApplication;
use App\Models\Verification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerdimApplication>
 */
class PerdimApplicationFactory extends Factory
{
    protected $model = PerdimApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'verification_id' => Verification::factory(),

            // Seksi 1: Permohonan & SPRI
            'application_type' => 'baru',
            'passport_type' => 'paspor_biasa_48',
            'replacement_reason' => null,
            'change_reason' => null,

            // Seksi 2: Identitas Diri Pemohon
            'full_name' => fake()->name(),
            'alias_name' => null,
            'gender' => fake()->randomElement(['L', 'P']),
            'height_cm' => fake()->numberBetween(150, 190),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->date('Y-m-d', '-20 years'),
            'nik' => fake()->numerify('317101##########'),
            'identity_number' => null,
            'identity_issued_place' => fake()->city(),
            'identity_issued_date' => fake()->date('Y-m-d', '-5 years'),
            'identity_expiry_date' => fake()->date('Y-m-d', '+5 years'),
            'occupation' => fake()->jobTitle(),
            'workplace_address' => fake()->address(),
            'office_phone' => fake()->phoneNumber(),
            'indonesia_address' => fake()->address(),
            'indonesia_phone' => fake()->phoneNumber(),
            'overseas_address' => null,
            'overseas_phone' => null,
            'email' => fake()->safeEmail(),
            'marital_status' => 'kawin',

            // Seksi 3: Data Keluarga
            'mother_name' => fake()->name('female'),
            'mother_nationality' => 'WNI',
            'mother_birth_place' => fake()->city(),
            'mother_birth_date' => fake()->date('Y-m-d', '-50 years'),

            'father_name' => fake()->name('male'),
            'father_nationality' => 'WNI',
            'father_birth_place' => fake()->city(),
            'father_birth_date' => fake()->date('Y-m-d', '-55 years'),
            'father_address' => fake()->address(),
            'father_phone' => fake()->phoneNumber(),

            'spouse_name' => fake()->name(),
            'spouse_nationality' => 'WNI',
            'spouse_birth_place' => fake()->city(),
            'spouse_birth_date' => fake()->date('Y-m-d', '-22 years'),
            'spouse_address' => fake()->address(),

            // Seksi 4: Perubahan Data, Kontak Darurat & Pernyataan
            'change_details' => null,
            'emergency_id_name' => fake()->name(),
            'emergency_id_relationship' => 'Saudara Kandung',
            'emergency_id_address' => fake()->address(),
            'emergency_id_phone' => fake()->phoneNumber(),

            'emergency_overseas_name' => null,
            'emergency_overseas_address' => null,
            'emergency_overseas_phone' => null,

            'declaration_accepted' => true,
            'declaration_accepted_at' => now(),
        ];
    }

    /**
     * State untuk permohonan penggantian paspor.
     */
    public function replacement(string $reason = 'habis_masa_berlaku'): static
    {
        return $this->state(fn (array $attributes) => [
            'application_type' => 'penggantian',
            'replacement_reason' => $reason,
        ]);
    }

    /**
     * State untuk permohonan perubahan data paspor.
     */
    public function change(string $reason = 'perubahan_nama', ?array $details = null): static
    {
        return $this->state(fn (array $attributes) => [
            'application_type' => 'perubahan',
            'change_reason' => $reason,
            'change_details' => $details ?? [
                [
                    'field' => 'Nama Pemohon',
                    'old_value' => 'Nama Lama',
                    'new_value' => 'Nama Baru',
                    'reason' => 'Putusan Pengadilan Negeri',
                ],
            ],
        ]);
    }

    /**
     * State dengan data domisili dan kontak darurat luar negeri.
     */
    public function withOverseasData(): static
    {
        return $this->state(fn (array $attributes) => [
            'overseas_address' => fake()->address(),
            'overseas_phone' => fake()->phoneNumber(),
            'emergency_overseas_name' => fake()->name(),
            'emergency_overseas_address' => fake()->address(),
            'emergency_overseas_phone' => fake()->phoneNumber(),
        ]);
    }
}
