@extends('layouts.app')

@section('title', 'Cek Status Verifikasi SPRI - Layanan Publik Imigrasi')

@section('content')
<div class="max-w-6xl w-full mx-auto py-2 space-y-6">

    <!-- Grid Asimetris 55/45 (Desktop) & Stack (Mobile) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Kolom Kiri (55%): Identitas Resmi, Panduan Verifikasi, Jam & Ketentuan SLA -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Identitas Layanan & Konteks Publik -->
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 text-xs font-mono font-semibold tracking-wide bg-[#12243E] text-amber-300 rounded border border-amber-400/30">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    PORTAL PELAYANAN PUBLIK RESMI
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-stone-900 font-serif leading-tight">
                    Lacak Status Permohonan SPRI
                </h2>
                <p class="text-sm text-stone-600 leading-relaxed">
                    Sistem informasi pemantauan real-time keabsahan berkas fisik Surat Perjalanan Republik Indonesia (Paspor Biasa, Paspor Elektronik, dan SPLP) pada seksi pelayanan verifikasi dokumen.
                </p>
            </div>

            <!-- Kartu Layanan Mandiri Formulir PERDIM 14 -->
            <div class="p-4 rounded-lg bg-emerald-50/70 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="space-y-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                        Pendaftaran Mandiri Online
                    </span>
                    <h3 class="text-sm font-bold text-slate-900">Belum Memiliki Nomor Registrasi?</h3>
                    <p class="text-xs text-slate-600">Isi Formulir PERDIM 14 secara mandiri sebelum hadir ke kantor imigrasi.</p>
                </div>
                <a href="{{ route('public.perdim.create') }}" class="shrink-0 px-3.5 py-2 rounded-md bg-[#1B365D] hover:bg-[#12243E] text-white text-xs font-semibold shadow-xs transition-colors flex items-center justify-center gap-1.5">
                    <span>Isi Formulir PERDIM 14</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            <!-- Panduan Alur Pemeriksaan Berkas Fisik -->
            <div class="editorial-card p-5 space-y-4">
                <div class="border-b border-stone-200 pb-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#1B365D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        Alur Tahapan Pemeriksaan Berkas Fisik
                    </h3>
                    <p class="text-[11px] text-stone-500 mt-0.5">
                        Setiap berkas permohonan melalui tahapan verifikasi keabsahan dokumen berikut:
                    </p>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-start gap-3 p-2.5 rounded bg-stone-50 border border-stone-200">
                        <span class="badge-status status-pending shrink-0">Menunggu</span>
                        <div>
                            <strong class="text-stone-900 block font-medium">Antrean Verifikasi Loket</strong>
                            <span class="text-stone-600 text-[11px]">Berkas telah diterima petugas loket dan masuk dalam antrean pemeriksaan fisik dokumen.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-2.5 rounded bg-stone-50 border border-stone-200">
                        <span class="badge-status status-in_review shrink-0">Diverifikasi</span>
                        <div>
                            <strong class="text-stone-900 block font-medium">Pemeriksaan Dokumen Persyaratan</strong>
                            <span class="text-stone-600 text-[11px]">Petugas verifikator memeriksa kelengkapan fisik KTP, KK, Akta/Ijazah, dan dokumen lampiran.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-2.5 rounded bg-stone-50 border border-stone-200">
                        <span class="badge-status status-needs_revision shrink-0">Perlu Perbaikan</span>
                        <div>
                            <strong class="text-stone-900 block font-medium">Dokumen Kurang / Memerlukan Konfirmasi</strong>
                            <span class="text-stone-600 text-[11px]">Pemohon diminta melengkapi kekurangan berkas fisik sesuai catatan resmi verifikator.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-2.5 rounded bg-stone-50 border border-stone-200">
                        <span class="badge-status status-approved shrink-0">Berkas Sah</span>
                        <div>
                            <strong class="text-stone-900 block font-medium">Verifikasi Berkas Lengkap & Sah</strong>
                            <span class="text-stone-600 text-[11px]">Seluruh persyaratan fisik terpenuhi. Pemohon dapat melanjutkan ke tahapan foto dan biometrik.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-2.5 rounded bg-stone-50 border border-stone-200">
                        <span class="badge-status status-rejected shrink-0">Ditolak</span>
                        <div>
                            <strong class="text-stone-900 block font-medium">Permohonan Ditolak / Dibatalkan</strong>
                            <span class="text-stone-600 text-[11px]">Dokumen tidak memenuhi kriteria peraturan keimigrasian yang berlaku.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jam Operasional & SLA Layanan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="editorial-card p-3.5 space-y-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-stone-500 block">Jam Operasional Layanan</span>
                    <p class="font-medium text-stone-800">Senin &ndash; Jumat: 08:00 &ndash; 15:00 WIB</p>
                    <p class="text-[11px] text-stone-500">Istirahat loket: 12:00 &ndash; 13:00 WIB</p>
                </div>
                <div class="editorial-card p-3.5 space-y-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-stone-500 block">Ketentuan SLA Verifikasi</span>
                    <p class="font-medium text-stone-800">1 &ndash; 3 Hari Kerja</p>
                    <p class="text-[11px] text-stone-500">Sejak berkas fisik diserahkan di loket</p>
                </div>
            </div>

            <!-- Jaminan Privasi Data Pemohon -->
            <div class="p-3.5 rounded bg-stone-100/80 border border-stone-200/80 text-[11px] text-stone-600 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-[#1B365D] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <p>
                    <strong>Jaminan Perlindungan Data:</strong> Nama pemohon disamarkan secara otomatis untuk melindungi privasi. Catatan internal loket hanya dapat diakses melalui otorisasi verifikator internal.
                </p>
            </div>
        </div>

        <!-- Kolom Kanan (45%): Slip Pencarian & Dossier Hasil Status -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Slip Penelusuran Dokumen -->
            <div class="editorial-card p-6 shadow-sm border-stone-300">
                <div class="border-b border-stone-200 pb-3 mb-4">
                    <span class="text-[11px] font-mono font-semibold uppercase tracking-wider text-[#1B365D] block">
                        SLIP PENELUSURAN DOKUMEN
                    </span>
                    <h3 class="text-base font-bold text-stone-900 font-serif">
                        Masukkan Nomor Registrasi
                    </h3>
                </div>

                <form action="{{ route('status.check') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="registration_code" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1.5">
                            Nomor Registrasi Permohonan <span class="text-required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="registration_code" 
                            name="registration_code" 
                            value="{{ old('registration_code', $searchedCode ?? '') }}" 
                            placeholder="Contoh: SPRI-2026-X7K9P2"
                            required
                            maxlength="32"
                            autocomplete="off"
                            class="editorial-input w-full font-mono text-base tracking-wider uppercase placeholder:normal-case placeholder:font-sans placeholder:text-stone-400 @error('registration_code') has-error border-rose-500 @enderror"
                            autofocus
                        >
                        @error('registration_code')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 text-[11px] text-stone-500 flex items-center justify-between">
                            <span>* Format kode tidak sensitif huruf</span>
                            <span class="font-mono">Maks 10x/menit</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full py-2.5 text-sm tracking-wide shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Periksa Status Berkas
                    </button>
                </form>
            </div>

            <!-- Hasil Pencarian: Ditemukan -->
            @if ($result)
                <div class="editorial-card overflow-hidden border-stone-300 shadow-sm animate-fade-in">
                    <div class="editorial-header px-5 py-3.5 flex items-center justify-between gap-2 border-b border-stone-200 bg-[#F8FAFC]">
                        <div>
                            <span class="text-[10px] uppercase tracking-wider text-stone-500 font-semibold block">Nomor Berkas</span>
                            <span class="font-mono text-base font-bold text-stone-900">{{ $result['registration_code'] }}</span>
                        </div>
                        <span class="badge-status status-{{ $result['status'] }} text-xs font-semibold">
                            {{ $result['status_label'] }}
                        </span>
                    </div>

                    <div class="p-5 space-y-4">
                        <!-- Rincian Pemohon -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pb-4 border-b border-stone-200 text-xs">
                            <div>
                                <span class="text-[11px] text-stone-500 block mb-0.5">Nama Pemohon (Disamarkan)</span>
                                <span class="font-bold text-stone-900 tracking-wide font-mono text-sm">{{ $result['applicant_name'] }}</span>
                            </div>
                            <div>
                                <span class="text-[11px] text-stone-500 block mb-0.5">Jenis Dokumen SPRI</span>
                                <span class="font-medium text-stone-900">{{ $result['document_type'] }}</span>
                            </div>
                        </div>

                        <!-- Penjelasan Status -->
                        <div class="rounded-md bg-stone-50 p-3.5 border border-stone-200">
                            <h4 class="text-[11px] font-semibold text-stone-700 uppercase tracking-wider mb-1">
                                Keterangan Tahap Layanan
                            </h4>
                            <p class="text-xs text-stone-700 leading-relaxed">
                                {{ $result['status_description'] }}
                            </p>
                        </div>

                        <!-- Catatan Publik Petugas (Jika ada) -->
                        @if (!empty($result['public_notes']))
                            <div class="rounded-md bg-amber-50/80 p-3.5 border border-amber-200">
                                <h4 class="text-[11px] font-semibold text-amber-900 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                    Catatan Resmi dari Petugas Verifikator:
                                </h4>
                                <p class="text-xs text-amber-950 leading-relaxed font-serif italic">
                                    &ldquo;{{ $result['public_notes'] }}&rdquo;
                                </p>
                            </div>
                        @endif

                        <!-- Metadata Tanggal -->
                        <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between text-[11px] text-stone-500 gap-1 border-t border-stone-100 font-mono">
                            <span>Pembaruan Terakhir: <strong>{{ $result['updated_at'] }}</strong></span>
                            @if (!empty($result['verified_at']))
                                <span>Waktu Verifikasi: <strong>{{ $result['verified_at'] }}</strong></span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-stone-50 px-5 py-2.5 border-t border-stone-200 text-[11px] text-stone-500">
                        Data pribadi dilindungi oleh sistem keamanan verifikasi. Catatan internal loket tidak dapat diakses secara publik.
                    </div>
                </div>
            @elseif ($notFound)
                <!-- Hasil Pencarian: Tidak Ditemukan -->
                <div class="editorial-card p-5 border-rose-300 bg-rose-50/40 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded bg-rose-100 border border-rose-300 flex items-center justify-center text-rose-700 font-bold text-sm shrink-0">
                            !
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-bold text-rose-900">
                                Berkas Registrasi Tidak Ditemukan
                            </h3>
                            <p class="text-xs text-stone-700 leading-relaxed">
                                Nomor registrasi <strong class="font-mono text-stone-900">{{ $searchedCode }}</strong> tidak tercatat dalam basis data verifikasi aktif.
                            </p>
                        </div>
                    </div>
                    <ul class="text-[11px] text-stone-600 list-disc list-inside space-y-1 pt-2 border-t border-rose-200/60">
                        <li>Pastikan nomor registrasi dimasukkan lengkap termasuk tanda strip (misal: SPRI-2026-X7K9P2).</li>
                        <li>Jika baru saja mendaftar di loket, berkas membutuhkan beberapa menit untuk sinkronisasi antrean.</li>
                        <li>Hubungi loket informasi kantor imigrasi apabila nomor tanda terima tetap tidak terdeteksi.</li>
                    </ul>
                </div>
            @else
                <!-- Initial State: Panduan Bukti Fisik Pendaftaran -->
                <div class="editorial-card p-5 text-center space-y-2 border-dashed border-stone-300 bg-stone-50/50">
                    <div class="w-10 h-10 rounded-full bg-stone-200/80 mx-auto flex items-center justify-center text-stone-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xs font-bold text-stone-800 uppercase tracking-wider">
                        Siapkan Lembar Bukti Pendaftaran
                    </h4>
                    <p class="text-xs text-stone-500 max-w-xs mx-auto leading-relaxed">
                        Periksa nomor registrasi yang tertera pada lembar bukti pendaftaran loket untuk memantau proses verifikasi berkas paspor Anda.
                    </p>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
