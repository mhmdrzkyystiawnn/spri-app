@extends('layouts.admin')

@section('title', 'Manajemen Pengguna & Hak Akses - Meja Layanan')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-stone-200">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-mono font-semibold tracking-wider uppercase bg-stone-100 text-stone-700 rounded border border-stone-200 mb-1">
                <span class="w-1.5 h-1.5 rounded-full bg-[#1B365D]"></span>
                Direktori Aparatur & Autentikasi
            </div>
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 font-serif">
                Manajemen Petugas & Pengguna Sistem
            </h2>
            <p class="text-xs text-stone-600">
                Pengelolaan akun petugas verifikator, pimpinan, dan hak akses administrasi meja layanan SPRI.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Tambah Petugas Baru
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="editorial-card p-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="flex-1">
                <label for="search" class="sr-only">Cari Petugas</label>
                <input 
                    type="text" 
                    id="search"
                    name="search" 
                    value="{{ $searchTerm }}" 
                    placeholder="Cari nama atau email dinas petugas..."
                    class="editorial-input w-full text-xs"
                >
            </div>
            <div class="sm:w-56">
                <label for="role" class="sr-only">Peran Petugas</label>
                <select id="role" name="role" class="editorial-input w-full text-xs bg-white">
                    <option value="">Semua Peran Petugas</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" {{ $currentRole === $role->value ? 'selected' : '' }}>
                            {{ $role->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary text-xs py-2">
                    Terapkan Filter
                </button>
                @if ($searchTerm || $currentRole)
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary text-xs py-2">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Pengguna -->
    <div class="editorial-card overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="editorial-header text-stone-700 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 border-b border-stone-200">Nama Petugas</th>
                        <th class="py-3 px-4 border-b border-stone-200">Email Dinas</th>
                        <th class="py-3 px-4 border-b border-stone-200">Peran Sistem</th>
                        <th class="py-3 px-4 border-b border-stone-200">Status Akun</th>
                        <th class="py-3 px-4 border-b border-stone-200">Terdaftar</th>
                        <th class="py-3 px-4 border-b border-stone-200 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($users as $user)
                        <tr class="hover:bg-stone-50/80 transition-colors {{ ! $user->is_active ? 'bg-stone-50/50 opacity-75' : '' }}">
                            <td class="py-3 px-4 font-medium text-stone-900 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-stone-900">{{ $user->name }}</span>
                                    @if ($user->id === auth()->id())
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-mono bg-stone-200 text-stone-700">
                                            (Anda)
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-stone-700 whitespace-nowrap">
                                {{ $user->email }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($user->role)
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium border {{ $user->role->badgeClasses() }}">
                                        {{ $user->role->label() }}
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium border bg-stone-100 text-stone-600 border-stone-300">
                                        Non-Staff
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-stone-100 text-stone-600 border border-stone-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-stone-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-stone-500 whitespace-nowrap font-mono text-[11px]">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary text-[11px] py-1 px-2" title="Edit Akun Petugas">
                                    Edit
                                </a>

                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button 
                                            type="submit" 
                                            class="btn-secondary text-[11px] py-1 px-2 {{ $user->is_active ? 'text-amber-700 hover:text-amber-800' : 'text-emerald-700 hover:text-emerald-800' }}"
                                            onclick="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan kembali' }} akun {{ $user->name }}?');"
                                        >
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger text-[11px] py-1 px-2">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-stone-500">
                                <p class="text-sm">Tidak ada petugas yang sesuai dengan kriteria.</p>
                                @if ($searchTerm || $currentRole)
                                    <p class="text-xs mt-1">Coba sesuaikan pencarian atau reset filter di atas.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="p-4 border-t border-stone-200 bg-stone-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
