<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicPerdimApplicationRequest;
use App\Http\Requests\UploadTempPerdimDocumentRequest;
use App\Models\Verification;
use App\Services\PerdimDocumentStorageService;
use App\Services\PublicPerdimApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPerdimApplicationController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran permohonan mandiri PERDIM 14.
     */
    public function create(): View
    {
        return view('public.perdim.create');
    }

    /**
     * Simpan formulir permohonan PERDIM 14 ke sistem secara transaksional.
     */
    public function store(
        StorePublicPerdimApplicationRequest $request,
        PublicPerdimApplicationService $service
    ): JsonResponse|RedirectResponse {
        $verification = $service->submit($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Permohonan Formulir PERDIM 14 berhasil dikirimkan ke sistem antrean verifikasi.',
                'data' => [
                    'registration_code' => $verification->registration_code,
                    'applicant_name' => $verification->applicant_name,
                    'document_type' => $verification->document_type,
                    'receipt_url' => route('public.perdim.receipt', ['code' => $verification->registration_code]),
                ],
            ]);
        }

        return redirect()
            ->route('public.perdim.receipt')
            ->with([
                'success_message' => 'Permohonan Formulir PERDIM 14 berhasil dikirimkan ke sistem antrean verifikasi.',
                'submitted_code' => $verification->registration_code,
                'submitted_name' => $verification->applicant_name,
                'submitted_document_type' => $verification->document_type,
                'submitted_created_at' => $verification->created_at?->translatedFormat('d F Y, H:i').' WIB',
            ]);
    }

    /**
     * Tampilkan halaman tanda terima pendaftaran formulir PERDIM 14.
     */
    public function receipt(Request $request): View
    {
        $code = session('submitted_code') ?? $request->query('code');
        $verification = null;

        if ($code) {
            $verification = Verification::query()
                ->with(['perdimApplication', 'checklists.requirement'])
                ->where('registration_code', strtoupper(trim((string) $code)))
                ->first();
        }

        return view('public.perdim.receipt', [
            'verification' => $verification,
            'code' => $code,
        ]);
    }

    /**
     * Unggah berkas sementara untuk formulir permohonan PERDIM 14 (staged upload).
     */
    public function uploadTemp(
        UploadTempPerdimDocumentRequest $request,
        PerdimDocumentStorageService $storageService
    ): JsonResponse {
        $result = $storageService->storeTemp(
            $request->file('file'),
            $request->input('document_type'),
            $request->session()->getId()
        );

        return response()->json([
            'success' => true,
            'message' => 'Berkas berhasil diunggah sementara.',
            'data' => $result,
        ], 200);
    }

    /**
     * Hapus berkas sementara berdasarkan token.
     */
    public function deleteTemp(
        Request $request,
        PerdimDocumentStorageService $storageService
    ): JsonResponse {
        $token = (string) $request->input('token');
        $removed = $storageService->removeTemp($token, $request->session()->getId());

        return response()->json([
            'success' => $removed,
            'message' => $removed ? 'Berkas sementara berhasil dihapus.' : 'Berkas sementara tidak ditemukan.',
        ], $removed ? 200 : 404);
    }
}
