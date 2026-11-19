@extends('layouts.admin')

@section('title', 'Ubah Data Petugas: ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Form -->
    <div class="pb-3 border-b border-stone-200">
        <span class="text-[11px] font-mono uppercase tracking-wider text-stone-500">ADMINISTRASI PENGGUNA</span>
        <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 font-serif">
            Perbarui Akun Petugas: {{ $user->name }}
        </h2>
        <p class="text-xs text-stone-600 mt-1">
            Ubah nama, email dinas, hak akses peran, atau setel ulang kata sandi petugas.
        </p>
    </div>

    <!-- Card Form -->
    <div class="editorial-card p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama Petugas -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                    Nama Lengkap Petugas <span class="text-red-700">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $user->name) }}" 
                    required 
                    class="editorial-input w-full text-sm @error('name') border-red-500 @enderror"
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
                    value="{{ old('email', $user->email) }}" 
                    required 
                    class="editorial-input w-full text-sm font-mono @error('email') border-red-500 @enderror"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kata Sandi Baru (Opsional) -->
            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
                    Kata Sandi Baru (Opsional)
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    minlength="8"
                    class="editorial-input w-full text-sm font-mono @error('password') border-red-500 @enderror"
                    placeholder="Kosongkan bila tidak ingin mengganti kata sandi..."
                >
                <p class="mt-1 text-[11px] text-stone-500">Biarkan kosong jika kata sandi tidak ingin diubah.</p>
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
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" {{ old('role', $user->role?->value) === $role->value ? 'selected' : '' }}>
                            {{ $role->label() }} ({{ $role->value }})
                        </option>
                    @endforeach
                </select>
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
                        {{ old('is_active', $user->is_active ? '1' : '0') === '1' ? 'checked' : '' }}
                        class="rounded border-stone-300 text-stone-900 focus:ring-stone-900 h-4 w-4"
                        {{ $user->id === auth()->id() ? 'disabled' : '' }}
                    >
                    <span class="text-xs font-semibold text-stone-800">
                        Akun Aktif (Dapat Mengakses Meja Layanan)
                    </span>
                </label>
                @if ($user->id === auth()->id())
                    <input type="hidden" name="is_active" value="1">
                    <p class="text-[11px] text-amber-700 ml-6 mt-0.5">Anda tidak dapat menonaktifkan akun yang sedang digunakan saat ini.</p>
                @else
                    <p class="text-[11px] text-stone-500 ml-6">Jika tidak dicentang, akun tidak dapat masuk ke sistem.</p>
                @endif
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
                    Perbarui Akun Petugas
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
