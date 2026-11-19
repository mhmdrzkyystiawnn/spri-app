<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVerificationChecklistRequest;
use App\Models\Verification;
use App\Models\VerificationChecklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class VerificationChecklistController extends Controller
{
    /**
     * Perbarui status checklist kelengkapan dokumen pada berkas verifikasi.
     */
    public function update(UpdateVerificationChecklistRequest $request, Verification $verification): RedirectResponse
    {
        Gate::authorize('updateForVerification', [VerificationChecklist::class, $verification]);

        $items = $request->validated('items', []);

        DB::transaction(function () use ($verification, $items) {
            $currentUser = Auth::user();

            foreach ($items as $checklistId => $data) {
                /** @var VerificationChecklist|null $checklist */
                $checklist = $verification->checklists()->find($checklistId);

                if (! $checklist) {
                    throw ValidationException::withMessages([
                        'items' => "Item persyaratan dengan ID #{$checklistId} tidak terdaftar pada berkas verifikasi ini.",
                    ]);
                }

                $isChecked = filter_var($data['is_checked'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $notes = isset($data['notes']) ? trim((string) $data['notes']) : null;

                if ($isChecked) {
                    $checklist->is_checked = true;
                    $checklist->checked_by = $currentUser->id;
                    $checklist->checked_at = now();
                    $checklist->notes = $notes;
                } else {
                    $checklist->is_checked = false;
                    $checklist->checked_by = null;
                    $checklist->checked_at = null;
                    $checklist->notes = $notes;
                }

                $checklist->save();
            }
        });

        return redirect()
            ->route('admin.verifications.show', $verification)
            ->with('status', 'Checklist kelengkapan dokumen fisik berhasil diperbarui.');
    }
}
