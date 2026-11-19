<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequirementRequest;
use App\Http\Requests\UpdateDocumentRequirementRequest;
use App\Models\DocumentRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DocumentRequirementController extends Controller
{
    /**
     * Tampilkan daftar master persyaratan dokumen.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', DocumentRequirement::class);

        $typeFilter = $request->query('document_type');
        $statusFilter = $request->query('status');
        $searchTerm = $request->query('search');

        $query = DocumentRequirement::query()
            ->withCount('checklists');

        if (! empty($typeFilter)) {
            $query->where('document_type', $typeFilter);
        }

        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if (! empty($searchTerm)) {
            $term = trim($searchTerm);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        $requirements = $query
            ->orderBy('document_type')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.document_requirements.index', [
            'requirements' => $requirements,
            'documentTypes' => VerificationController::DOCUMENT_TYPES,
            'currentType' => $typeFilter,
            'currentStatus' => $statusFilter,
            'searchTerm' => $searchTerm,
        ]);
    }

    /**
     * Form penambahan master persyaratan baru.
     */
    public function create(): View
    {
        Gate::authorize('create', DocumentRequirement::class);

        return view('admin.document_requirements.create', [
            'documentTypes' => VerificationController::DOCUMENT_TYPES,
            'requirement' => new DocumentRequirement([
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    /**
     * Simpan master persyaratan baru.
     */
    public function store(StoreDocumentRequirementRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_mandatory'] = $request->boolean('is_mandatory', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        DocumentRequirement::create($validated);

        return redirect()
            ->route('admin.document-requirements.index')
            ->with('status', 'Master persyaratan dokumen berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail master persyaratan.
     */
    public function show(DocumentRequirement $documentRequirement): View
    {
        Gate::authorize('view', $documentRequirement);

        $documentRequirement->loadCount('checklists');

        return view('admin.document_requirements.show', [
            'requirement' => $documentRequirement,
        ]);
    }

    /**
     * Form edit master persyaratan.
     */
    public function edit(DocumentRequirement $documentRequirement): View
    {
        Gate::authorize('update', $documentRequirement);

        return view('admin.document_requirements.edit', [
            'requirement' => $documentRequirement,
            'documentTypes' => VerificationController::DOCUMENT_TYPES,
        ]);
    }

    /**
     * Perbarui data master persyaratan.
     */
    public function update(UpdateDocumentRequirementRequest $request, DocumentRequirement $documentRequirement): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_mandatory'] = $request->boolean('is_mandatory', false);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $documentRequirement->update($validated);

        return redirect()
            ->route('admin.document-requirements.index')
            ->with('status', 'Data persyaratan dokumen berhasil diperbarui.');
    }

    /**
     * Hapus / Nonaktifkan master persyaratan.
     * Bila persyaratan pernah digunakan pada checklist verifikasi, lakukan soft deactivate agar integritas data lama terjaga.
     */
    public function destroy(DocumentRequirement $documentRequirement): RedirectResponse
    {
        Gate::authorize('delete', $documentRequirement);

        if ($documentRequirement->checklists()->exists()) {
            $documentRequirement->update(['is_active' => false]);

            return redirect()
                ->route('admin.document-requirements.index')
                ->with('status', "Persyaratan '{$documentRequirement->name}' dinonaktifkan karena telah tertaut pada berkas verifikasi terdahulu.");
        }

        $documentRequirement->delete();

        return redirect()
            ->route('admin.document-requirements.index')
            ->with('status', "Master persyaratan '{$documentRequirement->name}' berhasil dihapus dari sistem.");
    }

    /**
     * Toggle status aktif / nonaktif master persyaratan.
     */
    public function toggleActive(DocumentRequirement $documentRequirement): RedirectResponse
    {
        Gate::authorize('update', $documentRequirement);

        $documentRequirement->update(['is_active' => ! $documentRequirement->is_active]);

        $statusText = $documentRequirement->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.document-requirements.index')
            ->with('status', "Persyaratan '{$documentRequirement->name}' berhasil {$statusText}.");
    }
}
