<?php

namespace App\Models;

use App\Services\PerdimDocumentClassificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerdimApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'verification_id',
        // Seksi 1: Permohonan & SPRI
        'application_type',
        'passport_type',
        'replacement_reason',
        'change_reason',
        // Seksi 2: Identitas Diri Pemohon
        'full_name',
        'alias_name',
        'gender',
        'height_cm',
        'birth_place',
        'birth_date',
        'nik',
        'identity_number',
        'identity_issued_place',
        'identity_issued_date',
        'identity_expiry_date',
        'occupation',
        'workplace_address',
        'office_phone',
        'indonesia_address',
        'indonesia_phone',
        'overseas_address',
        'overseas_phone',
        'email',
        'marital_status',
        // Seksi 3: Data Keluarga
        'mother_name',
        'mother_nationality',
        'mother_birth_place',
        'mother_birth_date',
        'father_name',
        'father_nationality',
        'father_birth_place',
        'father_birth_date',
        'father_address',
        'father_phone',
        'spouse_name',
        'spouse_nationality',
        'spouse_birth_place',
        'spouse_birth_date',
        'spouse_address',
        // Seksi 4: Perubahan Data, Kontak Darurat & Pernyataan
        'change_details',
        'emergency_id_name',
        'emergency_id_relationship',
        'emergency_id_address',
        'emergency_id_phone',
        'emergency_overseas_name',
        'emergency_overseas_address',
        'emergency_overseas_phone',
        'declaration_accepted',
        'declaration_accepted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'identity_issued_date' => 'date',
            'identity_expiry_date' => 'date',
            'mother_birth_date' => 'date',
            'father_birth_date' => 'date',
            'spouse_birth_date' => 'date',
            'declaration_accepted_at' => 'datetime',
            'declaration_accepted' => 'boolean',
            'height_cm' => 'integer',
            'change_details' => 'array',
        ];
    }

    /**
     * Berkas verifikasi induk yang menaungi permohonan PERDIM 14 ini.
     */
    public function verification(): BelongsTo
    {
        return $this->belongsTo(Verification::class);
    }

    /**
     * Berkas-berkas dokumen persyaratan yang diunggah untuk permohonan ini.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PerdimDocument::class);
    }

    /**
     * Penyamaran NIK untuk tampilan tanda terima publik (keamanan privasi).
     * Contoh: "3171011708950001" -> "3171************"
     */
    public function getMaskedNikAttribute(): ?string
    {
        if (! $this->nik) {
            return null;
        }

        $len = strlen($this->nik);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return substr($this->nik, 0, 4).str_repeat('*', $len - 4);
    }

    /**
     * Mutator untuk memastikan kompatibilitas nilai status perkawinan belum_kawin <-> tidak_kawin.
     */
    public function setMaritalStatusAttribute(mixed $value): void
    {
        $status = strtolower(trim((string) $value));
        if ($status === 'belum_kawin') {
            $status = 'tidak_kawin';
        }
        $this->attributes['marital_status'] = $status;
    }

    /**
     * Kategori klasifikasi pemohon paspor:
     * - 'A' = Dewasa (usia >= 17 atau pernah/sudah kawin)
     * - 'B' = Anak-Anak (usia < 17 dan belum/tidak kawin)
     */
    public function getApplicantCategoryAttribute(): string
    {
        if (! $this->birth_date) {
            return PerdimDocumentClassificationService::CATEGORY_ADULT;
        }

        return app(PerdimDocumentClassificationService::class)->determineCategory(
            $this->birth_date,
            $this->marital_status ?? 'tidak_kawin'
        );
    }

    /**
     * Label teks kategori klasifikasi pemohon.
     */
    public function getApplicantCategoryLabelAttribute(): string
    {
        return app(PerdimDocumentClassificationService::class)->getCategoryLabel(
            $this->applicant_category
        );
    }

    /**
     * Penyamaran nomor identitas lain untuk tampilan publik.
     */
    public function getMaskedIdentityNumberAttribute(): ?string
    {
        if (! $this->identity_number) {
            return null;
        }

        $len = strlen($this->identity_number);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return substr($this->identity_number, 0, 2).str_repeat('*', $len - 4).substr($this->identity_number, -2);
    }
}
