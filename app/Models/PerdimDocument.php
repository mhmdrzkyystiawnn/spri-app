<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerdimDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'perdim_application_id',
        'document_type',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'hash_sha256',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /**
     * Permohonan PERDIM 14 yang memiliki dokumen ini.
     */
    public function perdimApplication(): BelongsTo
    {
        return $this->belongsTo(PerdimApplication::class);
    }

    /**
     * Format ukuran file yang mudah dibaca (KB / MB).
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    /**
     * Label jenis dokumen resmi PERDIM 14 yang ramah pengguna.
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        $labels = [
            'ktp_pemohon' => 'KTP Elektronik Pemohon',
            'kartu_keluarga' => 'Kartu Keluarga (KK)',
            'akta_kelahiran' => 'Akta Kelahiran',
            'buku_nikah_pemohon' => 'Buku Nikah Pemohon',
            'paspor_lama' => 'Paspor Lama',
            'surat_pewarganegaraan' => 'Surat Pewarganegaraan',
            'penetapan_ganti_nama' => 'Penetapan Ganti Nama',
            'ktp_ayah' => 'KTP Ayah Kandung',
            'ktp_ibu' => 'KTP Ibu Kandung',
            'ktp_wali' => 'KTP Wali Sah',
            'akta_kematian_orang_tua' => 'Akta Kematian Orang Tua',
            'buku_nikah_orang_tua' => 'Buku Nikah Orang Tua',
            'akta_cerai_orang_tua' => 'Akta Cerai Orang Tua',
            'putusan_hak_asuh' => 'Putusan Hak Asuh Pengadilan',
            'penetapan_pengadilan_wali' => 'Penetapan Pengadilan Wali',
            'paspor_orang_tua' => 'Paspor Orang Tua',
            'surat_pernyataan_orang_tua' => 'Surat Pernyataan Orang Tua',
        ];

        return $labels[$this->document_type] ?? ucwords(str_replace('_', ' ', (string) $this->document_type));
    }
}
