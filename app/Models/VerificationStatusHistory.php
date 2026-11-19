<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'verification_id',
        'from_status',
        'to_status',
        'note',
        'changed_by',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => VerificationStatus::class,
            'to_status' => VerificationStatus::class,
            'changed_at' => 'datetime',
        ];
    }

    /**
     * Berkas verifikasi yang bersangkutan.
     */
    public function verification(): BelongsTo
    {
        return $this->belongsTo(Verification::class);
    }

    /**
     * Petugas yang melakukan perubahan status.
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
