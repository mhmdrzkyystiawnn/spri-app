@extends('layouts.app')

@section('title', 'Lembar Tanda Terima Permohonan Paspor (PERDIM 14) - Ditjen Imigrasi')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    @if (session('success_message'))
        <div class="no-print p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ session('success_message') }}</span>
        </div>
    @endif

    <!-- Kartu Dokumen Tanda Terima Resmi (Area Cetak Utama) -->
    <div class="print-card bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        
        <!-- Header Dokumen Cetak / Banner Institusi -->
        <div class="bg-[#1B365D] text-white p-6 sm:p-8 text-center relative border-b-2 border-amber-400">
            <div class="flex items-center justify-center gap-2.5 mb-2">
                <div class="w-7 h-7 rounded bg-[#12243E] border border-amber-400/50 flex items-center justify-center font-bold text-amber-300 text-xs">
                    RI
                </div>
                <span class="text-xs uppercase tracking-widest text-slate-200 font-semibold">
                    Kementerian Hukum dan Hak Asasi Manusia RI &bull; Ditjen Imigrasi
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white font-serif">
                Tanda Terima Pendaftaran Mandiri PERDIM 14
            </h1>
            <p class="text-xs text-slate-300 mt-1">
                Bukti Pengajuan Berkas Tanda Terima Permohonan SPRI (Surat Perjalanan Republik Indonesia)
            </p>

            <!-- Monospace Registration Code Box -->
            <div class="mt-5 bg-[#12243E] border border-amber-400/50 rounded-lg p-4 max-w-md mx-auto shadow-inner">
                <p class="text-[11px] uppercase tracking-wider text-amber-300 font-semibold mb-1">
                    Nomor Registrasi Resmi
                </p>
                <div class="text-2xl sm:text-3xl font-mono font-bold tracking-widest text-white select-all">
                    {{ $verification->registration_code ?? session('submitted_code') ?? $code ?? 'SPRI-BELUM-TERBIT' }}
                </div>
                <p class="text-[10px] text-slate-400 mt-1">
                    Gunakan kode ini untuk pengecekan berkas dan pemantauan status
                </p>
            </div>
        </div>

        <!-- Badan Tanda Terima & Data Pemohon -->
        <div class="p-6 sm:p-8 space-y-6">
            
            <!-- Ringkasan Data PII dengan Masking Keamanan Privasi -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 pb-2 border-b border-slate-200 flex items-center justify-between">
                    <span>Ringkasan Berkas Pendaftaran</span>
                    <span class="text-[11px] font-normal text-slate-500 font-mono">Keamanan Data Terlindungi (Masked PII)</span>
                </h3>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 pt-3 text-xs">
                    <div class="p-3 rounded bg-slate-50 border border-slate-100">
                        <dt class="text-slate-500 font-medium">Nama Lengkap Pemohon</dt>
                        <dd class="text-slate-900 font-bold text-sm mt-0.5 font-mono">
                            {{ $verification?->masked_applicant_name ?? session('submitted_name') ?? '-' }}
                        </dd>
                    </div>

                    <div class="p-3 rounded bg-slate-50 border border-slate-100">
                        <dt class="text-slate-500 font-medium">Nomor Identitas (NIK / Paspor Lama)</dt>
                        <dd class="text-slate-900 font-bold text-sm mt-0.5 font-mono">
                            {{ $verification?->perdimApplication?->masked_nik ?? $verification?->perdimApplication?->masked_identity_number ?? '3171************' }}
                        </dd>
                    </div>

                    <div class="p-3 rounded bg-slate-50 border border-slate-100">
                        <dt class="text-slate-500 font-medium">Jenis Permohonan Dokumen</dt>
                        <dd class="text-slate-900 font-semibold mt-0.5">
                            {{ $verification->document_type ?? session('submitted_document_type') ?? '-' }}
                        </dd>
                    </div>

                    <div class="p-3 rounded bg-slate-50 border border-slate-100">
                        <dt class="text-slate-500 font-medium">Status Antrean Verifikasi</dt>
                        <dd class="mt-0.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                {{ $verification?->status?->label() ?? 'Menunggu Verifikasi Fisik' }}
                            </span>
                        </dd>
                    </div>

                    <div class="p-3 rounded bg-slate-50 border border-slate-100">
                        <dt class="text-slate-500 font-medium">Waktu Pendaftaran Online</dt>
                        <dd class="text-slate-700 font-medium mt-0.5 font-mono">
                            {{ $verification?->created_at?->translatedFormat('d F Y, H:i') . ' WIB' ?? session('submitted_created_at') ?? date('d F Y, H:i') . ' WIB' }}
                        </dd>
                    </div>

                    <div class="p-3 rounded bg-slate-50 border border-slate-100">
                        <dt class="text-slate-500 font-medium">Metode Pendaftaran</dt>
                        <dd class="text-slate-700 font-medium mt-0.5">
                            Portal Publik Mandiri PERDIM 14 Online
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Petunjuk Langkah Loket Imigrasi -->
            <div class="bg-amber-50/70 rounded-lg border border-amber-200 p-4 text-xs text-amber-950 space-y-2">
                <h4 class="font-bold uppercase tracking-wider text-amber-900 flex items-center gap-1.5 text-xs">
                    <svg class="w-4 h-4 text-amber-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    Instruksi Verifikasi Fisik di Kantor Imigrasi
                </h4>
                <ol class="list-decimal list-inside space-y-1.5 text-slate-800 pl-1 leading-relaxed">
                    <li>Simpan atau cetak lembar tanda terima ini sebagai dokumen rujukan antrean.</li>
                    <li>Bawa <strong>seluruh dokumen persyaratan asli</strong> beserta fotokopi ukuran kertas A4 (tidak dipotong) saat hadir ke kantor imigrasi.</li>
                    <li>Tunjukkan Nomor Registrasi ini kepada Petugas Pengarah Layanan / Loket Verifikasi Dokumen SPRI.</li>
                    <li>Pemeriksaan fisik berkas dan pencocokan data biometrik akan dilaksanakan di meja verifikator imigrasi.</li>
                </ol>
            </div>

            <!-- Checklist Persyaratan Berkas Fisik Asli -->
            @if ($verification && $verification->checklists->isNotEmpty())
                <div class="border border-slate-200 rounded-lg p-5 bg-slate-50/60 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">
                            Daftar Berkas Fisik yang Wajib Dibawa ke Loket:
                        </h4>
                        <span class="text-[11px] text-slate-500 font-mono">
                            {{ $verification->checklists->count() }} Dokumen Terdaftar
                        </span>
                    </div>

                    <ul class="text-xs space-y-2">
                        @foreach ($verification->checklists as $item)
                            <li class="flex items-start gap-2.5 p-2 rounded bg-white border border-slate-200">
                                <span class="w-4 h-4 rounded border border-slate-300 flex items-center justify-center text-[10px] text-slate-400 mt-0.5 shrink-0 bg-slate-50">
                                    &bull;
                                </span>
                                <div class="flex-1">
                                    <span class="font-medium text-slate-800">{{ $item->requirement->name ?? 'Dokumen Persyaratan' }}</span>
                                    @if ($item->requirement?->description)
                                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $item->requirement->description }}</p>
                                    @endif
                                </div>
                                @if ($item->requirement?->is_mandatory)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 shrink-0">
                                        Wajib Asli
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Catatan Kaki Dokumen Cetak -->
            <div class="pt-4 border-t border-slate-200 text-[11px] text-slate-500 flex flex-col sm:flex-row sm:items-center justify-between gap-2 font-mono">
                <span>Direktorat Jenderal Imigrasi &bull; Formulir PERDIM 14</span>
                <span>Dicetak pada: {{ date('d F Y, H:i') }} WIB</span>
            </div>
        </div>

        <!-- Tombol Aksi Layar (Disembunyikan Otomatis Saat Dicetak) -->
        <div class="no-print bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
            <a href="{{ route('public.perdim.create') }}" class="w-full sm:w-auto text-center px-4 py-2 border border-slate-300 text-xs font-semibold rounded-md text-slate-700 bg-white hover:bg-slate-100 transition-colors">
                + Buat Permohonan Baru
            </a>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                <button type="button" onclick="window.print()" class="w-full sm:w-auto px-4 py-2 border border-slate-300 text-xs font-semibold rounded-md text-slate-800 bg-white hover:bg-slate-100 transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Lembar Tanda Terima</span>
                </button>

                <a href="{{ route('status.show') }}" class="w-full sm:w-auto text-center px-4 py-2 bg-[#1B365D] hover:bg-[#12243E] text-white text-xs font-semibold rounded-md shadow-xs transition-colors">
                    Pantau Status Permohonan &rarr;
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
