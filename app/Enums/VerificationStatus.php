<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case PENDING = 'pending';
    case IN_REVIEW = 'in_review';
    case NEEDS_REVISION = 'needs_revision';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * Label representasi untuk antarmuka pengguna (Bahasa Indonesia).
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::IN_REVIEW => 'Sedang Diverifikasi',
            self::NEEDS_REVISION => 'Perlu Perbaikan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
        };
    }

    /**
     * Label ramah publik pada halaman cek status.
     */
    public function publicLabel(): string
    {
        return $this->label();
    }

    /**
     * Deskripsi status untuk transparansi layanan ke pemohon.
     */
    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Berkas permohonan telah tercatat dan sedang dalam antrean verifikasi petugas loket.',
            self::IN_REVIEW => 'Petugas verifikator sedang memeriksa keabsahan dan kelengkapan dokumen persyaratan SPRI.',
            self::NEEDS_REVISION => 'Terdapat dokumen yang kurang lengkap atau memerlukan perbaikan sesuai catatan petugas.',
            self::APPROVED => 'Verifikasi dokumen berhasil dan disetujui. Silakan menunggu tahapan layanan berikutnya.',
            self::REJECTED => 'Permohonan tidak dapat disetujui berdasarkan hasil pemeriksaan berkas imigrasi.',
        };
    }

    /**
     * CSS classes untuk status badge (gaya meja layanan resmi editorial).
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-50 text-amber-900 border-amber-300',
            self::IN_REVIEW => 'bg-sky-50 text-sky-900 border-sky-300',
            self::NEEDS_REVISION => 'bg-orange-50 text-orange-900 border-orange-300',
            self::APPROVED => 'bg-emerald-50 text-emerald-900 border-emerald-300',
            self::REJECTED => 'bg-rose-50 text-rose-900 border-rose-300',
        };
    }

    /**
     * Daftar seluruh nilai status yang valid.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
