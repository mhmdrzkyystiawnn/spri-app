@extends('layouts.admin')

@section('title', 'Master Persyaratan Dokumen - Meja Layanan SPRI')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-stone-200">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-mono font-semibold tracking-wider uppercase bg-stone-100 text-stone-700 rounded border border-stone-200 mb-1">
                <span class="w-1.5 h-1.5 rounded-full bg-[#1B365D]"></span>
                Katalog Kebijakan Persyaratan Fisik
            </div>
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 font-serif">
                Master Persyaratan Dokumen SPRI
            </h2>
            <p class="text-xs text-stone-600">
                Konfigurasi template checklist fisik dokumen permohonan paspor yang wajib dipenuhi oleh pemohon.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.document-requirements.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Persyaratan
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="editorial-card p-4">
        <form method="GET" action="{{ route('admin.document-requirements.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="flex-1">
                <label for="search" class="sr-only">Pencarian Persyaratan</label>
                <input 
                    type="text" 
                    id="search"
                    name="search" 
                    value="{{ $searchTerm }}" 
                    placeholder="Cari kode atau nama persyaratan dokumen..."
                    class="editorial-input w-full text-xs"
                >
            </div>
            <div class="sm:w-64">
                <label for="document_type" class="sr-only">Jenis Permohonan</label>
                <select id="document_type" name="document_type" class="editorial-input w-full text-xs bg-white">
                    <option value="">Semua Jenis Permohonan</option>
                    @foreach ($documentTypes as $type)
                        <option value="{{ $type }}" {{ $currentType === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:w-40">
                <label for="status" class="sr-only">Status Persyaratan</label>
                <select id="status" name="status" class="editorial-input w-full text-xs bg-white">
                    <option value="">Semua Status</option>
                    <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Aktif Digunakan</option>
                    <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary text-xs py-2">
                    Filter
                </button>
                @if ($searchTerm || $currentType || $currentStatus)
                    <a href="{{ route('admin.document-requirements.index') }}" class="btn-secondary text-xs py-2">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Master Persyaratan -->
    <div class="editorial-card overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="editorial-header text-stone-700 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 border-b border-stone-200">Jenis Permohonan</th>
                        <th class="py-3 px-4 border-b border-stone-200">Kode</th>
                        <th class="py-3 px-4 border-b border-stone-200">Nama Persyaratan Dokumen</th>
                        <th class="py-3 px-4 border-b border-stone-200">Sifat</th>
                        <th class="py-3 px-4 border-b border-stone-200 text-center">Urutan</th>
                        <th class="py-3 px-4 border-b border-stone-200">Status</th>
                        <th class="py-3 px-4 border-b border-stone-200 text-center">Dipakai</th>
                        <th class="py-3 px-4 border-b border-stone-200 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($requirements as $req)
                        <tr class="hover:bg-stone-50/80 transition-colors {{ ! $req->is_active ? 'bg-stone-50/50 opacity-75' : '' }}">
                            <td class="py-3 px-4 text-stone-800 font-medium">
                                {{ $req->document_type }}
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-stone-900 whitespace-nowrap">
                                <span class="bg-stone-100 px-1.5 py-0.5 rounded border border-stone-200">
                                    {{ $req->code }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-stone-900 font-medium">
                                <a href="{{ route('admin.document-requirements.show', $req) }}" class="hover:text-[#1B365D] underline decoration-stone-300">
                                    {{ $req->name }}
                                </a>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($req->is_mandatory)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-800 border border-rose-300">
                                        Wajib
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-stone-100 text-stone-600 border border-stone-300">
                                        Opsional
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-stone-600 whitespace-nowrap">
                                {{ $req->sort_order }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($req->is_active)
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
                            <td class="py-3 px-4 text-center font-mono text-stone-500 whitespace-nowrap text-[11px]">
                                {{ $req->checklists_count }} berkas
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('admin.document-requirements.edit', $req) }}" class="btn-secondary text-[11px] py-1 px-2" title="Edit Persyaratan">
                                    Edit
                                </a>

                                <form action="{{ route('admin.document-requirements.toggle-active', $req) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button 
                                        type="submit" 
                                        class="btn-secondary text-[11px] py-1 px-2 {{ $req->is_active ? 'text-amber-700 hover:text-amber-800' : 'text-emerald-700 hover:text-emerald-800' }}"
                                        title="{{ $req->is_active ? 'Nonaktifkan dari permohonan baru' : 'Aktifkan kembali' }}"
                                    >
                                        {{ $req->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.document-requirements.destroy', $req) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus/menonaktifkan persyaratan {{ $req->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger text-[11px] py-1 px-2" title="Hapus atau Nonaktifkan">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-stone-500">
                                <p class="text-sm">Tidak ada master persyaratan dokumen yang sesuai kriteria.</p>
                                @if ($searchTerm || $currentType || $currentStatus)
                                    <p class="text-xs mt-1">Coba sesuaikan filter atau reset pencarian di atas.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($requirements->hasPages())
            <div class="p-4 border-t border-stone-200 bg-stone-50/50">
                {{ $requirements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
