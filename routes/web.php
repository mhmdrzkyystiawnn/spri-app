<?php

use App\Http\Controllers\Admin\DocumentRequirementController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VerificationChecklistController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicPerdimApplicationController;
use App\Http\Controllers\PublicStatusController;
use Illuminate\Support\Facades\Route;

// Beranda dialihkan ke portal cek status publik
Route::get('/', function () {
    return redirect()->route('status.show');
});

// Portal Publik Pendaftaran Permohonan Paspor (Formulir PERDIM 14)
Route::get('/permohonan-perdim', [PublicPerdimApplicationController::class, 'create'])->name('public.perdim.create');
Route::post('/permohonan-perdim', [PublicPerdimApplicationController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('public.perdim.store');
Route::post('/permohonan-perdim/upload-temp', [PublicPerdimApplicationController::class, 'uploadTemp'])
    ->middleware('throttle:30,1')
    ->name('public.perdim.upload-temp');
Route::delete('/permohonan-perdim/upload-temp', [PublicPerdimApplicationController::class, 'deleteTemp'])
    ->middleware('throttle:30,1')
    ->name('public.perdim.delete-temp');
Route::get('/permohonan-perdim/tanda-terima', [PublicPerdimApplicationController::class, 'receipt'])->name('public.perdim.receipt');

// Portal Publik Cek Status Verifikasi SPRI
Route::get('/cek-status', [PublicStatusController::class, 'show'])->name('status.show');
Route::post('/cek-status', [PublicStatusController::class, 'check'])
    ->middleware('throttle:status-check')
    ->name('status.check');

// Autentikasi Meja Layanan Admin
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// Dashboard & Manajemen Verifikasi Dokumen SPRI
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'can:access-admin'])
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.verifications.index');
        });

        Route::resource('verifications', VerificationController::class);
        Route::patch('verifications/{verification}/checklist', [VerificationChecklistController::class, 'update'])
            ->name('verifications.checklist.update');
        Route::get('verifications/{verification}/documents/{document}/preview', [VerificationController::class, 'previewDocument'])
            ->name('verifications.documents.preview');

        Route::resource('users', UserController::class)->middleware('can:manage-users');
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active')
            ->middleware('can:manage-users');

        Route::resource('document-requirements', DocumentRequirementController::class)->middleware('can:manage-document-requirements');
        Route::patch('document-requirements/{document_requirement}/toggle-active', [DocumentRequirementController::class, 'toggleActive'])
            ->name('document-requirements.toggle-active')
            ->middleware('can:manage-document-requirements');
    });
