<?php

namespace App\Services;

use App\Models\Verification;
use RuntimeException;

class RegistrationCodeGenerator
{
    public const DEFAULT_MAX_ATTEMPTS = 5;

    /**
     * Menghasilkan nomor registrasi resmi SPRI dengan format:
     * SPRI-YYYY-XXXXXX (SPRI + tahun berjalan 4 digit + 6 karakter uppercase alfanumerik acak).
     *
     * Dilengkapi mekanisme collision retry untuk menjamin keunikan kode pada tabel verifications.
     *
     * @param  int|null  $year  Tahun permohonan (opsional, default: tahun saat ini)
     * @param  int  $maxAttempts  Batas maksimal percobaan retry jika terjadi duplikasi kode
     *
     * @throws RuntimeException Jika nomor registrasi unik gagal didapatkan setelah max attempts
     */
    public function generate(?int $year = null, int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS): string
    {
        $targetYear = $year ?? (int) date('Y');

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $randomSegment = $this->generateRandomSegment(6);
            $candidateCode = sprintf('SPRI-%04d-%s', $targetYear, $randomSegment);

            if (! Verification::query()->where('registration_code', $candidateCode)->exists()) {
                return $candidateCode;
            }
        }

        throw new RuntimeException("Gagal menghasilkan nomor registrasi unik setelah {$maxAttempts} percobaan.");
    }

    /**
     * Menghasilkan string acak alfanumerik uppercase [A-Z0-9] yang aman secara kriptografis.
     */
    protected function generateRandomSegment(int $length = 6): string
    {
        $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxIndex = strlen($pool) - 1;
        $segment = '';

        for ($i = 0; $i < $length; $i++) {
            $segment .= $pool[random_int(0, $maxIndex)];
        }

        return $segment;
    }
}
