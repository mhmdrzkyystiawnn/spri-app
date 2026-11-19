@extends('layouts.admin')

@section('title', 'Daftarkan Berkas Baru - Meja Layanan SPRI')

@section('content')
<div class="space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between pb-3 border-b border-stone-200">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-mono font-semibold tracking-wider uppercase bg-stone-100 text-stone-700 rounded border border-stone-200 mb-1">
                <span class="w-1.5 h-1.5 rounded-full bg-[#1B365D]"></span>
                Penerimaan Berkas Baru
            </div>
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 font-serif">
                Daftarkan Berkas Permohonan SPRI
            </h2>
            <p class="text-xs text-stone-600">
                Pencatatan tanda terima berkas fisik pemohon dari loket permohonan ke dalam antrean verifikasi resmi.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.verifications.index') }}" class="btn-secondary text-xs">
                &larr; Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Layout Dossier Entry Sheet 2-Kolom (Desktop) & Stack (Mobile) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Kolom Kiri (Panduan Jenis Layanan SPRI & Syarat Formil) -->
        <div class="lg:col-span-4 space-y-4">
            <!-- Panduan Jenis Dokumen SPRI -->
            <div class="editorial-card p-5 space-y-3">
                <div class="border-b border-stone-200 pb-2">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#1B365D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pedoman Jenis Dokumen SPRI
                    </h3>
                </div>
                <div class="space-y-2.5 text-xs text-stone-600">
                    <div class="p-2.5 rounded bg-stone-50 border border-stone-200 space-y-0.5">
                        <strong class="text-stone-900 block font-medium">Paspor Biasa (48 Halaman)</strong>
                        <p class="text-[11px]">Dokumen perjalanan standar internasional untuk WNI dewasa dan anak.</p>
                    </div>
                    <div class="p-2.5 rounded bg-stone-50 border border-stone-200 space-y-0.5">
                        <strong class="text-stone-900 block font-medium">Paspor Elektronik (E-Passport)</strong>
                        <p class="text-[11px]">Memuat chip biometrik terenkripsi dengan standar keimigrasian ICAO 9303.</p>
                    </div>
                    <div class="p-2.5 rounded bg-stone-50 border border-stone-200 space-y-0.5">
                        <strong class="text-stone-900 block font-medium">SPLP (Surat Perjalanan Laksana Paspor)</strong>
                        <p class="text-[11px]">Dokumen darurat satu kali perjalanan kepulangan bagi WNI di luar negeri.</p>
                    </div>
                </div>
            </div>

            <!-- Ketentuan Formil Penerimaan Berkas -->
            <div class="editorial-card p-5 space-y-3 bg-stone-50/60">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-700 border-b border-stone-200 pb-2">
                    Ketentuan Formil Loket
                </h3>
                <ul class="text-xs text-stone-600 space-y-2 list-disc list-inside">
                    <li>Pastikan ejaan nama pemohon identik dengan KTP-el dan Akta Lahir/Ijazah.</li>
                    <li>Nomor registrasi dapat diisi otomatis atau disesuaikan dengan nomor resi loket fisik.</li>
                    <li>Catatan internal loket dilindungi enkripsi dan tidak dapat diakses oleh publik.</li>
                </ul>
            </div>
        </div>

        <!-- Kolom Kanan (Formulir Entri Berkas) -->
        <div class="lg:col-span-8">
            <div class="editorial-card p-6 sm:p-8">
                <form action="{{ route('admin.verifications.store') }}" method="POST" class="space-y-6">
                    @csrf

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
                                    value="{{ old('registration_code', 'SPRI-' . date('Y') . '-' . strtoupper(\Illuminate\Support\Str::random(6))) }}" 
                                    required
                                    maxlength="32"
                                    class="editorial-input w-full font-mono text-sm uppercase @error('registration_code') border-rose-500 @enderror"
                                >
                                @error('registration_code')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-[11px] text-stone-500 font-mono">Format: SPRI-YYYY-XXXXXX</p>
                            </div>

                            <div>
                                <label for="applicant_name" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                                    Nama Lengkap Pemohon <span class="text-red-700">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="applicant_name" 
                                    name="applicant_name" 
                                    value="{{ old('applicant_name') }}" 
                                    required
                                    maxlength="150"
                                    placeholder="Sesuai KTP / Akta Kelahiran"
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
                                    <option value="">-- Pilih Jenis Layanan SPRI --</option>
                                    @foreach ($documentTypes as $type)
                                        <option value="{{ $type }}" {{ old('document_type') === $type ? 'selected' : '' }}>
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
                                    Status Awal Verifikasi <span class="text-red-700">*</span>
                                </label>
                                <select id="status" name="status" required class="editorial-input w-full text-sm bg-white @error('status') border-rose-500 @enderror">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" {{ old('status', 'pending') === $status->value ? 'selected' : '' }}>
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
                                placeholder="Keterangan yang DAPAT DIBACA oleh pemohon pada halaman cek status (contoh: berkas foto yang harus dilengkapi, arahan loket, dsb)."
                                class="editorial-input w-full text-xs font-sans @error('public_notes') border-rose-500 @enderror"
                            >{{ old('public_notes') }}</textarea>
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
                                placeholder="Catatan verifikator, temuan berkas, kecocokan NIK, riwayat wawancara singkat, dsb. Hanya dapat dibaca oleh sesama petugas berwenang."
                                class="editorial-input w-full text-xs font-mono bg-stone-50 @error('admin_notes') border-rose-500 @enderror"
                            >{{ old('admin_notes') }}</textarea>
                            @error('admin_notes')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-stone-200 flex items-center justify-end gap-3">
                        <a href="{{ route('admin.verifications.index') }}" class="btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn-primary">
                            Simpan Berkas Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
