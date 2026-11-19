<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class NikRule implements DataAwareRule, ValidationRule
{
    /**
     * Seluruh data payload permintaan untuk pencocokan silang (cross-validation).
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Menetapkan data payload permintaan.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Validasi nomor NIK 16 digit sesuai struktur standar kependudukan Indonesia:
     * - Panjang tepat 16 digit angka (numerik).
     * - Digit 1-6: Kode Wilayah (Provinsi, Kab/Kota, Kecamatan).
     * - Digit 7-12: Tanggal Lahir (DDMMYY).
     *   * Pria: Tanggal 01 s.d. 31.
     *   * Wanita: Tanggal lahir + 40 (41 s.d. 71).
     *   * Bulan: 01 s.d. 12.
     *   * Tahun: 2 digit terakhir tahun lahir.
     * - Digit 13-16: Nomor urut registrasi (0001 s.d. 9999).
     * - Cross-check terhadap tanggal lahir dan jenis kelamin pemohon bila tersedia.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail('Format NIK harus berupa string atau angka.');

            return;
        }

        $nik = (string) $value;

        // 1. Validasi panjang tepat 16 digit dan hanya numerik
        if (! preg_match('/^[0-9]{16}$/', $nik)) {
            $fail('Format NIK harus terdiri dari tepat 16 digit angka tanpa huruf atau karakter khusus.');

            return;
        }

        // 2. Ekstraksi segmen tanggal lahir (digit 7 s.d. 12, indeks 6..11)
        $daySegment = (int) substr($nik, 6, 2);
        $monthSegment = (int) substr($nik, 8, 2);
        $yearSegment = substr($nik, 10, 2);

        // Validasi batas logis bulan (01 - 12)
        if ($monthSegment < 1 || $monthSegment > 12) {
            $fail('Segmen bulan pada NIK (digit 9-10) tidak valid, harus bernilai antara 01 sampai 12.');

            return;
        }

        // Validasi batas logis tanggal lahir: 01-31 (Pria) atau 41-71 (Wanita)
        $isFemaleRange = ($daySegment >= 41 && $daySegment <= 71);
        $isMaleRange = ($daySegment >= 1 && $daySegment <= 31);

        if (! $isMaleRange && ! $isFemaleRange) {
            $fail('Segmen tanggal lahir pada NIK (digit 7-8) tidak valid. Harus bernilai 01-31 untuk laki-laki atau 41-71 untuk perempuan.');

            return;
        }

        // 3. Pencocokan silang terhadap data diri pemohon (birth_date & gender) jika tersedia di payload
        $birthDateStr = $this->data['birth_date'] ?? null;
        $gender = isset($this->data['gender']) ? strtoupper(trim((string) $this->data['gender'])) : null;

        if ($birthDateStr && $gender && in_array($gender, ['L', 'P'], true)) {
            try {
                $dob = Carbon::parse($birthDateStr);
                $dobDay = (int) $dob->format('d');
                $dobMonth = (int) $dob->format('m');
                $dobYear = $dob->format('y'); // 2 digit tahun

                // Validasi kesesuaian gender dengan segmen NIK
                if ($gender === 'L' && $isFemaleRange) {
                    $fail('Segmen tanggal lahir pada NIK mengindikasikan jenis kelamin Perempuan (+40), tidak cocok dengan jenis kelamin Laki-laki pemohon.');

                    return;
                }

                if ($gender === 'P' && $isMaleRange) {
                    $fail('Segmen tanggal lahir pada NIK mengindikasikan jenis kelamin Laki-laki, tidak cocok dengan jenis kelamin Perempuan pemohon (tanggal lahir seharusnya ditambah 40).');

                    return;
                }

                // Hitung segmen yang diharapkan
                $expectedDay = $gender === 'P' ? ($dobDay + 40) : $dobDay;
                $expectedSegment = sprintf('%02d%02d%s', $expectedDay, $dobMonth, $dobYear);
                $actualSegment = substr($nik, 6, 6);

                if ($actualSegment !== $expectedSegment) {
                    $fail("Segmen tanggal lahir pada NIK ({$actualSegment}) tidak cocok dengan tanggal lahir ({$dob->format('Y-m-d')}) dan jenis kelamin ({$gender}) pemohon. Seharusnya: {$expectedSegment}.");

                    return;
                }
            } catch (\Throwable $e) {
                // Biarkan aturan date_format pada birth_date menangani jika tanggal salah format
            }
        }
    }
}
