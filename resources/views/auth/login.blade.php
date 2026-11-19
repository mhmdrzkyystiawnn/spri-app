@extends('layouts.app')

@section('title', 'Masuk Meja Layanan Admin - Verifikasi SPRI')

@section('content')
<div class="max-w-4xl w-full mx-auto py-4 sm:py-8">
    <div class="editorial-card overflow-hidden grid grid-cols-1 md:grid-cols-12 border-stone-300 shadow-sm min-h-[500px]">
        
        <!-- Kolom Kiri: Panel Kewibawaan Instansi (#1B365D Deep Maritime Navy) -->
        <div class="panel-authority-navy md:col-span-5 bg-[#1B365D] text-white p-6 sm:p-8 flex flex-col justify-between border-b md:border-b-0 md:border-r border-[#2A4D7E]">
            <div class="space-y-5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded bg-[#12243E] border border-amber-400/40 flex items-center justify-center font-bold text-amber-300 text-xs tracking-wider shadow-xs">
                        RI
                    </div>
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-300 font-semibold block">Ditjen Imigrasi</span>
                        <span class="text-xs text-slate-200 font-medium">Layanan Verifikasi SPRI</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-white/10 space-y-2">
                    <span class="badge-navy-dark inline-block px-2 py-0.5 rounded text-[10px] font-mono font-bold tracking-wider bg-[#12243E] text-amber-300 border border-amber-400/30">
                        OTORISASI PETUGAS
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white font-serif leading-snug">
                        Masuk Meja Layanan Verifikator
                    </h2>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Akses terbatas khusus pegawai seksi Lalu Lintas Keimigrasian (Lantaskim) dan pimpinan kantor imigrasi yang memiliki hak otorisasi dinas.
                    </p>
                </div>
            </div>

            <!-- Peringatan Integritas Data & Audit Trail -->
            <div class="pt-6 border-t border-white/10 space-y-2.5 text-[11px] text-slate-300">
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <p class="leading-relaxed">
                        <strong>Integritas Audit:</strong> Setiap aktivitas verifikasi, pemeriksaan checklist, dan mutasi status dicatat secara otomatis dalam sistem audit log.
                    </p>
                </div>
                <p class="text-[10px] text-slate-400 font-mono pt-1">
                    Protokol Keamanan &bull; Sesi Resmi Terenkripsi
                </p>
            </div>
        </div>

        <!-- Kolom Kanan: Form Autentikasi Petugas -->
        <div class="md:col-span-7 bg-white p-6 sm:p-10 flex flex-col justify-between">
            <div>
                <div class="mb-6 space-y-1">
                    <h3 class="text-lg sm:text-xl font-bold text-stone-900 font-serif">
                        Otorisasi Akses Internal
                    </h3>
                    <p class="text-xs text-stone-500">
                        Silakan masukkan alamat email dinas dan kata sandi Anda.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded border border-emerald-300 bg-emerald-50 p-3 text-xs text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('login.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                            Email Dinas Petugas <span class="text-required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email', 'admin@imigrasi.go.id') }}" 
                            required 
                            autocomplete="username"
                            placeholder="nama.petugas@imigrasi.go.id"
                            class="editorial-input w-full @error('email') has-error border-rose-500 @enderror"
                            autofocus
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                            Kata Sandi <span class="text-required">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="••••••••••••"
                            class="editorial-input w-full @error('password') has-error border-rose-500 @enderror"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-stone-600 select-none">
                            <input type="checkbox" name="remember" class="rounded border-stone-300 text-[#1B365D] focus:ring-[#1B365D]">
                            <span>Ingat sesi ini</span>
                        </label>
                        <span class="text-[11px] text-stone-400 font-mono">Dev: SecretAdmin123!</span>
                    </div>

                    <button type="submit" class="btn-primary w-full py-2.5 text-sm tracking-wide mt-2 shadow-xs">
                        Masuk ke Dashboard Verifikasi
                    </button>
                </form>
            </div>

            <div class="pt-6 mt-6 border-t border-stone-200 text-center">
                <a href="{{ route('status.show') }}" class="text-xs text-stone-500 hover:text-stone-900 underline underline-offset-4 transition-colors">
                    &larr; Kembali ke Portal Pencarian Status Publik
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
