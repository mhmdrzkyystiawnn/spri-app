@extends('layouts.admin')

@section('title', 'Detail Berkas: ' . $verification->registration_code)

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Dossier Badge -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-stone-200">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-mono font-semibold tracking-wider uppercase bg-stone-100 text-stone-700 rounded border border-stone-200 mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1B365D]"></span>
                    DOSSIER BERKAS PERMOHONAN
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-stone-900 font-mono flex items-center gap-2">
                    {{ $verification->registration_code }}
                </h2>
            </div>
            <span class="badge-status status-{{ $verification->status->value }} text-xs font-semibold px-3 py-1">
                {{ $verification->status->label() }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $verification)
            <a href="{{ route('admin.verifications.edit', $verification) }}" class="btn-primary text-xs">
                Edit Berkas & Status
            </a>
            @endcan
            @can('delete', $verification)
            <form action="{{ route('admin.verifications.destroy', $verification) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas verifikasi ini? Data akan di-soft delete.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger text-xs py-2">
                    Hapus Berkas
                </button>
            </form>
            @endcan
            <a href="{{ route('admin.verifications.index') }}" class="btn-secondary text-xs">
                &larr; Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- Kolom Kiri & Tengah: Rincian Berkas (2 Kolom) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Data Utama Permohonan (Dossier Identity Sheet) -->
            <div class="editorial-card overflow-hidden">
                <div class="editorial-header px-6 py-3 font-semibold text-xs text-stone-700 uppercase tracking-wider flex items-center justify-between">
                    <span>Informasi Pendaftaran Pemohon</span>
                    <span class="text-[11px] font-mono text-stone-500 font-normal">Identitas Formil SPRI</span>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-xs">
                        <div class="p-2.5 rounded bg-stone-50 border border-stone-200">
                            <dt class="text-stone-500 font-medium text-[11px] uppercase tracking-wider">Nomor Registrasi:</dt>
                            <dd class="text-stone-900 font-mono font-bold text-sm mt-0.5">{{ $verification->registration_code }}</dd>
                        </div>
                        <div class="p-2.5 rounded bg-stone-50 border border-stone-200">
                            <dt class="text-stone-500 font-medium text-[11px] uppercase tracking-wider">Nama Lengkap Pemohon:</dt>
                            <dd class="text-stone-900 font-bold text-sm mt-0.5">{{ $verification->applicant_name }}</dd>
                        </div>
                        <div class="p-2.5 rounded bg-stone-50 border border-stone-200">
                            <dt class="text-stone-500 font-medium text-[11px] uppercase tracking-wider">Jenis Dokumen SPRI:</dt>
                            <dd class="text-stone-900 font-medium mt-0.5">{{ $verification->document_type }}</dd>
                        </div>
                        <div class="p-2.5 rounded bg-stone-50 border border-stone-200">
                            <dt class="text-stone-500 font-medium text-[11px] uppercase tracking-wider">Petugas Pembuat:</dt>
                            <dd class="text-stone-900 font-medium mt-0.5">{{ $verification->creator?->name ?? 'Sistem' }}</dd>
                        </div>
                        <div class="p-2.5 rounded bg-stone-50 border border-stone-200">
                            <dt class="text-stone-500 font-medium text-[11px] uppercase tracking-wider">Waktu Didaftarkan:</dt>
                            <dd class="text-stone-700 font-mono mt-0.5">{{ $verification->created_at->format('d/m/Y H:i:s') }} WIB</dd>
                        </div>
                        <div class="p-2.5 rounded bg-stone-50 border border-stone-200">
                            <dt class="text-stone-500 font-medium text-[11px] uppercase tracking-wider">Waktu Verifikasi Selesai (Approved):</dt>
                            <dd class="text-stone-700 font-mono mt-0.5">
                                {{ $verification->verified_at ? $verification->verified_at->format('d/m/Y H:i:s') . ' WIB' : 'Belum Selesai' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Panel Rincian Formulir PERDIM 14 (Dossier Digital Pemohon Mandiri) -->
            @if ($verification->perdimApplication)
                @php $perdim = $verification->perdimApplication; @endphp
                <div class="editorial-card overflow-hidden border-[#1B365D]/30 shadow-xs" id="perdim-dossier-panel">
                    <div class="editorial-header px-6 py-3 bg-[#12243E] text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="font-bold text-xs uppercase tracking-wider text-amber-300 font-mono">
                                Rincian Formulir PERDIM 14 (Pendaftaran Mandiri)
                            </span>
                        </div>
                        <span class="text-[11px] font-mono text-slate-300">
                            Waktu Submit: {{ $perdim->declaration_accepted_at ? $perdim->declaration_accepted_at->format('d/m/Y H:i') . ' WIB' : $verification->created_at->format('d/m/Y H:i') . ' WIB' }}
                        </span>
                    </div>

                    <div class="p-6 space-y-6 text-xs text-stone-800">
                        <!-- Seksi 1: Data Permohonan & Identitas Diri Pemohon -->
                        <div class="border border-stone-200 rounded p-4 bg-stone-50/50 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 border-b border-stone-200 pb-1.5 flex items-center justify-between">
                                <span>Seksi 1: Data Permohonan &amp; Identitas Diri Pemohon</span>
                                <span class="text-[10px] text-stone-500 font-mono">Formulir Resmi PERDIM 14</span>
                            </h4>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Jenis Permohonan:</dt>
                                    <dd class="font-semibold text-stone-900 uppercase">{{ $perdim->application_type }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Spesifikasi Paspor:</dt>
                                    <dd class="font-semibold text-stone-900">{{ $perdim->passport_type }}</dd>
                                </div>
                                @if ($perdim->replacement_reason)
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Alasan Penggantian:</dt>
                                    <dd class="font-semibold text-amber-800">{{ $perdim->replacement_reason }}</dd>
                                </div>
                                @endif
                                @if ($perdim->change_reason)
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Alasan Perubahan:</dt>
                                    <dd class="font-semibold text-amber-800">{{ $perdim->change_reason }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Nama Lengkap (KTP):</dt>
                                    <dd class="font-bold text-stone-900 font-mono">{{ $perdim->full_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Nama Alias:</dt>
                                    <dd class="text-stone-700">{{ $perdim->alias_name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Jenis Kelamin / Tinggi:</dt>
                                    <dd class="font-medium text-stone-900">{{ $perdim->gender === 'L' ? 'Laki-laki (L)' : 'Perempuan (P)' }} / {{ $perdim->height_cm }} cm</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Tempat, Tanggal Lahir:</dt>
                                    <dd class="font-medium text-stone-900 font-mono">{{ $perdim->birth_place }}, {{ $perdim->birth_date?->format('d/m/Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">NIK (16 Digit):</dt>
                                    <dd class="font-bold text-stone-900 font-mono">{{ $perdim->nik ?? '-' }}</dd>
                                </div>
                                @if ($perdim->identity_number)
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">No. Identitas Lain / IC:</dt>
                                    <dd class="font-bold text-stone-900 font-mono">{{ $perdim->identity_number }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Tempat &amp; Tgl Terbit ID:</dt>
                                    <dd class="text-stone-800">{{ $perdim->identity_issued_place }}, {{ $perdim->identity_issued_date?->format('d/m/Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Masa Berlaku ID:</dt>
                                    <dd class="text-stone-800">{{ $perdim->identity_expiry_date?->format('d/m/Y') ?? 'Seumur Hidup' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Pekerjaan:</dt>
                                    <dd class="text-stone-800">{{ $perdim->occupation }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Email Pemohon:</dt>
                                    <dd class="text-stone-800 font-mono">{{ $perdim->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Telepon Indonesia:</dt>
                                    <dd class="font-bold text-stone-900 font-mono">{{ $perdim->indonesia_phone }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] text-stone-500 font-medium">Status Perkawinan:</dt>
                                    <dd class="font-semibold text-stone-900 uppercase">{{ $perdim->marital_status }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-[11px] text-stone-500 font-medium">Alamat di Indonesia:</dt>
                                    <dd class="text-stone-800 mt-0.5">{{ $perdim->indonesia_address }}</dd>
                                </div>
                                @if ($perdim->workplace_address)
                                <div class="sm:col-span-2">
                                    <dt class="text-[11px] text-stone-500 font-medium">Alamat Tempat Bekerja:</dt>
                                    <dd class="text-stone-800 mt-0.5">{{ $perdim->workplace_address }} (Telp: {{ $perdim->office_phone ?? '-' }})</dd>
                                </div>
                                @endif
                                @if ($perdim->overseas_address)
                                <div class="sm:col-span-2">
                                    <dt class="text-[11px] text-stone-500 font-medium">Alamat &amp; Telp di Luar Negeri:</dt>
                                    <dd class="text-stone-800 mt-0.5">{{ $perdim->overseas_address }} (Telp: {{ $perdim->overseas_phone ?? '-' }})</dd>
                                </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Seksi 2: Data Keluarga (Ibu, Ayah, Pasangan) -->
                        <div class="border border-stone-200 rounded p-4 bg-stone-50/50 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 border-b border-stone-200 pb-1.5">
                                Seksi 2: Data Keluarga Pemohon
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-3 bg-white rounded border border-stone-200">
                                    <strong class="text-stone-900 block text-xs mb-1">Ibu Kandung</strong>
                                    <p><span class="text-stone-500">Nama:</span> <strong class="font-mono text-stone-900">{{ $perdim->mother_name }}</strong></p>
                                    <p><span class="text-stone-500">Kewarganegaraan:</span> {{ $perdim->mother_nationality }}</p>
                                    <p><span class="text-stone-500">TTL:</span> {{ $perdim->mother_birth_place }}, {{ $perdim->mother_birth_date?->format('d/m/Y') }}</p>
                                </div>

                                <div class="p-3 bg-white rounded border border-stone-200">
                                    <strong class="text-stone-900 block text-xs mb-1">Ayah Kandung</strong>
                                    <p><span class="text-stone-500">Nama:</span> <strong class="font-mono text-stone-900">{{ $perdim->father_name }}</strong></p>
                                    <p><span class="text-stone-500">Kewarganegaraan:</span> {{ $perdim->father_nationality }}</p>
                                    <p><span class="text-stone-500">TTL:</span> {{ $perdim->father_birth_place }}, {{ $perdim->father_birth_date?->format('d/m/Y') }}</p>
                                    <p><span class="text-stone-500">Telepon:</span> <span class="font-mono">{{ $perdim->father_phone }}</span></p>
                                    <p class="mt-1 text-[11px] text-stone-600"><span class="text-stone-500">Alamat:</span> {{ $perdim->father_address }}</p>
                                </div>

                                @if ($perdim->marital_status === 'kawin' || $perdim->spouse_name)
                                <div class="p-3 bg-white rounded border border-stone-200 sm:col-span-2">
                                    <strong class="text-stone-900 block text-xs mb-1">Suami / Istri (Pasangan)</strong>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <p><span class="text-stone-500">Nama Pasangan:</span> <strong class="font-mono text-stone-900">{{ $perdim->spouse_name ?? '-' }}</strong></p>
                                        <p><span class="text-stone-500">Kewarganegaraan:</span> {{ $perdim->spouse_nationality ?? '-' }}</p>
                                        <p><span class="text-stone-500">TTL Pasangan:</span> {{ $perdim->spouse_birth_place ?? '-' }}, {{ $perdim->spouse_birth_date?->format('d/m/Y') ?? '-' }}</p>
                                        <p><span class="text-stone-500">Alamat Pasangan:</span> {{ $perdim->spouse_address ?? '-' }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Seksi 3: Rincian Perubahan & Kontak Darurat -->
                        <div class="border border-stone-200 rounded p-4 bg-stone-50/50 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-stone-900 border-b border-stone-200 pb-1.5">
                                Seksi 3: Rincian Perubahan &amp; Kontak Darurat
                            </h4>
                            @if ($perdim->change_details)
                            <div class="p-3 rounded bg-amber-50/60 border border-amber-200 text-amber-950">
                                <strong class="block text-xs font-semibold mb-1">Rincian Perubahan Data:</strong>
                                @if (is_array($perdim->change_details))
                                    <pre class="font-mono text-[11px] whitespace-pre-wrap">{{ json_encode($perdim->change_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    <p class="text-xs leading-relaxed">{{ $perdim->change_details }}</p>
                                @endif
                            </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="p-3 bg-white rounded border border-stone-200">
                                    <strong class="text-stone-900 block text-xs mb-1">Kontak Darurat di Indonesia</strong>
                                    <p><span class="text-stone-500">Nama:</span> <strong class="font-mono text-stone-900">{{ $perdim->emergency_id_name }}</strong></p>
                                    <p><span class="text-stone-500">Hubungan:</span> {{ $perdim->emergency_id_relationship }}</p>
                                    <p><span class="text-stone-500">Telepon:</span> <span class="font-mono font-bold">{{ $perdim->emergency_id_phone }}</span></p>
                                    <p class="mt-1 text-[11px] text-stone-600"><span class="text-stone-500">Alamat:</span> {{ $perdim->emergency_id_address }}</p>
                                </div>

                                @if ($perdim->emergency_overseas_name)
                                <div class="p-3 bg-white rounded border border-stone-200">
                                    <strong class="text-stone-900 block text-xs mb-1">Kontak Darurat di Luar Negeri</strong>
                                    <p><span class="text-stone-500">Nama:</span> <strong class="font-mono text-stone-900">{{ $perdim->emergency_overseas_name }}</strong></p>
                                    <p><span class="text-stone-500">Telepon:</span> <span class="font-mono">{{ $perdim->emergency_overseas_phone ?? '-' }}</span></p>
                                    <p class="mt-1 text-[11px] text-stone-600"><span class="text-stone-500">Alamat:</span> {{ $perdim->emergency_overseas_address ?? '-' }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Seksi 4: Pernyataan Integritas Pemohon -->
                        <div class="p-3.5 rounded bg-emerald-50/70 border border-emerald-200 text-emerald-950 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-700 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <strong class="text-xs font-bold text-emerald-900">Pernyataan Integritas Disetujui Pemohon</strong>
                                    <p class="text-[11px] text-emerald-800">Pemohon telah menyetujui pernyataan keabsahan dan pertanggungjawaban hukum data.</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono text-emerald-800 font-semibold shrink-0">
                                Disetujui: {{ $perdim->declaration_accepted_at ? $perdim->declaration_accepted_at->format('d/m/Y H:i:s') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Panel Dokumen Persyaratan Digital (Berkas Unggahan Pemohon) -->
                @php 
                    $documents = $perdim->documents;
                @endphp
                <div class="editorial-card overflow-hidden border-[#1B365D]/30 shadow-xs" id="digital-documents-panel">
                    <div class="editorial-header px-6 py-3.5 bg-[#1B365D] text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="font-bold text-xs uppercase tracking-wider text-white">
                                Dokumen Persyaratan Digital
                            </span>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if ($perdim->applicant_category === 'A')
                                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-blue-900 text-blue-100 border border-blue-400/50">
                                    {{ $perdim->applicant_category_label }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-amber-900 text-amber-100 border border-amber-400/50">
                                    {{ $perdim->applicant_category_label }}
                                </span>
                            @endif
                            <span class="text-[11px] font-mono text-slate-300">
                                {{ $documents->count() }} Berkas Terunggah
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        @if ($documents->isNotEmpty())
                            <div class="divide-y divide-stone-200 border border-stone-200 rounded-lg overflow-hidden">
                                @foreach ($documents as $doc)
                                    <div class="p-4 bg-white hover:bg-stone-50/70 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-start gap-3 overflow-hidden">
                                            <div class="p-2 rounded bg-stone-100 text-[#1B365D] shrink-0 border border-stone-200 mt-0.5">
                                                @if (str_contains(strtolower($doc->mime_type), 'pdf'))
                                                    <span class="font-mono text-xs font-bold text-rose-700">PDF</span>
                                                @else
                                                    <span class="font-mono text-xs font-bold text-blue-700">IMG</span>
                                                @endif
                                            </div>
                                            <div class="truncate">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-xs font-bold text-stone-900">
                                                        {{ $doc->document_type_label }}
                                                    </span>
                                                    <span class="text-[10px] font-mono uppercase bg-stone-100 text-stone-600 px-1.5 py-0.5 rounded border border-stone-200">
                                                        {{ $doc->document_type }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-stone-600 font-mono mt-0.5 truncate">
                                                    {{ $doc->original_name }}
                                                </p>
                                                <div class="flex items-center gap-3 text-[10px] text-stone-500 font-mono mt-1">
                                                    <span>Ukuran: <strong>{{ $doc->formatted_file_size }}</strong></span>
                                                    <span>&bull;</span>
                                                    <span>MIME: {{ $doc->mime_type }}</span>
                                                    @if ($doc->hash_sha256)
                                                        <span>&bull;</span>
                                                        <span title="SHA-256: {{ $doc->hash_sha256 }}" class="cursor-help">SHA: {{ substr($doc->hash_sha256, 0, 8) }}...</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <a href="{{ route('admin.verifications.documents.preview', [$verification, $doc]) }}" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-[#1B365D] hover:bg-[#12243E] text-white text-xs font-semibold shadow-xs transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Pratinjau Berkas</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-6 text-center text-xs text-stone-400 italic bg-stone-50 rounded border border-stone-200">
                                Belum ada berkas digital yang diunggah untuk permohonan ini.
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Penanganan Rekaman Warisan (Legacy Record Fallback) -->
                <div class="editorial-card p-5 border-stone-200 bg-stone-50/70 text-stone-600 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-stone-700 uppercase tracking-wide">
                        <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Permohonan Langsung / Legacy</span>
                    </div>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        Berkas ini didaftarkan langsung oleh petugas loket atau tidak memiliki formulir digital PERDIM 14.
                    </p>
                </div>
            @endif

            <!-- Catatan Publik vs Internal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Catatan Publik -->
                <div class="editorial-card p-5 border-amber-200 bg-amber-50/30">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-900">Catatan Publik</span>
                        <span class="text-[10px] bg-amber-200/80 text-amber-900 px-2 py-0.5 rounded font-mono">Tampil ke Pemohon</span>
                    </div>
                    @if ($verification->public_notes)
                        <p class="text-xs text-stone-800 leading-relaxed font-serif">
                            &ldquo;{{ $verification->public_notes }}&rdquo;
                        </p>
                    @else
                        <p class="text-xs text-stone-400 italic">Tidak ada catatan publik yang diterbitkan.</p>
                    @endif
                </div>

                <!-- Catatan Internal Admin -->
                <div class="editorial-card p-5 border-stone-300 bg-stone-100/70">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-stone-900">Catatan Internal Loket</span>
                        <span class="text-[10px] bg-red-100 text-red-800 border border-red-300 px-2 py-0.5 rounded font-mono font-bold">RAHASIA / INTERNAL</span>
                    </div>
                    @if ($verification->admin_notes)
                        <p class="text-xs text-stone-800 font-mono leading-relaxed whitespace-pre-wrap">
{{ $verification->admin_notes }}
                        </p>
                    @else
                        <p class="text-xs text-stone-400 italic">Tidak ada catatan internal.</p>
                    @endif
                </div>
            </div>

            <!-- Checklist Kelengkapan Dokumen Fisik -->
            <div class="editorial-card overflow-hidden">
                <div class="editorial-header px-6 py-3 flex items-center justify-between">
                    <div>
                        <span class="font-semibold text-xs text-stone-700 uppercase tracking-wider block">
                            Checklist Kelengkapan Dokumen Fisik
                        </span>
                        <span class="text-[11px] text-stone-500 font-normal">
                            Pemeriksaan berkas persyaratan fisik pemohon di meja layanan.
                        </span>
                    </div>
                    @if (isset($completionStats))
                        <div>
                            @if ($completionStats['is_complete'])
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                                    <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                                    Lengkap ({{ $completionStats['checked'] }}/{{ $completionStats['total'] }})
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                    <span class="h-2 w-2 rounded-full bg-amber-600"></span>
                                    Belum Lengkap ({{ $completionStats['checked'] }}/{{ $completionStats['total'] }})
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    @can('updateForVerification', [App\Models\VerificationChecklist::class, $verification])
                        <form method="POST" action="{{ route('admin.verifications.checklist.update', $verification) }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div class="divide-y divide-stone-200 border border-stone-200 rounded">
                                @forelse ($verification->checklists as $item)
                                    <div class="p-4 bg-white hover:bg-stone-50/70 transition-colors {{ $item->is_checked ? 'bg-emerald-50/20' : '' }}">
                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                            <div class="flex items-start gap-3">
                                                <input type="hidden" name="items[{{ $item->id }}][is_checked]" value="0">
                                                <input 
                                                    type="checkbox" 
                                                    id="checklist_{{ $item->id }}" 
                                                    name="items[{{ $item->id }}][is_checked]" 
                                                    value="1" 
                                                    {{ $item->is_checked ? 'checked' : '' }}
                                                    class="rounded border-stone-300 text-stone-900 focus:ring-stone-900 h-4 w-4 mt-1"
                                                >
                                                <div>
                                                    <label for="checklist_{{ $item->id }}" class="font-medium text-xs text-stone-900 cursor-pointer block">
                                                        {{ $item->requirement?->name ?? 'Dokumen #' . $item->document_requirement_id }}
                                                    </label>
                                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                                        <span class="font-mono text-[10px] text-stone-600 bg-stone-100 px-1.5 py-0.2 rounded border border-stone-200">
                                                            {{ $item->requirement?->code ?? '-' }}
                                                        </span>
                                                        @if ($item->requirement?->is_mandatory)
                                                             <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200">
                                                                Wajib
                                                            </span>
                                                        @else
                                                            <span class="text-[10px] text-stone-500 bg-stone-100 px-1.5 py-0.2 rounded">
                                                                Opsional
                                                            </span>
                                                        @endif

                                                        @if ($item->is_checked && $item->checked_at)
                                                            <span class="text-[10px] text-emerald-700 font-mono">
                                                                &check; Diverifikasi oleh {{ $item->checker?->name ?? 'Petugas' }} ({{ $item->checked_at->format('d/m/Y H:i') }})
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="sm:w-1/3">
                                                <input 
                                                    type="text" 
                                                    name="items[{{ $item->id }}][notes]" 
                                                    value="{{ old('items.' . $item->id . '.notes', $item->notes) }}" 
                                                    placeholder="Catatan fisik (misal: legalisir/buram)..."
                                                    class="editorial-input w-full text-xs"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-xs text-stone-400 italic">
                                        Belum ada persyaratan dokumen yang terdaftar untuk jenis permohonan ini.
                                    </div>
                                @endforelse
                            </div>

                            @if ($verification->checklists->isNotEmpty())
                                <div class="flex items-center justify-between pt-2">
                                    <p class="text-[11px] text-stone-500">
                                        Perubahan checklist akan dicatat pada jejak audit sistem.
                                    </p>
                                    <button type="submit" class="btn-primary text-xs py-2">
                                        Simpan Perubahan Checklist
                                    </button>
                                </div>
                            @endif
                        </form>
                    @else
                        <!-- Mode Read-Only untuk Pimpinan / Tanpa Hak Mutasi -->
                        <div class="divide-y divide-stone-200 border border-stone-200 rounded">
                            @forelse ($verification->checklists as $item)
                                <div class="p-4 bg-white flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5">
                                            @if ($item->is_checked)
                                                <span class="inline-flex items-center justify-center h-4 w-4 rounded bg-emerald-600 text-white text-[10px] font-bold">&check;</span>
                                            @else
                                                <span class="inline-flex items-center justify-center h-4 w-4 rounded bg-stone-200 text-stone-600 text-[10px] font-bold">&cross;</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-medium text-xs text-stone-900 block">
                                                {{ $item->requirement?->name ?? 'Dokumen #' . $item->document_requirement_id }}
                                            </span>
                                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                                <span class="font-mono text-[10px] text-stone-600 bg-stone-100 px-1.5 py-0.2 rounded border border-stone-200">
                                                    {{ $item->requirement?->code ?? '-' }}
                                                </span>
                                                @if ($item->requirement?->is_mandatory)
                                                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200">
                                                        Wajib
                                                    </span>
                                                @else
                                                    <span class="text-[10px] text-stone-500 bg-stone-100 px-1.5 py-0.2 rounded">
                                                        Opsional
                                                    </span>
                                                @endif

                                                @if ($item->is_checked && $item->checked_at)
                                                    <span class="text-[10px] text-emerald-700 font-mono">
                                                        Diverifikasi oleh {{ $item->checker?->name ?? 'Petugas' }} ({{ $item->checked_at->format('d/m/Y H:i') }})
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if ($item->notes)
                                        <div class="sm:w-1/3 text-xs text-stone-600 italic bg-stone-50 p-2 rounded border border-stone-200">
                                            Catatan: &ldquo;{{ $item->notes }}&rdquo;
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="p-6 text-center text-xs text-stone-400 italic">
                                    Belum ada data checklist untuk berkas ini.
                                </div>
                            @endforelse
                        </div>
                        <p class="text-[11px] text-stone-500 mt-3 italic">
                            Mode pemantauan (Read-Only). Hanya Verifikator dan Administrator yang dapat memperbarui checklist fisik.
                        </p>
                    @endcan
                </div>
            </div>

            <!-- Kronologi Riwayat Status (Status Timeline / Ledger) -->
            <div class="editorial-card overflow-hidden">
                <div class="editorial-header px-6 py-3 flex items-center justify-between">
                    <span class="font-semibold text-xs text-stone-700 uppercase tracking-wider">
                        Kronologi Riwayat Status (Status Timeline)
                    </span>
                    <span class="text-[11px] font-mono text-stone-500">
                        {{ $verification->statusHistories->count() }} Entri
                    </span>
                </div>
                <div class="p-6">
                    <div class="relative border-l-2 border-stone-300 ml-4 space-y-6">
                        @forelse ($verification->statusHistories as $history)
                            <div class="relative pl-6">
                                <!-- Marker Bulat -->
                                <span class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full bg-white border-2 border-[#1B365D]"></span>

                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($history->from_status)
                                            <span class="badge-status status-{{ $history->from_status->value }} text-[11px] py-0.5 px-2">
                                                {{ $history->from_status->label() }}
                                            </span>
                                            <span class="text-stone-400 font-bold">&rarr;</span>
                                        @else
                                            <span class="text-[10px] font-mono uppercase bg-stone-100 text-stone-600 px-1.5 py-0.5 rounded border border-stone-200">
                                                Status Awal
                                            </span>
                                        @endif
                                        <span class="badge-status status-{{ $history->to_status->value }} text-xs font-semibold py-0.5 px-2.5">
                                            {{ $history->to_status->label() }}
                                        </span>
                                        <span class="text-[11px] text-stone-500 font-mono ml-auto">
                                            {{ $history->changed_at->format('d/m/Y H:i') }} WIB
                                        </span>
                                    </div>

                                    <p class="text-xs text-stone-600">
                                        Petugas: <strong class="text-stone-800">{{ $history->changer?->name ?? 'Sistem / Loket' }}</strong>
                                    </p>

                                    @if ($history->note)
                                        <div class="text-xs text-stone-700 bg-stone-50 p-2.5 rounded border border-stone-200 font-serif leading-relaxed">
                                            &ldquo;{{ $history->note }}&rdquo;
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-stone-400 italic pl-6">Belum ada catatan riwayat status.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Audit Trail / Jejak Mutasi (1 Kolom) -->
        <div class="lg:col-span-1">
            <div class="editorial-card overflow-hidden">
                <div class="editorial-header px-4 py-3 flex items-center justify-between">
                    <span class="font-semibold text-xs text-stone-700 uppercase tracking-wider">Audit Log Mutasi</span>
                    <span class="text-[11px] font-mono text-stone-500">{{ $verification->auditLogs->count() }} Entri</span>
                </div>
                <div class="p-4 max-h-[600px] overflow-y-auto space-y-3">
                    @forelse ($verification->auditLogs as $log)
                        <div class="text-xs border-l-2 border-stone-400 pl-3 py-1 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-stone-900 uppercase font-mono text-[11px]">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                                <span class="text-[10px] text-stone-500 font-mono">
                                    {{ $log->created_at->format('d/m/y H:i') }}
                                </span>
                            </div>
                            <p class="text-stone-600 text-[11px]">
                                Petugas: <strong>{{ $log->user?->name ?? 'Sistem' }}</strong>
                            </p>
                            @if ($log->ip_address)
                                <p class="text-stone-400 text-[10px] font-mono">
                                    IP: {{ $log->ip_address }}
                                </p>
                            @endif

                            @if (!empty($log->before) || !empty($log->after))
                                <details class="text-[11px] text-stone-600 mt-1 cursor-pointer">
                                    <summary class="text-stone-500 hover:text-stone-800 font-medium">Lihat rincian perubahan</summary>
                                    <div class="mt-1 p-2 bg-stone-50 rounded border border-stone-200 space-y-1 font-mono text-[10px]">
                                        @if (!empty($log->before))
                                            <div>
                                                <span class="text-rose-700 block font-bold">Sebelum:</span>
                                                <pre class="overflow-x-auto text-stone-600 whitespace-pre-wrap">{{ json_encode($log->before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        @endif
                                        @if (!empty($log->after))
                                            <div class="mt-1">
                                                <span class="text-emerald-700 block font-bold">Sesudah:</span>
                                                <pre class="overflow-x-auto text-stone-600 whitespace-pre-wrap">{{ json_encode($log->after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-stone-400 italic">Belum ada catatan mutasi.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
