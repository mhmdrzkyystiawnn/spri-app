<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;

class VerificationQueryFilter
{
    /**
     * Menerapkan seluruh filter pencarian dan kriteria ke query builder.
     *
     * @param  Builder  $query
     * @param  array<string, mixed>  $filters
     * @return Builder
     */
    public function apply(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search']) && is_string($filters['search'])) {
            $term = trim($filters['search']);
            if ($term !== '') {
                $this->applySearch($query, $term);
            }
        }

        if (! empty($filters['status']) && is_string($filters['status'])) {
            $status = trim($filters['status']);
            if ($status !== '') {
                $this->applyStatus($query, $status);
            }
        }

        if (! empty($filters['document_type']) && is_string($filters['document_type'])) {
            $documentType = trim($filters['document_type']);
            if ($documentType !== '') {
                $this->applyDocumentType($query, $documentType);
            }
        }

        $dateFrom = ! empty($filters['date_from']) && is_string($filters['date_from']) ? trim($filters['date_from']) : null;
        $dateTo = ! empty($filters['date_to']) && is_string($filters['date_to']) ? trim($filters['date_to']) : null;
        if ($dateFrom !== null || $dateTo !== null) {
            $this->applyDateRange($query, $dateFrom, $dateTo);
        }

        if (! empty($filters['completeness']) && is_string($filters['completeness'])) {
            $completeness = trim($filters['completeness']);
            if ($completeness !== '') {
                $this->applyCompleteness($query, $completeness);
            }
        }

        return $query;
    }

    /**
     * Filter pencarian teks pada nomor resi/registrasi atau nama pemohon.
     */
    protected function applySearch(Builder $query, string $term): void
    {
        $query->where(function (Builder $q) use ($term) {
            $q->where('registration_code', 'like', "%{$term}%")
              ->orWhere('applicant_name', 'like', "%{$term}%");
        });
    }

    /**
     * Filter berdasarkan status permohonan verifikasi.
     */
    protected function applyStatus(Builder $query, string $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Filter berdasarkan jenis permohonan SPRI.
     */
    protected function applyDocumentType(Builder $query, string $documentType): void
    {
        $query->where('document_type', $documentType);
    }

    /**
     * Filter rentang tanggal pengajuan (created_at).
     * Menggunakan format ISO langsung untuk memanfaatkan indeks basis data.
     */
    protected function applyDateRange(Builder $query, ?string $dateFrom, ?string $dateTo): void
    {
        if ($dateFrom !== null) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo !== null) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }
    }

    /**
     * Filter berdasarkan status kelengkapan dokumen persyaratan fisik.
     *
     * - complete: Memiliki checklist dan seluruh persyaratan wajib (is_mandatory=true) telah tercentang (is_checked=true).
     * - incomplete: Tidak memiliki checklist atau memiliki minimal satu persyaratan wajib yang belum tercentang.
     */
    protected function applyCompleteness(Builder $query, string $completeness): void
    {
        if ($completeness === 'complete') {
            $query->whereHas('checklists')
                ->whereDoesntHave('checklists', function (Builder $q) {
                    $q->where('is_checked', false)
                      ->whereHas('requirement', function (Builder $req) {
                          $req->where('is_mandatory', true);
                      });
                });
        } elseif ($completeness === 'incomplete') {
            $query->where(function (Builder $q) {
                $q->whereDoesntHave('checklists')
                  ->orWhereHas('checklists', function (Builder $sub) {
                      $sub->where('is_checked', false)
                          ->whereHas('requirement', function (Builder $req) {
                              $req->where('is_mandatory', true);
                          });
                  });
            });
        }
    }
}
