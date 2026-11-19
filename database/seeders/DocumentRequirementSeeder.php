<?php

namespace Database\Seeders;

use App\Models\DocumentRequirement;
use Illuminate\Database\Seeder;

class DocumentRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requirements = [
            // 1. SPRI Baru (Paspor Biasa 48 Hal)
            [
                'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
                'code' => 'BARU-KTP',
                'name' => 'KTP Elektronik / Surat Keterangan Perekaman Asli',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
                'code' => 'BARU-KK',
                'name' => 'Kartu Keluarga (KK) Asli / Salinan Terlegalisir',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
                'code' => 'BARU-AKTA',
                'name' => 'Akta Kelahiran / Akta Perkawinan / Buku Nikah / Ijazah',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'document_type' => 'SPRI Baru (Paspor Biasa 48 Hal)',
                'code' => 'BARU-REKOM',
                'name' => 'Surat Rekomendasi Instansi / Tempat Bekerja',
                'is_mandatory' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],

            // 2. SPRI Baru (Paspor Elektronik 48 Hal)
            [
                'document_type' => 'SPRI Baru (Paspor Elektronik 48 Hal)',
                'code' => 'EL-KTP',
                'name' => 'KTP Elektronik / Surat Keterangan Perekaman Asli',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'document_type' => 'SPRI Baru (Paspor Elektronik 48 Hal)',
                'code' => 'EL-KK',
                'name' => 'Kartu Keluarga (KK) Asli / Salinan Terlegalisir',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'document_type' => 'SPRI Baru (Paspor Elektronik 48 Hal)',
                'code' => 'EL-AKTA',
                'name' => 'Akta Kelahiran / Akta Perkawinan / Buku Nikah / Ijazah',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'document_type' => 'SPRI Baru (Paspor Elektronik 48 Hal)',
                'code' => 'EL-REKOM',
                'name' => 'Surat Rekomendasi Instansi / Tempat Bekerja',
                'is_mandatory' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],

            // 3. Penggantian SPRI Habis Masa Berlaku
            [
                'document_type' => 'Penggantian SPRI Habis Masa Berlaku',
                'code' => 'GANTI-KTP',
                'name' => 'KTP Elektronik Asli',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'document_type' => 'Penggantian SPRI Habis Masa Berlaku',
                'code' => 'GANTI-PASPOR',
                'name' => 'Fisik Paspor Lama Asli (Terbitan Dalam Negeri >2009)',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'document_type' => 'Penggantian SPRI Habis Masa Berlaku',
                'code' => 'GANTI-DOMISILI',
                'name' => 'Surat Keterangan Domisili (Bila KTP di luar wilayah kantor)',
                'is_mandatory' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],

            // 4. Penggantian SPRI Halaman Penuh
            [
                'document_type' => 'Penggantian SPRI Halaman Penuh',
                'code' => 'PENUH-KTP',
                'name' => 'KTP Elektronik Asli',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'document_type' => 'Penggantian SPRI Halaman Penuh',
                'code' => 'PENUH-PASPOR',
                'name' => 'Fisik Paspor Lama Asli yang Halamannya Telah Penuh',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],

            // 5. Penggantian SPRI Hilang/Rusak
            [
                'document_type' => 'Penggantian SPRI Hilang/Rusak',
                'code' => 'HILANG-KTP',
                'name' => 'KTP Elektronik Asli',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'document_type' => 'Penggantian SPRI Hilang/Rusak',
                'code' => 'HILANG-KK',
                'name' => 'Kartu Keluarga (KK) Asli',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'document_type' => 'Penggantian SPRI Hilang/Rusak',
                'code' => 'HILANG-STPLK',
                'name' => 'Surat Tanda Penerimaan Laporan Kehilangan (STPLK) Kepolisian',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'document_type' => 'Penggantian SPRI Hilang/Rusak',
                'code' => 'HILANG-BAP',
                'name' => 'Berita Acara Pemeriksaan (BAP) Paspor Hilang/Rusak dari Wasdakim',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'document_type' => 'Penggantian SPRI Hilang/Rusak',
                'code' => 'HILANG-FISIK-RUSAK',
                'name' => 'Fisik Paspor yang Rusak (Khusus Permohonan Penggantian Rusak)',
                'is_mandatory' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],

            // 6. Surat Perjalanan Laksana Paspor (SPLP)
            [
                'document_type' => 'Surat Perjalanan Laksana Paspor (SPLP)',
                'code' => 'SPLP-IDENTITAS',
                'name' => 'Bukti Dokumen Kewarganegaraan Indonesia / Surat Bukti Domisili',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'document_type' => 'Surat Perjalanan Laksana Paspor (SPLP)',
                'code' => 'SPLP-PENGANTAR',
                'name' => 'Surat Pengantar / Bukti Pemulangan dari Perwakilan RI atau Instansi Resmi',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'document_type' => 'Surat Perjalanan Laksana Paspor (SPLP)',
                'code' => 'SPLP-FOTO',
                'name' => 'Pasfoto Berwarna Formal Latar Belakang Putih 4x6',
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($requirements as $req) {
            DocumentRequirement::updateOrCreate(
                [
                    'document_type' => $req['document_type'],
                    'code' => $req['code'],
                ],
                $req
            );
        }
    }
}
