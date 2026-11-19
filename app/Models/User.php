<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Helper untuk memeriksa apakah pengguna berstatus admin.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_active && ($this->role === UserRole::ADMIN || (bool) $this->is_admin);
    }

    /**
     * Helper untuk memeriksa apakah pengguna berstatus verifikator.
     */
    public function isVerifikator(): bool
    {
        return (bool) $this->is_active && $this->role === UserRole::VERIFIKATOR;
    }

    /**
     * Helper untuk memeriksa apakah pengguna berstatus pimpinan.
     */
    public function isPimpinan(): bool
    {
        return (bool) $this->is_active && $this->role === UserRole::PIMPINAN;
    }

    /**
     * Helper untuk memeriksa apakah pengguna memiliki hak masuk meja layanan admin.
     */
    public function canAccessAdmin(): bool
    {
        return (bool) $this->is_active && (
            $this->role === UserRole::ADMIN ||
            $this->role === UserRole::VERIFIKATOR ||
            $this->role === UserRole::PIMPINAN ||
            (bool) $this->is_admin
        );
    }

    /**
     * Relasi verifikasi yang dibuat oleh pengguna.
     */
    public function verifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Verification::class, 'created_by');
    }

    /**
     * Relasi audit logs yang dilakukan pengguna.
     */
    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    /**
     * Relasi riwayat status yang diubah oleh pengguna.
     */
    public function statusHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VerificationStatusHistory::class, 'changed_by');
    }
}
