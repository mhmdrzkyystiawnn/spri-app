<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVerificationRequest;
use App\Http\Requests\UpdateVerificationRequest;
use App\Http\Requests\VerificationSearchRequest;
use App\Models\PerdimDocument;
use App\Models\Verification;
use App\Queries\VerificationQueryFilter;
use App\Services\VerificationChecklistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class VerificationController extends Controller
{
    /**
     * Daftar jenis dokumen SPRI standar.
     */
    public const DOCUMENT_TYPES = [
        'SPRI Baru (Paspor Biasa 48 Hal)',
        'SPRI Baru (Paspor Elektronik 48 Hal)',
        'Penggantian SPRI Habis Masa Berlaku',
        'Penggantian SPRI Halaman Penuh',
        'Penggantian SPRI Hilang/Rusak',
        'Surat Perjalanan Laksana Paspor (SPLP)',
    ];

    /**
     * Menampilkan daftar verifikasi permohonan SPRI dengan filter dan pencarian lanjutan.
     */
    public function index(VerificationSearchRequest $request, VerificationQueryFilter $filter): View
    {
        Gate::authorize('viewAny', Verification::class);

        $filters = $request->validated();

        $query = $filter->apply(
            Verification::query()->with('creator'),
            $filters
        )->latest('id');

        $verifications = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Verification::count(),
            'pending' => Verification::where('status', VerificationStatus::PENDING->value)->count(),
            'in_review' => Verification::where('status', VerificationStatus::IN_REVIEW->value)->count(),
            'needs_revision' => Verification::where('status', VerificationStatus::NEEDS_REVISION->value)->count(),
            'approved' => Verification::where('status', VerificationStatus::APPROVED->value)->count(),
            'rejected' => Verification::where('status', VerificationStatus::REJECTED->value)->count(),
        ];

        $currentStatus = $filters['status'] ?? null;
        $searchTerm = $filters['search'] ?? null;
        $currentDocumentType = $filters['document_type'] ?? null;
        $currentDateFrom = $filters['date_from'] ?? null;
        $currentDateTo = $filters['date_to'] ?? null;
        $currentCompleteness = $filters['completeness'] ?? null;

        $hasActiveFilters = ! empty($searchTerm)
            || ! empty($currentStatus)
            || ! empty($currentDocumentType)
            || ! empty($currentDateFrom)
            || ! empty($currentDateTo)
            || ! empty($currentCompleteness);

        return view('admin.verifications.index', [
            'verifications' => $verifications,
            'stats' => $stats,
            'filters' => $filters,
            'currentStatus' => $currentStatus,
            'searchTerm' => $searchTerm,
            'currentDocumentType' => $currentDocumentType,
            'currentDateFrom' => $currentDateFrom,
            'currentDateTo' => $currentDateTo,
            'currentCompleteness' => $currentCompleteness,
            'hasActiveFilters' => $hasActiveFilters,
            'statuses' => VerificationStatus::cases(),
            'documentTypes' => self::DOCUMENT_TYPES,
        ]);
    }

    /**
     * Form tambah berkas verifikasi baru.
     */
    public function create(): View
    {
        Gate::authorize('create', Verification::class);

        return view('admin.verifications.create', [
            'statuses' => VerificationStatus::cases(),
            'documentTypes' => self::DOCUMENT_TYPES,
        ]);
    }

    /**
     * Simpan data verifikasi baru ke database.
     */
    public function store(StoreVerificationRequest $request, VerificationChecklistService $checklistService): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();

        if ($validated['status'] === VerificationStatus::APPROVED->value) {
            $validated['verified_at'] = now();
        }

        $verification = Verification::create($validated);

        // Otomatis inisialisasi checklist dokumen fisik sesuai jenis permohonan
        $checklistService->initializeForVerification($verification);

        return redirect()
            ->route('admin.verifications.show', $verification)
            ->with('status', 'Berkas permohonan SPRI berhasil didaftarkan ke sistem.');
    }

    /**
     * Menampilkan detail lengkap berkas verifikasi beserta audit log.
     */
    public function show(Verification $verification, VerificationChecklistService $checklistService): View
    {
        Gate::authorize('view', $verification);

        // Inisialisasi otomatis jika berkas belum memiliki checklist
        if ($verification->checklists()->doesntExist()) {
            $checklistService->initializeForVerification($verification);
        }

        $verification->load([
            'creator',
            'auditLogs.user',
            'statusHistories.changer',
            'checklists.requirement',
            'checklists.checker',
            'perdimApplication.documents',
        ]);

        $completionStats = $checklistService->getCompletionStats($verification);

        return view('admin.verifications.show', [
            'verification' => $verification,
            'completionStats' => $completionStats,
        ]);
    }

    /**
     * Form edit berkas verifikasi.
     */
    public function edit(Verification $verification): View
    {
        Gate::authorize('update', $verification);

        return view('admin.verifications.edit', [
            'verification' => $verification,
            'statuses' => VerificationStatus::cases(),
            'documentTypes' => self::DOCUMENT_TYPES,
        ]);
    }

    /**
     * Perbarui data verifikasi berkas.
     */
    public function update(UpdateVerificationRequest $request, Verification $verification, VerificationChecklistService $checklistService): RedirectResponse
    {
        $validated = $request->validated();
        $oldDocumentType = $verification->document_type;

        if ($validated['status'] === VerificationStatus::APPROVED->value) {
            if (! $verification->verified_at) {
                $validated['verified_at'] = now();
            }
        } else {
            $validated['verified_at'] = null;
        }

        $verification->update($validated);

        // Jika jenis permohonan SPRI diubah, sinkronkan checklist aktif jenis baru
        if ($oldDocumentType !== $verification->document_type) {
            $checklistService->syncForDocumentType($verification, $verification->document_type);
        }

        return redirect()
            ->route('admin.verifications.show', $verification)
            ->with('status', 'Data verifikasi berkas berhasil diperbarui.');
    }

    /**
     * Hapus berkas permohonan (Soft Delete).
     */
    public function destroy(Verification $verification): RedirectResponse
    {
        Gate::authorize('delete', $verification);

        $verification->delete();

        return redirect()
            ->route('admin.verifications.index')
            ->with('status', 'Berkas verifikasi nomor '.$verification->registration_code.' berhasil dihapus.');
    }

    /**
     * Pratinjau streaming aman berkas dokumen digital PERDIM 14 dari disk privat.
     */
    public function previewDocument(Verification $verification, PerdimDocument $document): Response
    {
        Gate::authorize('view', $verification);

        // Validasi relasi kepemilikan dokumen untuk mencegah IDOR
        $application = $verification->perdimApplication;
        if (! $application || $document->perdim_application_id !== $application->id) {
            abort(404, 'Dokumen tidak berkaitan dengan berkas verifikasi ini.');
        }

        if (! Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'Berkas fisik dokumen tidak ditemukan pada penyimpanan privat.');
        }

        return Storage::disk('private')->response(
            $document->file_path,
            $document->original_name,
            [
                'Content-Type' => $document->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.$document->original_name.'"',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
