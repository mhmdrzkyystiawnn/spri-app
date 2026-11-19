@extends('layouts.admin')

@section('title', 'Tambah Master Persyaratan Dokumen - Meja Layanan SPRI')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Form -->
    <div class="pb-3 border-b border-stone-200">
        <span class="text-[11px] font-mono uppercase tracking-wider text-stone-500">MASTER TEMPLATE PERSYARATAN</span>
        <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 font-serif">
            Tambah Persyaratan Dokumen Baru
        </h2>
        <p class="text-xs text-stone-600 mt-1">
            Daftarkan dokumen fisik baru yang wajib atau opsional dipenuhi untuk jenis permohonan paspor tertentu.
        </p>
    </div>

    <!-- Card Form -->
    <div class="editorial-card p-6">
        <form method="POST" action="{{ route('admin.document-requirements.store') }}" class="space-y-5">
            @csrf

            @include('admin.document_requirements.partials.form')

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-stone-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.document-requirements.index') }}" class="btn-secondary text-xs">
                    Batal
                </a>
                <button type="submit" class="btn-primary text-xs">
                    Simpan Persyaratan Dokumen
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
