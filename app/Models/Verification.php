<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use App\Observers\VerificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([VerificationObserver::class])]
class Verification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_code',
        'applicant_name',
        'document_type',
        'status',
        'public_notes',
        'admin_notes',
        'verified_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Petugas yang mendaftarkan data verifikasi.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Riwayat audit log mutasi dokumen ini.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class)->latest();
    }

    /**
     * Riwayat kronologis perubahan status permohonan.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(VerificationStatusHistory::class)
            ->orderBy('changed_at', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Checklist persyaratan dokumen untuk verifikasi berkas ini.
     */
    public function checklists(): HasMany
    {
        return $this->hasMany(VerificationChecklist::class, 'verification_id');
    }

    /**
     * Data permohonan digital formulir PERDIM 14 (jika diajukan via portal publik).
     */
    public function perdimApplication(): HasOne
    {
        return $this->hasOne(PerdimApplication::class);
    }

    /**
     * Scope filter berdasarkan status verifikasi.
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Scope pencarian berdasarkan kode registrasi atau nama pemohon.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('registration_code', 'like', "%{$term}%")
              ->orWhere('applicant_name', 'like', "%{$term}%");
        });
    }

    /**
     * Penyamaran nama pemohon untuk keamanan privasi pada halaman publik.
     * Contoh: "Ahmad Dahlan" -> "A**** D*****"
     */
    public function getMaskedApplicantNameAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->applicant_name));
        $maskedWords = [];

        foreach ($words as $word) {
            $len = mb_strlen($word);
            if ($len <= 2) {
                $maskedWords[] = mb_substr($word, 0, 1) . '*';
            } else {
                $maskedWords[] = mb_substr($word, 0, 1) . str_repeat('*', min(6, $len - 1));
            }
        }

        return implode(' ', $maskedWords);
    }
}
