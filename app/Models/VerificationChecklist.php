<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'verification_id',
        'document_requirement_id',
        'is_checked',
        'notes',
        'checked_by',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'is_checked' => 'boolean',
            'checked_at' => 'datetime',
        ];
    }

    /**
     * Berkas verifikasi pemilik checklist ini.
     */
    public function verification(): BelongsTo
    {
        return $this->belongsTo(Verification::class, 'verification_id');
    }

    /**
     * Definisi master persyaratan dokumen.
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(DocumentRequirement::class, 'document_requirement_id');
    }

    /**
     * Petugas yang melakukan checklist fisik.
     */
    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /**
     * Tandai persyaratan telah diperiksa dan terpenuhi.
     */
    public function markChecked(User $user, ?string $notes = null): self
    {
        $this->is_checked = true;
        $this->checked_by = $user->id;
        $this->checked_at = now();

        if ($notes !== null) {
            $this->notes = $notes;
        }

        return $this;
    }

    /**
     * Batalkan tanda verifikasi persyaratan.
     */
    public function markUnchecked(?string $notes = null): self
    {
        $this->is_checked = false;
        $this->checked_by = null;
        $this->checked_at = null;

        if ($notes !== null) {
            $this->notes = $notes;
        }

        return $this;
    }
}
