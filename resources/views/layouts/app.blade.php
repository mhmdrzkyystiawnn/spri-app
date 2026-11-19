<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Cek Status Verifikasi SPRI - Direktorat Jenderal Imigrasi')</title>

    <!-- Tailwind / Editorial CSS -->
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif
    <link rel="stylesheet" href="{{ asset('css/editorial.css') }}">
</head>
<body class="min-h-[100dvh] flex flex-col justify-between bg-[#FBFBFA] text-[#1C1917]">
    <!-- Accessibility Skip Link -->
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    <!-- Top Bar Institusi Resmi (Deep Maritime Navy #1B365D) -->
    <header class="official-strip py-3.5 px-4 sm:px-6 shadow-xs">
        <div class="max-w-5xl mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded bg-[#12243E] border border-amber-400/40 flex items-center justify-center font-bold text-amber-300 text-xs tracking-wider shadow-xs">
                    RI
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-300 font-medium">Kementerian Hukum dan HAM RI &bull; Ditjen Imigrasi</p>
                    <h1 class="text-sm font-semibold tracking-wide text-white">Layanan Mandiri Status Verifikasi Dokumen SPRI</h1>
                </div>
            </div>
            <nav class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs">
                <a href="{{ route('public.perdim.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-amber-400 text-slate-900 font-bold hover:bg-amber-300 transition-colors shadow-xs">
                    <span>+ Permohonan Baru (PERDIM 14)</span>
                </a>
                <a href="{{ route('status.show') }}" class="text-slate-200 hover:text-white underline decoration-slate-400 underline-offset-4">
                    Cek Status
                </a>
                <span class="text-slate-400 hidden sm:inline">|</span>
                <a href="{{ route('login') }}" class="text-slate-200 hover:text-white underline decoration-slate-400 underline-offset-4 focus-visible:outline-2 focus-visible:outline-white">
                    Masuk Meja Layanan &rarr;
                </a>
            </nav>
        </div>
    </header>

    <!-- Konten Utama -->
    <main id="main-content" class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 py-8">
        @yield('content')
    </main>

    <!-- Footer Resmi -->
    <footer class="border-t border-[#E2E8F0] bg-[#F8FAFC] py-6 px-4 text-center text-xs text-[#64748B]">
        <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <p>&copy; {{ date('Y') }} Sistem Informasi Verifikasi Dokumen SPRI &bull; Proyek Simulasi Laporan PKL</p>
            <p class="font-mono text-[11px] text-[#64748B]">Keamanan & Transparansi Layanan Publik</p>
        </div>
    </footer>
</body>
</html>
