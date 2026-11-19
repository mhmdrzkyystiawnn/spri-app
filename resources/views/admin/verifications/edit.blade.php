@extends('layouts.admin')

@section('title', 'Perbarui Berkas: ' . $verification->registration_code)

@section('content')
<div class="space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-stone-200">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-mono font-semibold tracking-wider uppercase bg-stone-100 text-stone-700 rounded border border-stone-200 mb-1">
                <span class="w-1.5 h-1.5 rounded-full bg-[#1B365D]"></span>
                UBAH STATUS & CATATAN BERKAS
            </div>
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 font-serif">
                Perbarui Berkas: <span class="font-mono">{{ $verification->registration_code }}</span>
            </h2>
            <p class="text-xs text-stone-600">
                Pembaruan data permohonan, mutasi status pemeriksaan, serta penerbitan catatan resmi verifikator.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.verifications.show', $verification) }}" class="btn-secondary text-xs">
                Lihat Detail
            </a>
            <a href="{{ route('admin.verifications.index') }}" class="btn-secondary text-xs">
                &larr; Daftar Berkas
            </a>
        </div>
    </div>

    <!-- Layout Lembar Mutasi Berkas Meja Layanan (2-Kolom Desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Kolom Kiri (Ringkasan Metadata Dossier & Panduan Mutasi) -->
        <div class="lg:col-span-4 space-y-4">
            <!-- Ringkasan Dossier Saat Ini -->
            <div class="editorial-card p-5 space-y-3">
                <div class="border-b border-stone-200 pb-2 flex items-center justify-between">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-700">
                        Metadata Dossier
                    </h3>
                    <span class="badge-status status-{{ $verification->status->value }} text-[10px]">
                        {{ $verification->status->label() }}
                    </span>
                </div>
                <dl class="space-y-2 text-xs">
                    <div>
                        <dt class="text-[11px] text-stone-500 font-medium uppercase tracking-wider">Pemohon:</dt>
                        <dd class="text-stone-900 font-bold mt-0.5">{{ $verification->applicant_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] text-stone-500 font-medium uppercase tracking-wider">Jenis Dokumen:</dt>
                        <dd class="text-stone-800 font-medium mt-0.5">{{ $verification->document_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] text-stone-500 font-medium uppercase tracking-wider">Petugas Pendaftar:</dt>
                        <dd class="text-stone-800 mt-0.5">{{ $verification->creator?->name ?? 'Sistem' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] text-stone-500 font-medium uppercase tracking-wider">Waktu Didaftarkan:</dt>
                        <dd class="text-stone-600 font-mono text-[11px] mt-0.5">{{ $verification->created_at->format('d/m/Y H:i') }} WIB</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] text-stone-500 font-medium uppercase tracking-wider">Pembaruan Terakhir:</dt>
                        <dd class="text-stone-600 font-mono text-[11px] mt-0.5">{{ $verification->updated_at->format('d/m/Y H:i') }} WIB</dd>
                    </div>
                </dl>
            </div>

            <!-- Pedoman Mutasi Status Keimigrasian -->
            <div class="editorial-card p-5 space-y-3 bg-stone-50/60">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-700 border-b border-stone-200 pb-2">
                    Ketentuan Perubahan Status
                </h3>
                <div class="space-y-2 text-xs text-stone-600">
                    <p>
                        <strong class="text-stone-900">Perlu Perbaikan / Ditolak:</strong> Wajib menyertakan <em>Catatan Publik</em> agar pemohon mengetahui dokumen fisik yang harus dilengkapi.
                    </p>
                    <p>
                        <strong class="text-stone-900">Disetujui:</strong> Menandakan seluruh berkas fisik telah sah dan pemohon dapat hadir untuk pengambilan foto & biometrik.
                    </p>
                    <p class="text-[11px] text-stone-500 pt-1 border-t border-stone-200 font-mono">
                        Setiap perubahan status otomatis terekam dalam Kronologi & Audit Trail sistem.
                    </p>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan (Formulir Mutasi Berkas) -->
        <div class="lg:col-span-8">
            <div class="editorial-card p-6 sm:p-8">
                <form action="{{ route('admin.verifications.update', $verification) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Section 1: Data Pokok Permohonan -->
                    <div class="space-y-4">
                        <div class="border-b border-stone-200 pb-2 flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700">
                                1. Identitas Berkas & Pemohon
                            </h3>
                            <span class="text-[11px] text-stone-500 font-mono">Tahap 1 / 2</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="registration_code" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                                    Nomor Registrasi SPRI <span class="text-red-700">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="registration_code" 
                                    name="registration_code" 
                                    value="{{ old('registration_code', $verification->registration_code) }}" 
                                    required
                                    maxlength="32"
                                    class="editorial-input w-full font-mono text-sm uppercase @error('registration_code') border-rose-500 @enderror"
                                >
                                @error('registration_code')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="applicant_name" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                                    Nama Lengkap Pemohon <span class="text-red-700">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="applicant_name" 
                                    name="applicant_name" 
                                    value="{{ old('applicant_name', $verification->applicant_name) }}" 
                                    required
                                    maxlength="150"
                                    class="editorial-input w-full text-sm @error('applicant_name') border-rose-500 @enderror"
                                >
                                @error('applicant_name')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="document_type" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                                    Jenis Permohonan SPRI <span class="text-red-700">*</span>
                                </label>
                                <select id="document_type" name="document_type" required class="editorial-input w-full text-sm bg-white @error('document_type') border-rose-500 @enderror">
                                    @foreach ($documentTypes as $type)
                                        <option value="{{ $type }}" {{ old('document_type', $verification->document_type) === $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('document_type')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                                    Status Verifikasi <span class="text-red-700">*</span>
                                </label>
                                <select id="status" name="status" required class="editorial-input w-full text-sm bg-white font-medium @error('status') border-rose-500 @enderror">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" {{ old('status', $verification->status->value) === $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Catatan Keterangan & Catatan Internal -->
                    <div class="space-y-4 pt-2">
                        <div class="border-b border-stone-200 pb-2 flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700">
                                2. Catatan Keterangan & Catatan Internal
                            </h3>
                            <span class="text-[11px] text-stone-500 font-mono">Tahap 2 / 2</span>
                        </div>

                        <div>
                            <label for="public_notes" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                                Catatan Publik untuk Pemohon
                            </label>
                            <textarea 
                                id="public_notes" 
                                name="public_notes" 
                                rows="3" 
                                maxlength="1000"
                                placeholder="Keterangan yang DAPAT DIBACA oleh pemohon pada halaman cek status."
                                class="editorial-input w-full text-xs font-sans @error('public_notes') border-rose-500 @enderror"
                            >{{ old('public_notes', $verification->public_notes) }}</textarea>
                            @error('public_notes')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-[11px] text-stone-500">* Wajib diisi jika status permohonan Perlu Perbaikan atau Ditolak.</p>
                        </div>

                        <div>
                            <label for="admin_notes" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1 flex items-center justify-between">
                                <span>Catatan Rahasia Internal Loket (Petugas Only)</span>
                                <span class="text-red-700 font-mono text-[10px]">TIDAK DITAMPILKAN DI PUBLIK</span>
                            </label>
                            <textarea 
                                id="admin_notes" 
                                name="admin_notes" 
                                rows="3" 
                                maxlength="2000"
                                class="editorial-input w-full text-xs font-mono bg-stone-50 @error('admin_notes') border-rose-500 @enderror"
                            >{{ old('admin_notes', $verification->admin_notes) }}</textarea>
                            @error('admin_notes')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-stone-200 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.verifications.show', $verification) }}" class="btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn-primary">
                            Simpan Pembaruan Berkas
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
