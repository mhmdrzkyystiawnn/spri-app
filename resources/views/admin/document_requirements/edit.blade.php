@extends('layouts.admin')

@section('title', 'Ubah Persyaratan: ' . $requirement->code . ' - Meja Layanan SPRI')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Form -->
    <div class="pb-3 border-b border-stone-200">
        <span class="text-[11px] font-mono uppercase tracking-wider text-stone-500">MASTER TEMPLATE PERSYARATAN</span>
        <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 font-serif">
            Perbarui Persyaratan: {{ $requirement->code }}
        </h2>
        <p class="text-xs text-stone-600 mt-1">
            Ubah nama, status mandatori, urutan tampilan, atau status keaktifan dokumen.
        </p>
    </div>

    <!-- Card Form -->
    <div class="editorial-card p-6">
        <form method="POST" action="{{ route('admin.document-requirements.update', $requirement) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @include('admin.document_requirements.partials.form')

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-stone-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.document-requirements.index') }}" class="btn-secondary text-xs">
                    Batal
                </a>
                <button type="submit" class="btn-primary text-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
