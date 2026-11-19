<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type',
        'code',
        'name',
        'is_mandatory',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope untuk persyaratan yang aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk memfilter berdasarkan jenis dokumen SPRI.
     */
    public function scopeForDocumentType(Builder $query, string $documentType): Builder
    {
        return $query->where('document_type', $documentType);
    }

    /**
     * Scope untuk mengurutkan berdasarkan urutan tampilan.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Relasi ke checklist instance pada verifikasi berkas.
     */
    public function checklists(): HasMany
    {
        return $this->hasMany(VerificationChecklist::class, 'document_requirement_id');
    }
}
