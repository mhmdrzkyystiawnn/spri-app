@extends('layouts.admin')

@section('title', 'Tambah Petugas Baru - Meja Layanan SPRI')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Form -->
    <div class="pb-3 border-b border-stone-200">
        <span class="text-[11px] font-mono uppercase tracking-wider text-stone-500">ADMINISTRASI PENGGUNA</span>
        <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 font-serif">
            Daftarkan Akun Petugas Baru
        </h2>
        <p class="text-xs text-stone-600 mt-1">
            Buat akun untuk verifikator berkas atau pimpinan dengan hak akses yang terisolasi sesuai kewenangan.
        </p>
    </div>

    <!-- Card Form -->
    <div class="editorial-card p-6">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <!-- Nama Petugas -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                    Nama Lengkap Petugas <span class="text-red-700">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required 
                    class="editorial-input w-full text-sm @error('name') border-red-500 @enderror"
                    placeholder="Contoh: Ahmad Fauzi, S.H."
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Dinas -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                    Alamat Email Dinas <span class="text-red-700">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    class="editorial-input w-full text-sm font-mono @error('email') border-red-500 @enderror"
                    placeholder="petugas@imigrasi.go.id"
                >
                <p class="mt-1 text-[11px] text-stone-500">Email digunakan sebagai ID masuk ke dalam sistem internal.</p>
                @error('email')
                    <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kata Sandi -->
            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                    Kata Sandi <span class="text-red-700">*</span>
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    minlength="8"
                    class="editorial-input w-full text-sm font-mono @error('password') border-red-500 @enderror"
                    placeholder="Minimal 8 karakter..."
                >
                <p class="mt-1 text-[11px] text-stone-500">Gunakan kombinasi karakter yang kuat untuk keamanan loket.</p>
                @error('password')
                    <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- Peran Sistem -->
            <div>
                <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                    Peran & Hak Akses <span class="text-red-700">*</span>
                </label>
                <select 
                    id="role" 
                    name="role" 
                    required 
                    class="editorial-input w-full text-sm bg-white @error('role') border-red-500 @enderror"
                >
                    <option value="">Pilih peran petugas...</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>
                            {{ $role->label() }} ({{ $role->value }})
                        </option>
                    @endforeach
                </select>
                <div class="mt-2 text-[11px] text-stone-600 space-y-1 bg-stone-50 p-3 rounded border border-stone-200">
                    <p><strong>Panduan Hak Akses:</strong></p>
                    <p>&bull; <strong>Administrator:</strong> Akses penuh verifikasi dokumen dan kelola seluruh akun pengguna.</p>
                    <p>&bull; <strong>Petugas Verifikator:</strong> Mendaftar, memeriksa berkas, mengubah status/catatan verifikasi. Tidak dapat menghapus berkas atau mengelola pengguna.</p>
                    <p>&bull; <strong>Kepala Seksi / Pimpinan:</strong> Memantau dashboard, melihat dossier berkas dan riwayat audit (read-only). Tidak dapat memutasi data berkas.</p>
                </div>
                @error('role')
                    <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Akun Aktif -->
            <div class="pt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', '1') === '1' ? 'checked' : '' }}
                        class="rounded border-stone-300 text-stone-900 focus:ring-stone-900 h-4 w-4"
                    >
                    <span class="text-xs font-semibold text-stone-800">
                        Akun Langsung Aktif
                    </span>
                </label>
                <p class="text-[11px] text-stone-500 ml-6">Jika tidak dicentang, akun tidak akan dapat login ke meja layanan.</p>
                @error('is_active')
                    <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-stone-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary text-xs">
                    Batal
                </a>
                <button type="submit" class="btn-primary text-xs">
                    Simpan Akun Petugas
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
