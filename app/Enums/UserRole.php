<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case VERIFIKATOR = 'verifikator';
    case PIMPINAN = 'pimpinan';

    /**
     * Label peran pengguna dalam Bahasa Indonesia.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator Sistem',
            self::VERIFIKATOR => 'Petugas Verifikator',
            self::PIMPINAN => 'Kepala Seksi / Pimpinan',
        };
    }

    /**
     * CSS classes untuk badge peran.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::ADMIN => 'bg-red-50 text-red-800 border-red-300',
            self::VERIFIKATOR => 'bg-sky-50 text-sky-800 border-sky-300',
            self::PIMPINAN => 'bg-purple-50 text-purple-800 border-purple-300',
        };
    }

    /**
     * Daftar seluruh nilai peran yang valid.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
