@extends('layouts.admin')

@section('title', 'Detail Persyaratan: ' . $requirement->code . ' - Meja Layanan SPRI')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-stone-200">
        <div>
            <span class="text-[11px] font-mono uppercase tracking-wider text-stone-500">MASTER PERSYARATAN</span>
            <h2 class="text-2xl font-bold tracking-tight text-stone-900 font-mono flex items-center gap-2">
                {{ $requirement->code }}
            </h2>
            <p class="text-xs text-stone-600 mt-0.5">{{ $requirement->name }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.document-requirements.edit', $requirement) }}" class="btn-primary text-xs">
                Edit Persyaratan
            </a>
            <a href="{{ route('admin.document-requirements.index') }}" class="btn-secondary text-xs">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Rincian Informasi -->
    <div class="editorial-card p-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-xs">
            <div>
                <dt class="text-stone-500 font-medium">Jenis Permohonan SPRI:</dt>
                <dd class="text-stone-900 font-bold text-sm mt-0.5">{{ $requirement->document_type }}</dd>
            </div>
            <div>
                <dt class="text-stone-500 font-medium">Kode Persyaratan:</dt>
                <dd class="text-stone-900 font-mono font-bold text-sm mt-0.5">{{ $requirement->code }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-stone-500 font-medium">Nama Dokumen:</dt>
                <dd class="text-stone-900 font-medium text-sm mt-0.5">{{ $requirement->name }}</dd>
            </div>
            <div>
                <dt class="text-stone-500 font-medium">Sifat Persyaratan:</dt>
                <dd class="mt-0.5">
                    @if ($requirement->is_mandatory)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-rose-50 text-rose-800 border border-rose-300">
                            Wajib (Mandatori)
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-stone-100 text-stone-600 border border-stone-300">
                            Opsional
                        </span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-stone-500 font-medium">Status Penggunaan:</dt>
                <dd class="mt-0.5">
                    @if ($requirement->is_active)
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            Aktif Digunakan
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-stone-100 text-stone-600 border border-stone-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-stone-400"></span>
                            Nonaktif
                        </span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-stone-500 font-medium">Nomor Urut Tampilan:</dt>
                <dd class="text-stone-900 font-mono mt-0.5">{{ $requirement->sort_order }}</dd>
            </div>
            <div>
                <dt class="text-stone-500 font-medium">Total Digunakan pada Berkas:</dt>
                <dd class="text-stone-900 font-mono font-bold mt-0.5">{{ $requirement->checklists_count }} berkas verifikasi</dd>
            </div>
            <div>
                <dt class="text-stone-500 font-medium">Terakhir Diperbarui:</dt>
                <dd class="text-stone-700 font-mono mt-0.5">{{ $requirement->updated_at ? $requirement->updated_at->format('d/m/Y H:i') : '-' }}</dd>
            </div>
        </dl>
    </div>

</div>
@endsection
