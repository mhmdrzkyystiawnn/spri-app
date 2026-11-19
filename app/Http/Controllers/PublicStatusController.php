<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicStatusController extends Controller
{
    /**
     * Tampilkan form publik pencarian status verifikasi SPRI.
     */
    public function show(Request $request): View
    {
        return view('public.status', [
            'result' => null,
            'searchedCode' => null,
            'notFound' => false,
        ]);
    }

    /**
     * Proses pencarian status permohonan SPRI secara publik.
     */
    public function check(Request $request): View
    {
        $validated = $request->validate([
            'registration_code' => ['required', 'string', 'alpha_dash', 'max:32'],
        ], [
            'registration_code.required' => 'Nomor registrasi wajib diisi.',
            'registration_code.alpha_dash' => 'Format nomor registrasi hanya boleh berupa kombinasi huruf, angka, tanda strip, atau garis bawah.',
            'registration_code.max' => 'Nomor registrasi maksimal 32 karakter.',
        ]);

        $code = strtoupper(trim($validated['registration_code']));

        // Query hanya data aktif (non-deleted)
        $verification = Verification::query()
            ->where('registration_code', $code)
            ->first();

        if (! $verification) {
            return view('public.status', [
                'result' => null,
                'searchedCode' => $code,
                'notFound' => true,
            ]);
        }

        // Susun payload publik minimal & aman (tanpa admin_notes, id internal, atau user_id)
        $result = [
            'registration_code' => $verification->registration_code,
            'applicant_name' => $verification->masked_applicant_name,
            'document_type' => $verification->document_type,
            'status' => $verification->status->value,
            'status_label' => $verification->status->label(),
            'status_description' => $verification->status->description(),
            'badge_classes' => $verification->status->badgeClasses(),
            'public_notes' => $verification->public_notes,
            'verified_at' => $verification->verified_at?->translatedFormat('d F Y, H:i') . ' WIB',
            'updated_at' => $verification->updated_at?->translatedFormat('d F Y, H:i') . ' WIB',
        ];

        return view('public.status', [
            'result' => $result,
            'searchedCode' => $code,
            'notFound' => false,
        ]);
    }
}
