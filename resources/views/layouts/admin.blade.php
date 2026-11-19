<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Meja Layanan Verifikasi SPRI - Administrasi')</title>

    <!-- Tailwind / Editorial CSS -->
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif
    <link rel="stylesheet" href="{{ asset('css/editorial.css') }}">
</head>
<body class="min-h-[100dvh] flex flex-col justify-between bg-[#FBFBFA] text-[#1C1917]">
    <!-- Accessibility Skip Link -->
    <a href="#admin-main-content" class="skip-link">Lewati ke konten meja layanan</a>

    <!-- Admin Topbar (Deep Maritime Navy #1B365D) -->
    <header class="admin-navbar bg-[#1B365D] text-white border-b border-[#2A4D7E] shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="badge-navy-dark inline-block px-2 py-0.5 rounded text-[11px] font-mono font-bold tracking-wider bg-[#12243E] text-amber-300 border border-amber-400/40">
                    LOKET INTERNAL
                </span>
                <div>
                    <h1 class="text-sm font-semibold tracking-tight text-white flex items-center gap-2">
                        Meja Layanan Verifikasi Berkas SPRI
                    </h1>
                    <p class="text-[11px] text-slate-300">Direktorat Jenderal Imigrasi &bull; Modul Administrasi Verifikator</p>
                </div>
            </div>

            <!-- Navigation & User Info -->
            <div class="flex flex-wrap items-center gap-4 text-xs">
                <a href="{{ route('admin.verifications.index') }}" class="transition-colors {{ request()->routeIs('admin.verifications.index') ? 'font-bold text-white border-b-2 border-amber-400 pb-0.5' : 'text-slate-300 hover:text-white' }}">
                    Daftar Berkas
                </a>
                @can('create', App\Models\Verification::class)
                <a href="{{ route('admin.verifications.create') }}" class="transition-colors {{ request()->routeIs('admin.verifications.create') ? 'font-bold text-white border-b-2 border-amber-400 pb-0.5' : 'text-slate-300 hover:text-white' }}">
                    + Tambah Berkas
                </a>
                @endcan
                @can('manage-users')
                <a href="{{ route('admin.users.index') }}" class="transition-colors {{ request()->routeIs('admin.users.*') ? 'font-bold text-white border-b-2 border-amber-400 pb-0.5' : 'text-slate-300 hover:text-white' }}">
                    Kelola Pengguna
                </a>
                @endcan
                @can('manage-document-requirements')
                <a href="{{ route('admin.document-requirements.index') }}" class="transition-colors {{ request()->routeIs('admin.document-requirements.*') ? 'font-bold text-white border-b-2 border-amber-400 pb-0.5' : 'text-slate-300 hover:text-white' }}">
                    Master Persyaratan
                </a>
                @endcan
                <a href="{{ route('status.show') }}" target="_blank" class="text-slate-300 hover:text-white transition-colors">
                    Portal Publik &nearr;
                </a>

                <div class="h-4 w-px bg-slate-600 hidden sm:block"></div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-200 font-medium">
                            {{ auth()->user()->name }}
                        </span>
                        @if (auth()->user()->role)
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-mono border {{ auth()->user()->role->badgeClasses() }}">
                                {{ auth()->user()->role->label() }}
                            </span>
                        @endif
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-slate-300 hover:text-rose-300 underline decoration-slate-400 hover:decoration-rose-300 underline-offset-2 transition-colors cursor-pointer">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main id="admin-main-content" class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 py-6">
        <!-- Flash Alert -->
        @if (session('status'))
            <div class="mb-5 rounded border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 flex items-start justify-between gap-3 shadow-xs">
                <div>
                    <strong class="font-semibold">Sukses:</strong> {{ session('status') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-900 flex items-start justify-between gap-3 shadow-xs">
                <div>
                    <strong class="font-semibold">Perhatian:</strong> {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-[#E2E8F0] bg-[#F8FAFC] py-4 px-4 text-center text-xs text-[#64748B]">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>&copy; {{ date('Y') }} Sistem Verifikasi SPRI &bull; Dokumentasi PKL</p>
            <p class="font-mono text-[11px]">Audit Logging Aktif &bull; Sesi Aman</p>
        </div>
    </footer>
</body>
</html>
