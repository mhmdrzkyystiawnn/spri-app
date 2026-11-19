<?php

namespace App\Services;

use Carbon\Carbon;

class PerdimDocumentClassificationService
{
    public const CATEGORY_ADULT = 'A';

    public const CATEGORY_CHILD = 'B';

    /**
     * Menentukan kategori pemohon:
     * - Kategori A (Dewasa): Usia >= 17 tahun ATAU sudah/pernah kawin.
     * - Kategori B (Anak): Usia < 17 tahun DAN belum/tidak pernah kawin.
     */
    public function determineCategory(string|\DateTimeInterface $birthDate, string $maritalStatus): string
    {
        $dob = $birthDate instanceof \DateTimeInterface ? $birthDate : Carbon::parse($birthDate);
        $age = $dob->age;
        $status = strtolower(trim($maritalStatus));

        if ($status === 'belum_kawin') {
            $status = 'tidak_kawin';
        }

        $isMarried = in_array($status, ['kawin', 'cerai_hidup', 'cerai_mati'], true);

        if ($age >= 17 || $isMarried) {
            return self::CATEGORY_ADULT;
        }

        return self::CATEGORY_CHILD;
    }

    /**
     * Mendapatkan label kategori pemohon.
     */
    public function getCategoryLabel(string $category): string
    {
        return $category === self::CATEGORY_ADULT
            ? 'Dewasa (Kategori A)'
            : 'Anak-Anak (Kategori B)';
    }

    /**
     * Daftar dokumen yang valid dan diwajibkan untuk setiap kategori.
     *
     * @param  string  $category  'A' atau 'B'
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function getCategoryRequirementRules(string $category, array $context = []): array
    {
        if ($category === self::CATEGORY_ADULT) {
            return [
                'category' => self::CATEGORY_ADULT,
                'name' => 'Dewasa (Kategori A)',
                'mandatory' => ['ktp_pemohon', 'kartu_keluarga'],
                'one_of' => ['akta_kelahiran', 'buku_nikah_pemohon', 'paspor_lama'],
                'optional' => ['surat_pewarganegaraan', 'penetapan_ganti_nama'],
            ];
        }

        return [
            'category' => self::CATEGORY_CHILD,
            'name' => 'Anak-Anak (Kategori B)',
            'mandatory' => ['kartu_keluarga', 'akta_kelahiran'],
            'parent_identity' => ['ktp_ayah', 'ktp_ibu', 'ktp_wali', 'akta_kematian_orang_tua'],
            'parent_marriage_or_legal' => ['buku_nikah_orang_tua', 'akta_cerai_orang_tua', 'putusan_hak_asuh', 'penetapan_pengadilan_wali'],
            'optional' => ['paspor_orang_tua', 'surat_pernyataan_orang_tua'],
        ];
    }

    /**
     * Memvalidasi apakah kumpulan jenis dokumen yang diunggah memenuhi persyaratan kategori.
     *
     * @param  string  $category  'A' atau 'B'
     * @param  array<string>  $uploadedTypes  Array string tipe dokumen yang diunggah
     * @param  array<string, mixed>  $context
     * @return array{is_valid: bool, errors: array<string>, missing: array<string>}
     */
    public function validateUploadedDocuments(string $category, array $uploadedTypes, array $context = []): array
    {
        $uploaded = array_unique($uploadedTypes);
        $errors = [];
        $missing = [];

        if ($category === self::CATEGORY_ADULT) {
            // Wajib KTP Pemohon & Kartu Keluarga
            if (! in_array('ktp_pemohon', $uploaded, true)) {
                $missing[] = 'KTP Pemohon (ktp_pemohon)';
                $errors[] = 'Kategori A (Dewasa) wajib melampirkan KTP Pemohon.';
            }
            if (! in_array('kartu_keluarga', $uploaded, true)) {
                $missing[] = 'Kartu Keluarga (kartu_keluarga)';
                $errors[] = 'Kategori A (Dewasa) wajib melampirkan Kartu Keluarga.';
            }

            // Minimal 1 dari: akta_kelahiran, buku_nikah_pemohon, paspor_lama
            $hasSupporting = in_array('akta_kelahiran', $uploaded, true)
                || in_array('buku_nikah_pemohon', $uploaded, true)
                || in_array('paspor_lama', $uploaded, true);

            if (! $hasSupporting) {
                $missing[] = 'Salah satu dari: Akta Kelahiran, Buku Nikah Pemohon, atau Paspor Lama';
                $errors[] = 'Kategori A (Dewasa) wajib melampirkan minimal salah satu dokumen pendukung: Akta Kelahiran, Buku Nikah Pemohon, atau Paspor Lama.';
            }
        } else {
            // Kategori B (Anak)
            // Wajib Kartu Keluarga & Akta Kelahiran
            if (! in_array('kartu_keluarga', $uploaded, true)) {
                $missing[] = 'Kartu Keluarga (kartu_keluarga)';
                $errors[] = 'Kategori B (Anak) wajib melampirkan Kartu Keluarga.';
            }
            if (! in_array('akta_kelahiran', $uploaded, true)) {
                $missing[] = 'Akta Kelahiran (akta_kelahiran)';
                $errors[] = 'Kategori B (Anak) wajib melampirkan Akta Kelahiran.';
            }

            // Identitas Orang Tua / Wali
            $hasParentId = in_array('ktp_ayah', $uploaded, true)
                || in_array('ktp_ibu', $uploaded, true)
                || in_array('ktp_wali', $uploaded, true)
                || in_array('akta_kematian_orang_tua', $uploaded, true);

            if (! $hasParentId) {
                $missing[] = 'KTP Ayah/Ibu/Wali atau Akta Kematian Orang Tua';
                $errors[] = 'Kategori B (Anak) wajib melampirkan identitas orang tua (KTP Ayah/Ibu), KTP Wali, atau Akta Kematian Orang Tua.';
            }

            // Dokumen Pernikahan / Legalitas Ortu
            // Jika ada perwalian: wajib penetapan_pengadilan_wali + ktp_wali
            $isGuardianship = in_array('ktp_wali', $uploaded, true) || in_array('penetapan_pengadilan_wali', $uploaded, true);
            if ($isGuardianship) {
                if (! in_array('penetapan_pengadilan_wali', $uploaded, true)) {
                    $missing[] = 'Penetapan Pengadilan tentang Perwalian (penetapan_pengadilan_wali)';
                    $errors[] = 'Permohonan anak dengan wali wajib melampirkan Penetapan Pengadilan tentang Perwalian.';
                }
                if (! in_array('ktp_wali', $uploaded, true)) {
                    $missing[] = 'KTP Wali (ktp_wali)';
                    $errors[] = 'Permohonan anak dengan wali wajib melampirkan KTP Wali.';
                }
            } else {
                // Bukan perwalian: buku nikah ortu ATAU akta cerai / putusan hak asuh ATAU akta kematian
                $hasMarriageOrLegal = in_array('buku_nikah_orang_tua', $uploaded, true)
                    || in_array('akta_cerai_orang_tua', $uploaded, true)
                    || in_array('putusan_hak_asuh', $uploaded, true)
                    || in_array('akta_kematian_orang_tua', $uploaded, true);

                if (! $hasMarriageOrLegal) {
                    $missing[] = 'Buku Nikah Orang Tua / Akta Perceraian & Putusan Hak Asuh / Akta Kematian';
                    $errors[] = 'Kategori B (Anak) wajib melampirkan Buku Nikah Orang Tua, atau Akta Perceraian/Putusan Hak Asuh, atau Akta Kematian Orang Tua.';
                }
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'missing' => $missing,
        ];
    }
}
