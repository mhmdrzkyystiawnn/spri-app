<?php

namespace Database\Seeders;

use App\Enums\VerificationStatus;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationStatusHistory;
use Illuminate\Database\Seeder;

class VerificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('is_admin', true)->first() ?? User::factory()->admin()->create();

        $samples = [
            [
                'registration_code' => 'SPRI-2026-X7K9P2',
                'applicant_name' => 'Muhammad Rizky',
                'document_type' => 'SPRI Baru (Paspor Elektronik 48 Hal)',
                'status' => VerificationStatus::APPROVED,
                'public_notes' => 'Dokumen persyaratan lengkap dan absah. Silakan hadir pada jadwal sesi foto & biometrik.',
                'admin_notes' => 'Verifikasi KTP dan KK sinkron dengan database kependudukan. Siap lanjut biometrik.',
                'verified_at' => now()->subDay(),
                'created_by' => $admin->id,
                'histories' => [
                    [
                        'from_status' => null,
                        'to_status' => VerificationStatus::PENDING,
                        'note' => 'Pendaftaran permohonan baru di loket 2.',
                        'changed_at' => now()->subDays(3),
                    ],
                    [
                        'from_status' => VerificationStatus::PENDING,
                        'to_status' => VerificationStatus::IN_REVIEW,
                        'note' => 'Pemeriksaan fisik dokumen oleh verifikator Lantaskim.',
                        'changed_at' => now()->subDays(2),
                    ],
                    [
                        'from_status' => VerificationStatus::IN_REVIEW,
                        'to_status' => VerificationStatus::APPROVED,
                        'note' => 'Dokumen persyaratan lengkap dan absah. Silakan hadir pada jadwal sesi foto & biometrik.',
                        'changed_at' => now()->subDay(),
                    ],
                ],
            ],
            [
                'registration_code' => 'SPRI-2026-M3W1Q8',
                'applicant_name' => 'Siti Nurhaliza',
                'document_type' => 'Penggantian SPRI Habis Masa Berlaku',
                'status' => VerificationStatus::IN_REVIEW,
                'public_notes' => 'Berkas sedang dalam tahap penelitian fisik oleh verifikator seksi Lantaskim.',
                'admin_notes' => 'Paspor lama fisik telah diserahkan di loket 3. Menunggu validasi nomor seri lama.',
                'verified_at' => null,
                'created_by' => $admin->id,
                'histories' => [
                    [
                        'from_status' => null,
                        'to_status' => VerificationStatus::PENDING,
                        'note' => 'Pendaftaran permohonan penggantian paspor.',
                        'changed_at' => now()->subDays(2),
                    ],
                    [
                        'from_status' => VerificationStatus::PENDING,
                        'to_status' => VerificationStatus::IN_REVIEW,
                        'note' => 'Berkas sedang dalam tahap penelitian fisik oleh verifikator seksi Lantaskim.',
                        'changed_at' => now()->subHours(12),
                    ],
                ],
            ],
            [
                'registration_code' => 'SPRI-2026-B5N4T9',
                'applicant_name' => 'Budi Santoso',
                'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
                'status' => VerificationStatus::NEEDS_REVISION,
                'public_notes' => 'Lampiran Akta Lahir buram dan tidak terbaca jelas. Mohon serahkan salinan legalisir di loket informasi.',
                'admin_notes' => 'Scan akta lahir resolusi rendah, nama orang tua terpotong.',
                'verified_at' => null,
                'created_by' => $admin->id,
                'histories' => [
                    [
                        'from_status' => null,
                        'to_status' => VerificationStatus::PENDING,
                        'note' => 'Pendaftaran berkas loket.',
                        'changed_at' => now()->subDays(2),
                    ],
                    [
                        'from_status' => VerificationStatus::PENDING,
                        'to_status' => VerificationStatus::NEEDS_REVISION,
                        'note' => 'Lampiran Akta Lahir buram dan tidak terbaca jelas. Mohon serahkan salinan legalisir di loket informasi.',
                        'changed_at' => now()->subHours(6),
                    ],
                ],
            ],
            [
                'registration_code' => 'SPRI-2026-R8L2J5',
                'applicant_name' => 'Dewi Sartika',
                'document_type' => 'SPRI Baru (Paspor Elektronik 48 Hal)',
                'status' => VerificationStatus::PENDING,
                'public_notes' => 'Permohonan telah tercatat di sistem loket imigrasi.',
                'admin_notes' => 'Menunggu giliran pemeriksaan berkas loket antrean A-12.',
                'verified_at' => null,
                'created_by' => $admin->id,
                'histories' => [
                    [
                        'from_status' => null,
                        'to_status' => VerificationStatus::PENDING,
                        'note' => 'Permohonan telah tercatat di sistem loket imigrasi.',
                        'changed_at' => now()->subHours(3),
                    ],
                ],
            ],
            [
                'registration_code' => 'SPRI-2026-K1D6V4',
                'applicant_name' => 'Joko Susilo',
                'document_type' => 'Penggantian SPRI Hilang/Rusak',
                'status' => VerificationStatus::REJECTED,
                'public_notes' => 'Permohonan ditolak karena belum melampirkan Surat Laporan Kehilangan Kepolisian dan BAP paspor rusak/hilang.',
                'admin_notes' => 'Pemohon belum mengikuti sidang BAP paspor hilang sesuai ketentuan Ditjen Imigrasi.',
                'verified_at' => now()->subHours(5),
                'created_by' => $admin->id,
                'histories' => [
                    [
                        'from_status' => null,
                        'to_status' => VerificationStatus::PENDING,
                        'note' => 'Pendaftaran penggantian paspor hilang.',
                        'changed_at' => now()->subDays(1),
                    ],
                    [
                        'from_status' => VerificationStatus::PENDING,
                        'to_status' => VerificationStatus::REJECTED,
                        'note' => 'Permohonan ditolak karena belum melampirkan Surat Laporan Kehilangan Kepolisian dan BAP paspor rusak/hilang.',
                        'changed_at' => now()->subHours(5),
                    ],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $histories = $sample['histories'] ?? [];
            unset($sample['histories']);

            $verification = Verification::firstOrCreate(
                ['registration_code' => $sample['registration_code']],
                $sample
            );

            foreach ($histories as $h) {
                VerificationStatusHistory::firstOrCreate(
                    [
                        'verification_id' => $verification->id,
                        'to_status' => $h['to_status'],
                        'changed_at' => $h['changed_at'],
                    ],
                    [
                        'from_status' => $h['from_status'],
                        'note' => $h['note'],
                        'changed_by' => $admin->id,
                    ]
                );
            }
        }
    }
}
