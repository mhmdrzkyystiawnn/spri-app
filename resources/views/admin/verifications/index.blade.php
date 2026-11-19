@extends('layouts.admin')

@section('title', 'Daftar Berkas Verifikasi SPRI - Meja Layanan')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-stone-200">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-mono font-semibold tracking-wider uppercase bg-stone-100 text-stone-700 rounded border border-stone-200 mb-1">
                <span class="w-1.5 h-1.5 rounded-full bg-[#1B365D]"></span>
                Meja Layanan Verifikasi Berkas Fisik
            </div>
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 font-serif">
                Daftar Berkas Permohonan SPRI
            </h2>
            <p class="text-xs text-stone-600">
                Pemeriksaan fisik dokumen, validasi identitas, dan pembaruan status verifikasi pemohon.
            </p>
        </div>
        @can('create', App\Models\Verification::class)
        <div class="shrink-0">
            <a href="{{ route('admin.verifications.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Daftarkan Berkas Baru
            </a>
        </div>
        @endcan
    </div>

    <!-- Continuous Metrics Bar Terpadu (Divide-X & Tabular Numerals) -->
    <div class="editorial-card p-0 overflow-hidden shadow-xs">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 divide-y sm:divide-y-0 sm:divide-x divide-stone-200">
            <!-- Total Berkas -->
            <a href="{{ route('admin.verifications.index') }}" 
               class="p-4 transition-colors hover:bg-stone-50 flex flex-col justify-between group {{ empty($currentStatus) ? 'bg-stone-50/80 border-b-2 border-b-stone-900' : '' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-stone-500 group-hover:text-stone-800">Total Berkas</span>
                    <span class="w-2 h-2 rounded-full bg-stone-400"></span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-stone-900 font-mono tabular-nums">{{ $stats['total'] }}</span>
                    <span class="text-[10px] text-stone-500 font-mono">dokumen</span>
                </div>
            </a>

            <!-- Menunggu -->
            <a href="{{ route('admin.verifications.index', ['status' => 'pending']) }}" 
               class="p-4 transition-colors hover:bg-amber-50/40 flex flex-col justify-between group {{ $currentStatus === 'pending' ? 'bg-amber-50/60 border-b-2 border-b-amber-600' : '' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-amber-800 group-hover:text-amber-950">Menunggu</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-amber-900 font-mono tabular-nums">{{ $stats['pending'] }}</span>
                    <span class="text-[10px] text-amber-700 font-mono">antrean</span>
                </div>
            </a>

            <!-- Diverifikasi -->
            <a href="{{ route('admin.verifications.index', ['status' => 'in_review']) }}" 
               class="p-4 transition-colors hover:bg-sky-50/40 flex flex-col justify-between group {{ $currentStatus === 'in_review' ? 'bg-sky-50/60 border-b-2 border-b-sky-600' : '' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-sky-800 group-hover:text-sky-950">Diverifikasi</span>
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-sky-900 font-mono tabular-nums">{{ $stats['in_review'] }}</span>
                    <span class="text-[10px] text-sky-700 font-mono">proses</span>
                </div>
            </a>

            <!-- Perlu Perbaikan -->
            <a href="{{ route('admin.verifications.index', ['status' => 'needs_revision']) }}" 
               class="p-4 transition-colors hover:bg-orange-50/40 flex flex-col justify-between group {{ $currentStatus === 'needs_revision' ? 'bg-orange-50/60 border-b-2 border-b-orange-600' : '' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-orange-800 group-hover:text-orange-950">Perlu Perbaikan</span>
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-orange-900 font-mono tabular-nums">{{ $stats['needs_revision'] }}</span>
                    <span class="text-[10px] text-orange-700 font-mono">revisi</span>
                </div>
            </a>

            <!-- Disetujui -->
            <a href="{{ route('admin.verifications.index', ['status' => 'approved']) }}" 
               class="p-4 transition-colors hover:bg-emerald-50/40 flex flex-col justify-between group {{ $currentStatus === 'approved' ? 'bg-emerald-50/60 border-b-2 border-b-emerald-600' : '' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-emerald-800 group-hover:text-emerald-950">Disetujui</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-emerald-900 font-mono tabular-nums">{{ $stats['approved'] }}</span>
                    <span class="text-[10px] text-emerald-700 font-mono">sah</span>
                </div>
            </a>

            <!-- Ditolak -->
            <a href="{{ route('admin.verifications.index', ['status' => 'rejected']) }}" 
               class="p-4 transition-colors hover:bg-rose-50/40 flex flex-col justify-between group {{ $currentStatus === 'rejected' ? 'bg-rose-50/60 border-b-2 border-b-rose-600' : '' }}">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-rose-800 group-hover:text-rose-950">Ditolak</span>
                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-rose-900 font-mono tabular-nums">{{ $stats['rejected'] }}</span>
                    <span class="text-[10px] text-rose-700 font-mono">gugur</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Alert Kesalahan Validasi Filter -->
    @if ($errors->any())
        <div class="rounded border border-rose-300 bg-rose-50 p-4 text-xs text-rose-900 shadow-xs">
            <strong class="font-semibold block mb-1">Parameter Filter Tidak Valid:</strong>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filter & Pencarian Bar Interaktif -->
    <div class="editorial-card p-4 space-y-4">
        <form method="GET" action="{{ route('admin.verifications.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- Search Keyword -->
                <div>
                    <label for="search" class="block text-[11px] font-semibold uppercase tracking-wider text-stone-700 mb-1">
                        Pencarian Berkas
                    </label>
                    <input 
                        type="text" 
                        id="search"
                        name="search" 
                        value="{{ $searchTerm }}" 
                        placeholder="Cari kode registrasi atau nama pemohon..."
                        class="editorial-input w-full text-xs"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-[11px] font-semibold uppercase tracking-wider text-stone-700 mb-1">
                        Status Verifikasi
                    </label>
                    <select id="status" name="status" class="editorial-input w-full text-xs bg-white">
                        <option value="">Semua Status Verifikasi</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" {{ $currentStatus === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Document Type Filter -->
                <div>
                    <label for="document_type" class="block text-[11px] font-semibold uppercase tracking-wider text-stone-700 mb-1">
                        Jenis Dokumen SPRI
                    </label>
                    <select id="document_type" name="document_type" class="editorial-input w-full text-xs bg-white">
                        <option value="">Semua Jenis Dokumen SPRI</option>
                        @foreach ($documentTypes as $type)
                            <option value="{{ $type }}" {{ $currentDocumentType === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end pt-2 border-t border-stone-200">
                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-[11px] font-semibold uppercase tracking-wider text-stone-700 mb-1">
                        Tanggal Mulai (Dari)
                    </label>
                    <input 
                        type="date" 
                        id="date_from"
                        name="date_from" 
                        value="{{ $currentDateFrom }}" 
                        class="editorial-input w-full text-xs bg-white font-mono"
                    >
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-[11px] font-semibold uppercase tracking-wider text-stone-700 mb-1">
                        Tanggal Akhir (Sampai)
                    </label>
                    <input 
                        type="date" 
                        id="date_to"
                        name="date_to" 
                        value="{{ $currentDateTo }}" 
                        class="editorial-input w-full text-xs bg-white font-mono"
                    >
                </div>

                <!-- Completeness -->
                <div>
                    <label for="completeness" class="block text-[11px] font-semibold uppercase tracking-wider text-stone-700 mb-1">
                        Kelengkapan Fisik
                    </label>
                    <select id="completeness" name="completeness" class="editorial-input w-full text-xs bg-white">
                        <option value="">Semua Kelengkapan</option>
                        <option value="complete" {{ $currentCompleteness === 'complete' ? 'selected' : '' }}>
                            Lengkap (Semua Syarat Wajib Terpenuhi)
                        </option>
                        <option value="incomplete" {{ $currentCompleteness === 'incomplete' ? 'selected' : '' }}>
                            Belum Lengkap (Syarat Wajib Belum Terpenuhi)
                        </option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="btn-primary text-xs py-2 px-4 flex-1 justify-center">
                        Terapkan Filter
                    </button>
                    @if ($hasActiveFilters)
                        <a href="{{ route('admin.verifications.index') }}" class="btn-secondary text-xs py-2 px-3 text-center" title="Hapus semua filter">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Active Filter Indicator / Chips -->
        @if ($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-stone-200 text-xs">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">Filter Aktif:</span>
                @if ($searchTerm)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-stone-200 text-stone-800 text-[11px]">
                        Kata Kunci: <strong>{{ $searchTerm }}</strong>
                    </span>
                @endif
                @if ($currentStatus)
                    @php $statusObj = collect($statuses)->firstWhere('value', $currentStatus); @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-100 text-amber-900 text-[11px]">
                        Status: <strong>{{ $statusObj ? $statusObj->label() : $currentStatus }}</strong>
                    </span>
                @endif
                @if ($currentDocumentType)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-sky-100 text-sky-900 text-[11px]">
                        Dokumen: <strong>{{ $currentDocumentType }}</strong>
                    </span>
                @endif
                @if ($currentDateFrom || $currentDateTo)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-100 text-emerald-900 text-[11px]">
                        Periode: <strong>{{ $currentDateFrom ?? '...' }} s/d {{ $currentDateTo ?? '...' }}</strong>
                    </span>
                @endif
                @if ($currentCompleteness)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded {{ $currentCompleteness === 'complete' ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900' }} text-[11px]">
                        Fisik: <strong>{{ $currentCompleteness === 'complete' ? 'Lengkap' : 'Belum Lengkap' }}</strong>
                    </span>
                @endif
                <a href="{{ route('admin.verifications.index') }}" class="text-[11px] text-red-700 hover:text-red-900 underline ml-auto">
                    Hapus Semua Filter
                </a>
            </div>
        @endif
    </div>

    <!-- Tabel Data Verifikasi -->
    <div class="editorial-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="editorial-header text-stone-700 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 border-b border-stone-200">No. Registrasi</th>
                        <th class="py-3 px-4 border-b border-stone-200">Nama Pemohon</th>
                        <th class="py-3 px-4 border-b border-stone-200">Jenis Dokumen SPRI</th>
                        <th class="py-3 px-4 border-b border-stone-200">Status</th>
                        <th class="py-3 px-4 border-b border-stone-200">Petugas</th>
                        <th class="py-3 px-4 border-b border-stone-200">Tanggal Update</th>
                        <th class="py-3 px-4 border-b border-stone-200 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($verifications as $v)
                        <tr class="hover:bg-stone-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-stone-900 whitespace-nowrap">
                                <a href="{{ route('admin.verifications.show', $v) }}" class="hover:text-[#1B365D] underline decoration-stone-300">
                                    {{ $v->registration_code }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-medium text-stone-900 whitespace-nowrap">
                                {{ $v->applicant_name }}
                            </td>
                            <td class="py-3 px-4 text-stone-700">
                                {{ $v->document_type }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="badge-status status-{{ $v->status->value }} text-[11px]">
                                    {{ $v->status->label() }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-stone-500 whitespace-nowrap">
                                {{ $v->creator?->name ?? 'Sistem' }}
                            </td>
                            <td class="py-3 px-4 text-stone-500 whitespace-nowrap font-mono text-[11px]">
                                {{ $v->updated_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('admin.verifications.show', $v) }}" class="btn-secondary text-[11px] py-1 px-2" title="Detail & Audit">
                                    Detail
                                </a>
                                @can('update', $v)
                                <a href="{{ route('admin.verifications.edit', $v) }}" class="btn-secondary text-[11px] py-1 px-2" title="Ubah Berkas">
                                    Edit
                                </a>
                                @endcan
                                @can('delete', $v)
                                <form action="{{ route('admin.verifications.destroy', $v) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas verifikasi nomor {{ $v->registration_code }}? Data akan di-soft delete.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger text-[11px] py-1 px-2">
                                        Hapus
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-stone-500">
                                <div class="max-w-md mx-auto space-y-2">
                                    <p class="text-sm font-medium text-stone-700">Tidak ada berkas verifikasi yang sesuai dengan kriteria filter.</p>
                                    @if ($hasActiveFilters)
                                        <p class="text-xs text-stone-500">Silakan sesuaikan kata kunci pencarian, rentang tanggal, atau reset filter aktif di atas.</p>
                                        <div class="pt-2">
                                            <a href="{{ route('admin.verifications.index') }}" class="btn-secondary text-xs py-1.5 px-3 inline-flex items-center gap-1">
                                                Reset Semua Filter
                                            </a>
                                        </div>
                                    @else
                                        <p class="text-xs text-stone-500">Belum ada berkas permohonan yang terdaftar pada sistem.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($verifications->hasPages())
            <div class="p-4 border-t border-stone-200 bg-stone-50/50">
                {{ $verifications->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
