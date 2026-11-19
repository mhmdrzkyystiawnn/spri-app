<?php

namespace Database\Factories;

use App\Models\PerdimApplication;
use App\Models\PerdimDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PerdimDocument>
 */
class PerdimDocumentFactory extends Factory
{
    protected $model = PerdimDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['ktp_pemohon', 'kartu_keluarga', 'akta_kelahiran', 'buku_nikah_pemohon', 'paspor_lama'];
        $docType = fake()->randomElement($types);
        $fileName = Str::slug($docType).'_'.Str::random(10).'.pdf';

        return [
            'perdim_application_id' => PerdimApplication::factory(),
            'document_type' => $docType,
            'original_name' => $fileName,
            'file_path' => 'perdim_documents/2026/SPRI-2026-TEST01/'.$fileName,
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(50000, 1500000),
            'hash_sha256' => hash('sha256', Str::random(32)),
        ];
    }
}
