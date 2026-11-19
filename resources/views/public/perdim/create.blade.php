@extends('layouts.app')

@section('title', 'Pendaftaran Permohonan Paspor RI (Formulir PERDIM 14) - Ditjen Imigrasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Formulir Resmi -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-[#12243E] text-amber-300 border border-amber-400/40 text-[11px] font-mono font-semibold tracking-wider">
                    <span>Formulir Resmi PERDIM 14</span> &bull; <span>DOKUMEN RESMI NEGARA</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-serif mt-2 tracking-tight">
                    Permohonan Surat Perjalanan Republik Indonesia
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Pengisian mandiri data permohonan paspor baru, penggantian, atau perubahan data sesuai Formulir PERDIM 14.
                </p>
            </div>
            <div class="text-right shrink-0">
                <a href="{{ route('status.show') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 underline decoration-slate-300">
                    &larr; Cek Status Permohonan
                </a>
            </div>
        </div>

        <!-- Alert Global Galat Validasi Server -->
        @if ($errors->any())
            <div class="mt-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-900" role="alert" id="server-error-banner">
                <div class="flex items-center gap-2 font-bold text-xs uppercase tracking-wide text-rose-800">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>Terdapat Kesalahan Pengisian Formulir ({{ $errors->count() }} Bidang Memerlukan Perhatian):</span>
                </div>
                <ul class="mt-2 list-disc list-inside text-xs space-y-1 text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Visual Stepper Progress Bar (6 Tahap) -->
        <nav aria-label="Progress Pengisian Formulir" class="mt-8 mb-8">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-1.5 sm:gap-2">
                <!-- Step 1 Indicator -->
                <button type="button" onclick="goToStep(1)" id="step-nav-1" class="step-nav-btn text-left p-2 sm:p-2.5 rounded border border-slate-200 transition-all">
                    <span class="block text-[10px] font-mono font-semibold uppercase tracking-wider text-slate-400 step-num">Langkah 1</span>
                    <span class="block text-xs font-bold text-slate-800 truncate step-title">Jenis SPRI</span>
                </button>

                <!-- Step 2 Indicator -->
                <button type="button" onclick="goToStep(2)" id="step-nav-2" class="step-nav-btn text-left p-2 sm:p-2.5 rounded border border-slate-200 transition-all opacity-70">
                    <span class="block text-[10px] font-mono font-semibold uppercase tracking-wider text-slate-400 step-num">Langkah 2</span>
                    <span class="block text-xs font-bold text-slate-800 truncate step-title">Identitas</span>
                </button>

                <!-- Step 3 Indicator -->
                <button type="button" onclick="goToStep(3)" id="step-nav-3" class="step-nav-btn text-left p-2 sm:p-2.5 rounded border border-slate-200 transition-all opacity-70">
                    <span class="block text-[10px] font-mono font-semibold uppercase tracking-wider text-slate-400 step-num">Langkah 3</span>
                    <span class="block text-xs font-bold text-slate-800 truncate step-title">Keluarga</span>
                </button>

                <!-- Step 4 Indicator -->
                <button type="button" onclick="goToStep(4)" id="step-nav-4" class="step-nav-btn text-left p-2 sm:p-2.5 rounded border border-slate-200 transition-all opacity-70">
                    <span class="block text-[10px] font-mono font-semibold uppercase tracking-wider text-slate-400 step-num">Langkah 4</span>
                    <span class="block text-xs font-bold text-slate-800 truncate step-title">Darurat</span>
                </button>

                <!-- Step 5 Indicator: Dokumen Persyaratan Digital -->
                <button type="button" onclick="goToStep(5)" id="step-nav-5" class="step-nav-btn text-left p-2 sm:p-2.5 rounded border border-slate-200 transition-all opacity-70">
                    <span class="block text-[10px] font-mono font-semibold uppercase tracking-wider text-slate-400 step-num">Langkah 5</span>
                    <span class="block text-xs font-bold text-slate-800 truncate step-title">Dokumen</span>
                </button>

                <!-- Step 6 Indicator: Pratinjau & Deklarasi -->
                <button type="button" onclick="goToStep(6)" id="step-nav-6" class="step-nav-btn text-left p-2 sm:p-2.5 rounded border border-slate-200 transition-all opacity-70">
                    <span class="block text-[10px] font-mono font-semibold uppercase tracking-wider text-slate-400 step-num">Langkah 6</span>
                    <span class="block text-xs font-bold text-slate-800 truncate step-title">Pratinjau</span>
                </button>
            </div>
            
            <!-- Progress Line Bar -->
            <div class="w-full bg-slate-200 h-1.5 rounded-full mt-3 overflow-hidden">
                <div id="progress-bar-fill" class="bg-[#1B365D] h-full transition-all duration-300" style="width: 16.666%;"></div>
            </div>
        </nav>

        <!-- Formulir PERDIM 14 -->
        <form action="{{ route('public.perdim.store') }}" method="POST" id="perdim-wizard-form" novalidate>
            @csrf

            <!-- ============================================================== -->
            <!-- LANGKAH 1: Jenis Permohonan & SPRI -->
            <!-- ============================================================== -->
            <div id="step-pane-1" class="wizard-pane space-y-6">
                <div class="border-b border-slate-200 pb-3">
                    <span class="text-[11px] font-mono font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        Bagian A: Seksi 1: Jenis Permohonan &amp; SPRI
                    </span>
                    <h2 class="text-lg font-bold text-slate-900 mt-1">Jenis Permohonan &amp; Spesifikasi Paspor</h2>
                    <p class="text-xs text-slate-600">Pilih jenis permohonan paspor yang diajukan beserta alasannya.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="application_type" class="block text-xs font-semibold text-slate-800 mb-1">
                            Jenis Permohonan <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <select name="application_type" id="application_type" required onchange="handleApplicationTypeChange()" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            <option value="">-- Pilih Jenis Permohonan --</option>
                            <option value="baru" {{ old('application_type', 'baru') === 'baru' ? 'selected' : '' }}>Permohonan Baru</option>
                            <option value="penggantian" {{ old('application_type') === 'penggantian' ? 'selected' : '' }}>Penggantian Paspor</option>
                            <option value="perubahan" {{ old('application_type') === 'perubahan' ? 'selected' : '' }}>Perubahan Data Paspor</option>
                        </select>
                        @error('application_type')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="passport_type" class="block text-xs font-semibold text-slate-800 mb-1">
                            Jenis Paspor yang Diminta <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <select name="passport_type" id="passport_type" required class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            <option value="paspor_biasa_48" {{ old('passport_type', 'paspor_biasa_48') === 'paspor_biasa_48' ? 'selected' : '' }}>Paspor Biasa 48 Halaman</option>
                            <option value="paspor_elektronik_48" {{ old('passport_type') === 'paspor_elektronik_48' ? 'selected' : '' }}>Paspor Elektronik 48 Halaman</option>
                            <option value="elektronik_lembar_polikarbonat" {{ old('passport_type') === 'elektronik_lembar_polikarbonat' ? 'selected' : '' }}>Paspor Elektronik Lembar Polikarbonat</option>
                            <option value="splp" {{ old('passport_type') === 'splp' ? 'selected' : '' }}>Surat Perjalanan Laksana Paspor (SPLP)</option>
                        </select>
                        @error('passport_type')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alasan Penggantian (Kondisional: Muncul jika penggantian) -->
                    <div id="replacement-reason-container" class="{{ old('application_type') === 'penggantian' ? '' : 'hidden' }}">
                        <label for="replacement_reason" class="block text-xs font-semibold text-slate-800 mb-1">
                            Alasan Penggantian Paspor <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <select name="replacement_reason" id="replacement_reason" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            <option value="">-- Pilih Alasan Penggantian --</option>
                            <option value="habis_masa_berlaku" {{ old('replacement_reason') === 'habis_masa_berlaku' ? 'selected' : '' }}>Habis Masa Berlaku</option>
                            <option value="halaman_penuh" {{ old('replacement_reason') === 'halaman_penuh' ? 'selected' : '' }}>Halaman Penuh</option>
                            <option value="hilang" {{ old('replacement_reason') === 'hilang' ? 'selected' : '' }}>Hilang (Wajib Surat Kehilangan Polisi)</option>
                            <option value="rusak" {{ old('replacement_reason') === 'rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="lainnya" {{ old('replacement_reason') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('replacement_reason')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alasan Perubahan (Kondisional: Muncul jika perubahan) -->
                    <div id="change-reason-container" class="{{ old('application_type') === 'perubahan' ? '' : 'hidden' }}">
                        <label for="change_reason" class="block text-xs font-semibold text-slate-800 mb-1">
                            Alasan Perubahan Data <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="text" name="change_reason" id="change_reason" value="{{ old('change_reason') }}" maxlength="80" placeholder="Contoh: Perubahan Nama Berdasarkan Penetapan Pengadilan" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        @error('change_reason')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="p-3.5 rounded bg-blue-50/60 border border-blue-200 text-xs text-blue-900 leading-relaxed">
                    <strong>Catatan Loket:</strong> Untuk permohonan penggantian paspor hilang atau rusak, pemohon wajib melampirkan Surat Tanda Lapor Kehilangan dari Kepolisian RI dan paspor lama yang rusak saat verifikasi fisik.
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- LANGKAH 2: Identitas Diri Pemohon & Alamat -->
            <!-- ============================================================== -->
            <div id="step-pane-2" class="wizard-pane space-y-6 hidden">
                <div class="border-b border-slate-200 pb-3">
                    <span class="text-[11px] font-mono font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        Bagian B: Seksi 2
                    </span>
                    <h2 class="text-lg font-bold text-slate-900 mt-1">Identitas Diri Pemohon &amp; Alamat Tinggal</h2>
                    <p class="text-xs text-slate-600">Pastikan ejaan nama dan tempat tanggal lahir sesuai dengan ejaan pada e-KTP dan Akta Kelahiran.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="full_name" class="block text-xs font-semibold text-slate-800 mb-1">
                            Nama Lengkap Sesuai KTP <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required maxlength="255" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        @error('full_name') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="alias_name" class="block text-xs font-semibold text-slate-800 mb-1">
                            Nama Lain / Alias <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" name="alias_name" id="alias_name" value="{{ old('alias_name') }}" maxlength="255" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                    </div>

                    <div>
                        <label for="gender" class="block text-xs font-semibold text-slate-800 mb-1">
                            Jenis Kelamin <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <select name="gender" id="gender" required class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                            <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                        </select>
                        @error('gender') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="height_cm" class="block text-xs font-semibold text-slate-800 mb-1">
                            Tinggi Badan (cm) <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="number" name="height_cm" id="height_cm" value="{{ old('height_cm') }}" min="30" max="250" required placeholder="Contoh: 170" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        @error('height_cm') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="birth_place" class="block text-xs font-semibold text-slate-800 mb-1">
                            Tempat Lahir <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="text" name="birth_place" id="birth_place" value="{{ old('birth_place') }}" required maxlength="100" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        @error('birth_place') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="birth_date" class="block text-xs font-semibold text-slate-800 mb-1">
                            Tanggal Lahir <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" required max="{{ date('Y-m-d') }}" onchange="updateCategoryReactivity()" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        @error('birth_date') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Bagian NIK dan Nomor Identitas Lainnya -->
                    <div class="md:col-span-2 p-3.5 rounded bg-slate-50 border border-slate-200 space-y-3">
                        <span class="text-xs font-bold text-slate-800 block">Identitas Kependudukan (Wajib Terisi Salah Satu)</span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="nik" class="block text-[11px] font-semibold text-slate-700 mb-1">
                                    Nomor Induk Kependudukan (NIK - 16 Digit)
                                </label>
                                <input type="text" name="nik" id="nik" value="{{ old('nik') }}" maxlength="16" placeholder="317101xxxxxxxxxx" class="w-full font-mono text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                                @error('nik') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="identity_number" class="block text-[11px] font-semibold text-slate-700 mb-1">
                                    Nomor Identitas Lain / IC / Paspor Lama <span class="text-slate-400 font-normal">(Jika NIK belum ada)</span>
                                </label>
                                <input type="text" name="identity_number" id="identity_number" value="{{ old('identity_number') }}" maxlength="50" placeholder="Nomor IC / Dokumen Asing" class="w-full font-mono text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                                @error('identity_number') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="identity_issued_place" class="block text-xs font-semibold text-slate-800 mb-1">
                            Tempat Dikeluarkan Identitas <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="text" name="identity_issued_place" id="identity_issued_place" value="{{ old('identity_issued_place') }}" required maxlength="100" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        @error('identity_issued_place') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="identity_issued_date" class="block text-xs font-semibold text-slate-800 mb-1">
                            Tanggal Dikeluarkan Identitas <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="date" name="identity_issued_date" id="identity_issued_date" value="{{ old('identity_issued_date') }}" required class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        @error('identity_issued_date') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="identity_expiry_date" class="block text-xs font-semibold text-slate-800 mb-1">
                            Masa Berlaku Identitas <span class="text-slate-400 font-normal">(Kosongkan jika Seumur Hidup)</span>
                        </label>
                        <input type="date" name="identity_expiry_date" id="identity_expiry_date" value="{{ old('identity_expiry_date') }}" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                    </div>

                    <div>
                        <label for="occupation" class="block text-xs font-semibold text-slate-800 mb-1">
                            Pekerjaan <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="text" name="occupation" id="occupation" value="{{ old('occupation') }}" required maxlength="100" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        @error('occupation') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-800 mb-1">
                            Alamat Email Aktif <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required maxlength="150" placeholder="nama@email.com" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        @error('email') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="marital_status" class="block text-xs font-semibold text-slate-800 mb-1">
                            Status Perkawinan <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <select name="marital_status" id="marital_status" required onchange="handleMaritalStatusChange(); updateCategoryReactivity();" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            <option value="">-- Pilih Status --</option>
                            <option value="kawin" {{ old('marital_status', 'kawin') === 'kawin' ? 'selected' : '' }}>Kawin</option>
                            <option value="tidak_kawin" {{ in_array(old('marital_status'), ['tidak_kawin', 'belum_kawin'], true) ? 'selected' : '' }}>Belum Kawin / Tidak Kawin</option>
                            <option value="cerai_hidup" {{ old('marital_status') === 'cerai_hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                            <option value="cerai_mati" {{ old('marital_status') === 'cerai_mati' ? 'selected' : '' }}>Cerai Mati</option>
                        </select>
                        @error('marital_status') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="indonesia_address" class="block text-xs font-semibold text-slate-800 mb-1">
                            Alamat Lengkap di Indonesia <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <textarea name="indonesia_address" id="indonesia_address" rows="2" required class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">{{ old('indonesia_address') }}</textarea>
                        @error('indonesia_address') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="indonesia_phone" class="block text-xs font-semibold text-slate-800 mb-1">
                            Nomor Telepon/HP di Indonesia <span class="text-rose-600 font-bold">*</span>
                        </label>
                        <input type="text" name="indonesia_phone" id="indonesia_phone" value="{{ old('indonesia_phone') }}" required maxlength="30" placeholder="08xxxxxxxxxx" class="w-full font-mono text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        @error('indonesia_phone') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="office_phone" class="block text-xs font-semibold text-slate-800 mb-1">
                            Nomor Telepon Kantor <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" name="office_phone" id="office_phone" value="{{ old('office_phone') }}" maxlength="30" class="w-full font-mono text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                    </div>

                    <div class="md:col-span-2">
                        <label for="workplace_address" class="block text-xs font-semibold text-slate-800 mb-1">
                            Alamat Tempat Bekerja <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea name="workplace_address" id="workplace_address" rows="2" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">{{ old('workplace_address') }}</textarea>
                    </div>

                    <div>
                        <label for="overseas_address" class="block text-xs font-semibold text-slate-800 mb-1">
                            Alamat di Luar Negeri <span class="text-slate-400 font-normal">(Khusus WNI Domisili LN)</span>
                        </label>
                        <textarea name="overseas_address" id="overseas_address" rows="2" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">{{ old('overseas_address') }}</textarea>
                    </div>

                    <div>
                        <label for="overseas_phone" class="block text-xs font-semibold text-slate-800 mb-1">
                            Nomor Telepon di Luar Negeri <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" name="overseas_phone" id="overseas_phone" value="{{ old('overseas_phone') }}" maxlength="30" class="w-full font-mono text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- LANGKAH 3: Data Keluarga -->
            <!-- ============================================================== -->
            <div id="step-pane-3" class="wizard-pane space-y-6 hidden">
                <div class="border-b border-slate-200 pb-3">
                    <span class="text-[11px] font-mono font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        Bagian C: Seksi 3
                    </span>
                    <h2 class="text-lg font-bold text-slate-900 mt-1">Data Keluarga (Orang Tua &amp; Pasangan)</h2>
                    <p class="text-xs text-slate-600">Lengkapi data orang tua kandung dan pasangan sesuai ketentuan Formulir PERDIM 14.</p>
                </div>

                <!-- Banner Informasi Khusus Pemohon Anak -->
                <div id="step3-child-notice" class="p-3.5 rounded bg-amber-50 border border-amber-200 text-amber-900 text-xs hidden">
                    <strong>Penting untuk Pemohon Anak-Anak:</strong> Data orang tua kandung di bawah ini wajib diisi lengkap karena orang tua berperan sebagai penanggung jawab hukum permohonan paspor anak.
                </div>

                <!-- Data Ibu Kandung -->
                <div class="border border-slate-200 rounded p-4 bg-slate-50/50 space-y-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">1. Data Ibu Kandung</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="mother_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Ibu <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="mother_name" id="mother_name" value="{{ old('mother_name') }}" required maxlength="150" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('mother_name') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="mother_nationality" class="block text-xs font-semibold text-slate-700 mb-1">Kewarganegaraan Ibu <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="mother_nationality" id="mother_nationality" value="{{ old('mother_nationality', 'WNI') }}" required maxlength="50" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('mother_nationality') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="mother_birth_place" class="block text-xs font-semibold text-slate-700 mb-1">Tempat Lahir Ibu <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="mother_birth_place" id="mother_birth_place" value="{{ old('mother_birth_place') }}" required maxlength="100" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('mother_birth_place') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="mother_birth_date" class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lahir Ibu <span class="text-rose-600 font-bold">*</span></label>
                            <input type="date" name="mother_birth_date" id="mother_birth_date" value="{{ old('mother_birth_date') }}" required class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('mother_birth_date') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Data Ayah Kandung -->
                <div class="border border-slate-200 rounded p-4 bg-slate-50/50 space-y-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">2. Data Ayah Kandung</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="father_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Ayah <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="father_name" id="father_name" value="{{ old('father_name') }}" required maxlength="150" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('father_name') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="father_nationality" class="block text-xs font-semibold text-slate-700 mb-1">Kewarganegaraan Ayah <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="father_nationality" id="father_nationality" value="{{ old('father_nationality', 'WNI') }}" required maxlength="50" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('father_nationality') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="father_birth_place" class="block text-xs font-semibold text-slate-700 mb-1">Tempat Lahir Ayah <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="father_birth_place" id="father_birth_place" value="{{ old('father_birth_place') }}" required maxlength="100" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('father_birth_place') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="father_birth_date" class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lahir Ayah <span class="text-rose-600 font-bold">*</span></label>
                            <input type="date" name="father_birth_date" id="father_birth_date" value="{{ old('father_birth_date') }}" required class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('father_birth_date') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="father_address" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Ayah Kandung <span class="text-rose-600 font-bold">*</span></label>
                            <textarea name="father_address" id="father_address" rows="2" required class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">{{ old('father_address') }}</textarea>
                            @error('father_address') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="father_phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon/HP Ayah <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="father_phone" id="father_phone" value="{{ old('father_phone') }}" required maxlength="30" class="w-full font-mono text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('father_phone') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Data Suami / Istri (Kondisional: Ditampilkan jika status Kawin & Dewasa) -->
                <div id="spouse-section-container" class="border border-slate-200 rounded p-4 bg-slate-50/50 space-y-3 {{ old('marital_status', 'kawin') === 'kawin' ? '' : 'hidden' }}">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">3. Data Suami / Istri</h3>
                        <span class="text-[10px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Wajib Diisi untuk Status Kawin</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="spouse_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Pasangan <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="spouse_name" id="spouse_name" value="{{ old('spouse_name') }}" maxlength="150" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('spouse_name') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="spouse_nationality" class="block text-xs font-semibold text-slate-700 mb-1">Kewarganegaraan Pasangan <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="spouse_nationality" id="spouse_nationality" value="{{ old('spouse_nationality', 'WNI') }}" maxlength="50" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('spouse_nationality') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="spouse_birth_place" class="block text-xs font-semibold text-slate-700 mb-1">Tempat Lahir Pasangan</label>
                            <input type="text" name="spouse_birth_place" id="spouse_birth_place" value="{{ old('spouse_birth_place') }}" maxlength="100" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        </div>

                        <div>
                            <label for="spouse_birth_date" class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lahir Pasangan</label>
                            <input type="date" name="spouse_birth_date" id="spouse_birth_date" value="{{ old('spouse_birth_date') }}" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        </div>

                        <div class="md:col-span-2">
                            <label for="spouse_address" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Tinggal Pasangan</label>
                            <textarea name="spouse_address" id="spouse_address" rows="2" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">{{ old('spouse_address') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- LANGKAH 4: Rincian Perubahan & Kontak Darurat -->
            <!-- ============================================================== -->
            <div id="step-pane-4" class="wizard-pane space-y-6 hidden">
                <div class="border-b border-slate-200 pb-3">
                    <span class="text-[11px] font-mono font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        Bagian D: Seksi 4
                    </span>
                    <h2 class="text-lg font-bold text-slate-900 mt-1">Rincian Perubahan &amp; Kontak Darurat</h2>
                    <p class="text-xs text-slate-600">Kontak yang dapat dihubungi saat keadaan darurat di Indonesia serta rincian perubahan data paspor jika ada.</p>
                </div>

                <!-- Rincian Perubahan Data (Kondisional: Muncul jika memilih jenis perubahan) -->
                <div id="change-details-section" class="border border-slate-200 rounded p-4 bg-slate-50/50 space-y-2 {{ old('application_type') === 'perubahan' ? '' : 'hidden' }}">
                    <label for="change_details_text" class="block text-xs font-bold text-slate-800 uppercase tracking-wide">
                        Rincian Perubahan Data (Data Lama vs Data Baru)
                    </label>
                    <textarea name="change_details" id="change_details_text" rows="3" placeholder="Contoh: Nama lama 'Budi' diubah menjadi 'Budi Santoso' sesuai Penetapan Pengadilan Negeri Jakarta No. 123/Pdt.P/2026." class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">{{ old('change_details') }}</textarea>
                </div>

                <!-- Kontak Darurat di Indonesia -->
                <div class="border border-slate-200 rounded p-4 bg-slate-50/50 space-y-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">1. Kontak Darurat di Indonesia</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="emergency_id_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Kontak Darurat <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="emergency_id_name" id="emergency_id_name" value="{{ old('emergency_id_name') }}" required maxlength="150" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('emergency_id_name') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="emergency_id_relationship" class="block text-xs font-semibold text-slate-700 mb-1">Hubungan Keluarga <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="emergency_id_relationship" id="emergency_id_relationship" value="{{ old('emergency_id_relationship') }}" required maxlength="50" placeholder="Contoh: Saudara Kandung / Paman" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('emergency_id_relationship') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="emergency_id_address" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Kontak Darurat <span class="text-rose-600 font-bold">*</span></label>
                            <textarea name="emergency_id_address" id="emergency_id_address" rows="2" required class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">{{ old('emergency_id_address') }}</textarea>
                            @error('emergency_id_address') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="emergency_id_phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon/HP Darurat <span class="text-rose-600 font-bold">*</span></label>
                            <input type="text" name="emergency_id_phone" id="emergency_id_phone" value="{{ old('emergency_id_phone') }}" required maxlength="30" class="w-full font-mono text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                            @error('emergency_id_phone') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Kontak Darurat di Luar Negeri (Opsional) -->
                <div class="border border-slate-200 rounded p-4 bg-slate-50/50 space-y-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">2. Kontak Darurat di Luar Negeri (Opsional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="emergency_overseas_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Kontak LN</label>
                            <input type="text" name="emergency_overseas_name" id="emergency_overseas_name" value="{{ old('emergency_overseas_name') }}" maxlength="150" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        </div>

                        <div>
                            <label for="emergency_overseas_phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon LN</label>
                            <input type="text" name="emergency_overseas_phone" id="emergency_overseas_phone" value="{{ old('emergency_overseas_phone') }}" maxlength="30" class="w-full font-mono text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">
                        </div>

                        <div class="md:col-span-2">
                            <label for="emergency_overseas_address" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Kontak di Luar Negeri</label>
                            <textarea name="emergency_overseas_address" id="emergency_overseas_address" rows="2" class="w-full text-xs sm:text-sm rounded border-slate-300 focus:border-[#1B365D] focus:ring-1 focus:ring-[#1B365D]">{{ old('emergency_overseas_address') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- LANGKAH 5: Dokumen Persyaratan Digital (Unggahan Berkas Reaktif) -->
            <!-- ============================================================== -->
            <div id="step-pane-5" class="wizard-pane space-y-6 hidden">
                <div class="border-b border-slate-200 pb-3">
                    <span class="text-[11px] font-mono font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        Bagian E: Seksi 5
                    </span>
                    <h2 class="text-lg font-bold text-slate-900 mt-1">Dokumen Persyaratan Digital</h2>
                    <p class="text-xs text-slate-600">Unggah berkas dokumen persyaratan resmi sesuai kategori pemohon (JPG, PNG, atau PDF maks 2 MB per berkas).</p>
                </div>

                <!-- Banner Kategori Dinamis: Kategori A (Dewasa) -->
                <div id="category-banner-adult" class="p-4 rounded-lg bg-blue-50/70 border border-blue-200 text-blue-950 text-xs">
                    <div class="flex items-center gap-2 font-bold text-blue-900 uppercase tracking-wide">
                        <span class="px-2 py-0.5 rounded bg-[#1B365D] text-white font-mono text-[10px]">Kategori A</span>
                        <span>Pemohon Dewasa (Usia &ge; 17 Tahun atau Pernah/Sudah Kawin)</span>
                    </div>
                    <p class="mt-1.5 text-slate-700 leading-relaxed">
                        Sesuai Permenkumham No. 8 Tahun 2014 jo. Permenkumham No. 18 Tahun 2022, pemohon dewasa wajib melampirkan <strong>KTP Elektronik</strong>, <strong>Kartu Keluarga</strong>, dan minimal salah satu dokumen pendukung: <strong>Akta Kelahiran</strong>, <strong>Buku Nikah</strong>, atau <strong>Paspor Lama</strong>.
                    </p>
                </div>

                <!-- Banner Kategori Dinamis: Kategori B (Anak) -->
                <div id="category-banner-child" class="p-4 rounded-lg bg-amber-50/80 border border-amber-300 text-amber-950 text-xs hidden">
                    <div class="flex items-center gap-2 font-bold text-amber-900 uppercase tracking-wide">
                        <span class="px-2 py-0.5 rounded bg-amber-800 text-white font-mono text-[10px]">Kategori B</span>
                        <span>Pemohon Anak-Anak (Usia &lt; 17 Tahun &amp; Belum Kawin)</span>
                    </div>
                    <p class="mt-1.5 text-slate-700 leading-relaxed">
                        Permohonan paspor untuk anak wajib melampirkan <strong>Kartu Keluarga</strong>, <strong>Akta Kelahiran Anak</strong>, <strong>KTP Orang Tua/Wali</strong>, dan <strong>Buku Nikah Orang Tua</strong> (atau Akta Cerai/Putusan Hak Asuh/Akta Kematian/Penetapan Perwalian jika berlaku).
                    </p>
                </div>

                <!-- Alert Galat Kelengkapan Dokumen Langkah 5 -->
                <div id="document-validation-alert" class="p-3.5 rounded bg-rose-50 border border-rose-300 text-rose-900 text-xs hidden" role="alert">
                    <div class="flex items-center gap-2 font-bold text-rose-800">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>Dokumen Persyaratan Belum Lengkap:</span>
                    </div>
                    <ul id="document-validation-list" class="mt-1.5 list-disc list-inside text-rose-700 space-y-0.5"></ul>
                </div>

                <!-- ============================================================== -->
                <!-- Slot Dokumen Kategori A (Dewasa) -->
                <!-- ============================================================== -->
                <div id="category-a-docs" class="space-y-5">
                    <div class="border-b border-slate-200 pb-1.5 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Dokumen Wajib &amp; Pendukung Pemohon Dewasa
                        </h3>
                        <span class="text-[11px] text-slate-500">Maksimal 2 MB per berkas (JPG/PNG/PDF)</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Dropzone: KTP Pemohon (Wajib) -->
                        <div class="border border-slate-200 rounded-lg p-4 bg-white space-y-2 dropzone-card" data-doc-type="ktp_pemohon">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">1. KTP Elektronik Pemohon</span>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 rounded">Wajib</span>
                            </div>
                            <p class="text-[11px] text-slate-500">Scan / foto e-KTP asli pemohon tampak jelas, tidak terpotong, dan tidak silau.</p>
                            
                            <div class="file-dropzone dropzone-file rounded-lg p-4 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('ktp_pemohon')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'ktp_pemohon')">
                                <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('ktp_pemohon', this.files)">
                                <div class="doc-idle-state space-y-1">
                                    <svg class="mx-auto h-8 w-8 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                    <div class="text-xs text-slate-700 font-medium">Klik atau seret berkas ke area ini</div>
                                    <div class="text-[10px] text-slate-400">JPG, PNG, atau PDF (Maks. 2 MB)</div>
                                </div>
                                <div class="doc-uploading-state hidden space-y-2 py-2">
                                    <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-[#1B365D] border-t-transparent"></div>
                                    <div class="text-xs text-slate-600 font-medium">Sedang mengunggah berkas...</div>
                                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden max-w-xs mx-auto">
                                        <div class="bg-[#1B365D] h-full transition-all duration-200" style="width: 70%;"></div>
                                    </div>
                                </div>
                                <div class="doc-success-state hidden text-left bg-emerald-50/80 p-3 rounded border border-emerald-300">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <span class="text-emerald-700 text-sm font-bold">✓</span>
                                            <div class="truncate">
                                                <p class="text-xs font-bold text-emerald-950 truncate filename-display">-</p>
                                                <p class="text-[10px] text-emerald-700 filesize-display">-</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" onclick="event.stopPropagation(); triggerFileInput('ktp_pemohon')" class="text-[11px] text-slate-600 hover:text-slate-900 underline font-medium">Ganti</button>
                                            <button type="button" onclick="event.stopPropagation(); removeDocument('ktp_pemohon')" class="text-[11px] text-rose-600 hover:text-rose-800 font-bold">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-error-state hidden p-2 rounded bg-rose-50 border border-rose-200 text-rose-700 text-[11px]"></div>
                        </div>

                        <!-- Dropzone: Kartu Keluarga (Wajib) -->
                        <div class="border border-slate-200 rounded-lg p-4 bg-white space-y-2 dropzone-card" data-doc-type="kartu_keluarga">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">2. Kartu Keluarga (KK)</span>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 rounded">Wajib</span>
                            </div>
                            <p class="text-[11px] text-slate-500">Scan / foto Kartu Keluarga asli atau salinan berbarcode Dukcapil yang jelas.</p>
                            
                            <div class="file-dropzone dropzone-file rounded-lg p-4 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('kartu_keluarga')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'kartu_keluarga')">
                                <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('kartu_keluarga', this.files)">
                                <div class="doc-idle-state space-y-1">
                                    <svg class="mx-auto h-8 w-8 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                    <div class="text-xs text-slate-700 font-medium">Klik atau seret berkas ke area ini</div>
                                    <div class="text-[10px] text-slate-400">JPG, PNG, atau PDF (Maks. 2 MB)</div>
                                </div>
                                <div class="doc-uploading-state hidden space-y-2 py-2">
                                    <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-[#1B365D] border-t-transparent"></div>
                                    <div class="text-xs text-slate-600 font-medium">Sedang mengunggah berkas...</div>
                                    <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden max-w-xs mx-auto">
                                        <div class="bg-[#1B365D] h-full transition-all duration-200" style="width: 70%;"></div>
                                    </div>
                                </div>
                                <div class="doc-success-state hidden text-left bg-emerald-50/80 p-3 rounded border border-emerald-300">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <span class="text-emerald-700 text-sm font-bold">✓</span>
                                            <div class="truncate">
                                                <p class="text-xs font-bold text-emerald-950 truncate filename-display">-</p>
                                                <p class="text-[10px] text-emerald-700 filesize-display">-</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" onclick="event.stopPropagation(); triggerFileInput('kartu_keluarga')" class="text-[11px] text-slate-600 hover:text-slate-900 underline font-medium">Ganti</button>
                                            <button type="button" onclick="event.stopPropagation(); removeDocument('kartu_keluarga')" class="text-[11px] text-rose-600 hover:text-rose-800 font-bold">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-error-state hidden p-2 rounded bg-rose-50 border border-rose-200 text-rose-700 text-[11px]"></div>
                        </div>
                    </div>

                    <!-- Dokumen Bukti Diri Pemohon (Minimal 1 Wajib) -->
                    <div class="border border-slate-200 rounded-lg p-4 bg-slate-50/60 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-b border-slate-200 pb-2">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">3. Dokumen Bukti Diri (Pilih Minimal Salah Satu) <span class="text-rose-600 font-bold">*</span></h4>
                                <p class="text-[11px] text-slate-600">Unggah minimal satu dokumen untuk mencocokkan nama, tempat, dan tanggal lahir pemohon.</p>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-300 rounded self-start sm:self-auto">Min. 1 Wajib</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Akta Kelahiran -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="akta_kelahiran">
                                <span class="text-xs font-semibold text-slate-800 block">Akta Kelahiran</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('akta_kelahiran')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'akta_kelahiran')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('akta_kelahiran', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Unggah Akta</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[11px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[11px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-[9px] text-emerald-700 filesize-display">-</span>
                                            <button type="button" onclick="event.stopPropagation(); removeDocument('akta_kelahiran')" class="text-[10px] text-rose-600 font-bold hover:underline">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1.5 rounded bg-rose-50 border border-rose-200 text-rose-700 text-[10px]"></div>
                            </div>

                            <!-- Buku Nikah Pemohon -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="buku_nikah_pemohon">
                                <span class="text-xs font-semibold text-slate-800 block">Buku Nikah Pemohon</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('buku_nikah_pemohon')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'buku_nikah_pemohon')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('buku_nikah_pemohon', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Unggah Buku Nikah</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[11px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[11px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-[9px] text-emerald-700 filesize-display">-</span>
                                            <button type="button" onclick="event.stopPropagation(); removeDocument('buku_nikah_pemohon')" class="text-[10px] text-rose-600 font-bold hover:underline">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1.5 rounded bg-rose-50 border border-rose-200 text-rose-700 text-[10px]"></div>
                            </div>

                            <!-- Paspor Lama -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="paspor_lama">
                                <span class="text-xs font-semibold text-slate-800 block">Paspor Lama</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('paspor_lama')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'paspor_lama')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('paspor_lama', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Unggah Paspor Lama</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[11px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[11px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-[9px] text-emerald-700 filesize-display">-</span>
                                            <button type="button" onclick="event.stopPropagation(); removeDocument('paspor_lama')" class="text-[10px] text-rose-600 font-bold hover:underline">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1.5 rounded bg-rose-50 border border-rose-200 text-rose-700 text-[10px]"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- Slot Dokumen Kategori B (Anak-Anak) -->
                <!-- ============================================================== -->
                <div id="category-b-docs" class="space-y-5 hidden">
                    <div class="border-b border-slate-200 pb-1.5 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Dokumen Wajib &amp; Legalitas Pemohon Anak-Anak
                        </h3>
                        <span class="text-[11px] text-slate-500">Maksimal 2 MB per berkas (JPG/PNG/PDF)</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Kartu Keluarga (Wajib Anak) -->
                        <div class="border border-slate-200 rounded-lg p-4 bg-white space-y-2 dropzone-card" data-doc-type="kartu_keluarga">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">1. Kartu Keluarga (Memuat Nama Anak &amp; Ortu)</span>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 rounded">Wajib</span>
                            </div>
                            <p class="text-[11px] text-slate-500">Scan / foto Kartu Keluarga asli atau salinan berbarcode Dukcapil.</p>
                            
                            <div class="file-dropzone dropzone-file rounded-lg p-4 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('kartu_keluarga')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'kartu_keluarga')">
                                <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('kartu_keluarga', this.files)">
                                <div class="doc-idle-state space-y-1">
                                    <svg class="mx-auto h-8 w-8 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                    <div class="text-xs text-slate-700 font-medium">Klik atau seret Kartu Keluarga ke sini</div>
                                    <div class="text-[10px] text-slate-400">JPG, PNG, atau PDF (Maks. 2 MB)</div>
                                </div>
                                <div class="doc-uploading-state hidden space-y-2 py-2">
                                    <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-[#1B365D] border-t-transparent"></div>
                                    <div class="text-xs text-slate-600 font-medium">Sedang mengunggah berkas...</div>
                                </div>
                                <div class="doc-success-state hidden text-left bg-emerald-50/80 p-3 rounded border border-emerald-300">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <span class="text-emerald-700 text-sm font-bold">✓</span>
                                            <div class="truncate">
                                                <p class="text-xs font-bold text-emerald-950 truncate filename-display">-</p>
                                                <p class="text-[10px] text-emerald-700 filesize-display">-</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" onclick="event.stopPropagation(); triggerFileInput('kartu_keluarga')" class="text-[11px] text-slate-600 hover:text-slate-900 underline font-medium">Ganti</button>
                                            <button type="button" onclick="event.stopPropagation(); removeDocument('kartu_keluarga')" class="text-[11px] text-rose-600 hover:text-rose-800 font-bold">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-error-state hidden p-2 rounded bg-rose-50 border border-rose-200 text-rose-700 text-[11px]"></div>
                        </div>

                        <!-- Akta Kelahiran Anak (Wajib Anak) -->
                        <div class="border border-slate-200 rounded-lg p-4 bg-white space-y-2 dropzone-card" data-doc-type="akta_kelahiran">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800">2. Akta Kelahiran Anak</span>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 rounded">Wajib</span>
                            </div>
                            <p class="text-[11px] text-slate-500">Scan / foto Akta Kelahiran asli anak yang mencantumkan nama orang tua kandung.</p>
                            
                            <div class="file-dropzone dropzone-file rounded-lg p-4 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('akta_kelahiran')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'akta_kelahiran')">
                                <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('akta_kelahiran', this.files)">
                                <div class="doc-idle-state space-y-1">
                                    <svg class="mx-auto h-8 w-8 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                    <div class="text-xs text-slate-700 font-medium">Klik atau seret Akta Kelahiran ke sini</div>
                                    <div class="text-[10px] text-slate-400">JPG, PNG, atau PDF (Maks. 2 MB)</div>
                                </div>
                                <div class="doc-uploading-state hidden space-y-2 py-2">
                                    <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-[#1B365D] border-t-transparent"></div>
                                    <div class="text-xs text-slate-600 font-medium">Sedang mengunggah berkas...</div>
                                </div>
                                <div class="doc-success-state hidden text-left bg-emerald-50/80 p-3 rounded border border-emerald-300">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <span class="text-emerald-700 text-sm font-bold">✓</span>
                                            <div class="truncate">
                                                <p class="text-xs font-bold text-emerald-950 truncate filename-display">-</p>
                                                <p class="text-[10px] text-emerald-700 filesize-display">-</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" onclick="event.stopPropagation(); triggerFileInput('akta_kelahiran')" class="text-[11px] text-slate-600 hover:text-slate-900 underline font-medium">Ganti</button>
                                            <button type="button" onclick="event.stopPropagation(); removeDocument('akta_kelahiran')" class="text-[11px] text-rose-600 hover:text-rose-800 font-bold">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-error-state hidden p-2 rounded bg-rose-50 border border-rose-200 text-rose-700 text-[11px]"></div>
                        </div>
                    </div>

                    <!-- Identitas Orang Tua / Wali (Pilih Minimal 1) -->
                    <div class="border border-slate-200 rounded-lg p-4 bg-slate-50/60 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-b border-slate-200 pb-2">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">3. Identitas Orang Tua / Wali (Pilih Minimal Salah Satu) <span class="text-rose-600 font-bold">*</span></h4>
                                <p class="text-[11px] text-slate-600">Unggah KTP Ayah, KTP Ibu, KTP Wali, atau Akta Kematian jika salah satu/kedua ortu telah wafat.</p>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-300 rounded self-start sm:self-auto">Min. 1 Wajib</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <!-- KTP Ayah -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="ktp_ayah">
                                <span class="text-xs font-semibold text-slate-800 block truncate">KTP Ayah</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('ktp_ayah')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'ktp_ayah')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('ktp_ayah', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Unggah KTP Ayah</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[10px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[10px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <button type="button" onclick="event.stopPropagation(); removeDocument('ktp_ayah')" class="text-[9px] text-rose-600 font-bold hover:underline block mt-1">Hapus</button>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1 rounded bg-rose-50 text-rose-700 text-[10px]"></div>
                            </div>

                            <!-- KTP Ibu -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="ktp_ibu">
                                <span class="text-xs font-semibold text-slate-800 block truncate">KTP Ibu</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('ktp_ibu')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'ktp_ibu')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('ktp_ibu', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Unggah KTP Ibu</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[10px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[10px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <button type="button" onclick="event.stopPropagation(); removeDocument('ktp_ibu')" class="text-[9px] text-rose-600 font-bold hover:underline block mt-1">Hapus</button>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1 rounded bg-rose-50 text-rose-700 text-[10px]"></div>
                            </div>

                            <!-- KTP Wali -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="ktp_wali">
                                <span class="text-xs font-semibold text-slate-800 block truncate">KTP Wali Sah</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('ktp_wali')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'ktp_wali')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('ktp_wali', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Unggah KTP Wali</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[10px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[10px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <button type="button" onclick="event.stopPropagation(); removeDocument('ktp_wali')" class="text-[9px] text-rose-600 font-bold hover:underline block mt-1">Hapus</button>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1 rounded bg-rose-50 text-rose-700 text-[10px]"></div>
                            </div>

                            <!-- Akta Kematian Orang Tua -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="akta_kematian_orang_tua">
                                <span class="text-xs font-semibold text-slate-800 block truncate">Akta Kematian Ortu</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('akta_kematian_orang_tua')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'akta_kematian_orang_tua')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('akta_kematian_orang_tua', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Akta Kematian</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[10px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[10px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <button type="button" onclick="event.stopPropagation(); removeDocument('akta_kematian_orang_tua')" class="text-[9px] text-rose-600 font-bold hover:underline block mt-1">Hapus</button>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1 rounded bg-rose-50 text-rose-700 text-[10px]"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Dokumen Legalitas Pernikahan / Hak Asuh / Perwalian (Pilih Sesuai Status) -->
                    <div class="border border-slate-200 rounded-lg p-4 bg-slate-50/60 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-b border-slate-200 pb-2">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">4. Legalitas Hubungan Orang Tua / Perwalian <span class="text-rose-600 font-bold">*</span></h4>
                                <p class="text-[11px] text-slate-600">Unggah Buku Nikah Orang Tua (atau Akta Cerai/Hak Asuh/Penetapan Perwalian jika diasuh wali).</p>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-300 rounded self-start sm:self-auto">Wajib Legalitas</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <!-- Buku Nikah Orang Tua -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="buku_nikah_orang_tua">
                                <span class="text-xs font-semibold text-slate-800 block truncate">Buku Nikah Ortu</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('buku_nikah_orang_tua')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'buku_nikah_orang_tua')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('buku_nikah_orang_tua', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Buku Nikah Ortu</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[10px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[10px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <button type="button" onclick="event.stopPropagation(); removeDocument('buku_nikah_orang_tua')" class="text-[9px] text-rose-600 font-bold hover:underline block mt-1">Hapus</button>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1 rounded bg-rose-50 text-rose-700 text-[10px]"></div>
                            </div>

                            <!-- Akta Cerai Orang Tua -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="akta_cerai_orang_tua">
                                <span class="text-xs font-semibold text-slate-800 block truncate">Akta Cerai Ortu</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('akta_cerai_orang_tua')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'akta_cerai_orang_tua')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('akta_cerai_orang_tua', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Akta Cerai Ortu</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[10px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[10px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <button type="button" onclick="event.stopPropagation(); removeDocument('akta_cerai_orang_tua')" class="text-[9px] text-rose-600 font-bold hover:underline block mt-1">Hapus</button>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1 rounded bg-rose-50 text-rose-700 text-[10px]"></div>
                            </div>

                            <!-- Putusan Hak Asuh -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="putusan_hak_asuh">
                                <span class="text-xs font-semibold text-slate-800 block truncate">Putusan Hak Asuh</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('putusan_hak_asuh')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'putusan_hak_asuh')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('putusan_hak_asuh', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Putusan Hak Asuh</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[10px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[10px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <button type="button" onclick="event.stopPropagation(); removeDocument('putusan_hak_asuh')" class="text-[9px] text-rose-600 font-bold hover:underline block mt-1">Hapus</button>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1 rounded bg-rose-50 text-rose-700 text-[10px]"></div>
                            </div>

                            <!-- Penetapan Pengadilan Wali -->
                            <div class="border border-slate-200 rounded p-3 bg-white space-y-2 dropzone-card" data-doc-type="penetapan_pengadilan_wali">
                                <span class="text-xs font-semibold text-slate-800 block truncate">Penetapan Wali PN</span>
                                <div class="file-dropzone dropzone-file rounded p-3 text-center cursor-pointer transition-all border-2 border-dashed border-slate-300 hover:border-[#1B365D]" onclick="triggerFileInput('penetapan_pengadilan_wali')" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleFileDrop(event, 'penetapan_pengadilan_wali')">
                                    <input type="file" class="doc-file-input hidden" accept=".jpg,.jpeg,.png,.pdf" onchange="handleFileSelect('penetapan_pengadilan_wali', this.files)">
                                    <div class="doc-idle-state space-y-1">
                                        <div class="text-[11px] text-slate-700 font-medium">Penetapan Wali</div>
                                        <div class="text-[10px] text-slate-400">&le; 2 MB</div>
                                    </div>
                                    <div class="doc-uploading-state hidden py-1"><span class="text-[10px] text-slate-500 font-medium">Mengunggah...</span></div>
                                    <div class="doc-success-state hidden text-left bg-emerald-50 p-2 rounded border border-emerald-300">
                                        <p class="text-[10px] font-bold text-emerald-950 truncate filename-display">-</p>
                                        <button type="button" onclick="event.stopPropagation(); removeDocument('penetapan_pengadilan_wali')" class="text-[9px] text-rose-600 font-bold hover:underline block mt-1">Hapus</button>
                                    </div>
                                </div>
                                <div class="doc-error-state hidden p-1 rounded bg-rose-50 text-rose-700 text-[10px]"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kontainer Injeksi Hidden Input Token untuk Submisi Form -->
                <div id="uploaded-document-tokens-container">
                    @if (old('document_tokens'))
                        @foreach (old('document_tokens') as $index => $oldToken)
                            <input type="hidden" name="document_tokens[]" value="{{ $oldToken }}">
                            <input type="hidden" name="document_types[]" value="{{ old('document_types.'.$index, '') }}">
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- LANGKAH 6: Pratinjau Ringkasan & Konfirmasi -->
            <!-- ============================================================== -->
            <div id="step-pane-6" class="wizard-pane space-y-6 hidden">
                <div class="border-b border-slate-200 pb-3">
                    <span class="text-[11px] font-mono font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        Bagian F: Langkah Terakhir
                    </span>
                    <h2 class="text-lg font-bold text-slate-900 mt-1">Pratinjau &amp; Konfirmasi Data Permohonan</h2>
                    <p class="text-xs text-slate-600">Periksa kembali data Anda dan dokumen persyaratan yang terunggah sebelum mengirimkan permohonan ke sistem verifikasi.</p>
                </div>

                <!-- Kartu Pratinjau Data Lengkap -->
                <div class="space-y-4">
                    <!-- Seksi 1 Review -->
                    <div class="border border-slate-200 rounded p-4 bg-slate-50/60">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Seksi 1: Jenis Permohonan</h3>
                            <button type="button" onclick="goToStep(1)" class="text-xs text-[#1B365D] hover:underline font-semibold">&larr; Ubah</button>
                        </div>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 text-xs">
                            <div><dt class="text-slate-500">Jenis Permohonan:</dt><dd id="review-app-type" class="font-bold text-slate-800">-</dd></div>
                            <div><dt class="text-slate-500">Jenis Paspor:</dt><dd id="review-passport-type" class="font-bold text-slate-800">-</dd></div>
                            <div id="review-replacement-container" class="hidden"><dt class="text-slate-500">Alasan Penggantian:</dt><dd id="review-replacement-reason" class="font-bold text-slate-800">-</dd></div>
                            <div id="review-change-container" class="hidden"><dt class="text-slate-500">Alasan Perubahan:</dt><dd id="review-change-reason" class="font-bold text-slate-800">-</dd></div>
                        </dl>
                    </div>

                    <!-- Seksi 2 Review -->
                    <div class="border border-slate-200 rounded p-4 bg-slate-50/60">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Seksi 2: Identitas Diri Pemohon</h3>
                            <button type="button" onclick="goToStep(2)" class="text-xs text-[#1B365D] hover:underline font-semibold">&larr; Ubah</button>
                        </div>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 text-xs">
                            <div><dt class="text-slate-500">Nama Lengkap:</dt><dd id="review-full-name" class="font-bold text-slate-800 font-mono">-</dd></div>
                            <div><dt class="text-slate-500">Jenis Kelamin:</dt><dd id="review-gender" class="font-bold text-slate-800">-</dd></div>
                            <div><dt class="text-slate-500">Tempat, Tanggal Lahir:</dt><dd id="review-ttl" class="font-bold text-slate-800 font-mono">-</dd></div>
                            <div><dt class="text-slate-500">Tinggi Badan:</dt><dd id="review-height" class="font-bold text-slate-800 font-mono">-</dd></div>
                            <div><dt class="text-slate-500">NIK / Nomor Identitas:</dt><dd id="review-nik-id" class="font-bold text-slate-800 font-mono">-</dd></div>
                            <div><dt class="text-slate-500">Email Aktif:</dt><dd id="review-email" class="font-bold text-slate-800 font-mono">-</dd></div>
                            <div><dt class="text-slate-500">Nomor Telepon:</dt><dd id="review-phone" class="font-bold text-slate-800 font-mono">-</dd></div>
                            <div><dt class="text-slate-500">Status Perkawinan:</dt><dd id="review-marital" class="font-bold text-slate-800">-</dd></div>
                            <div class="sm:col-span-2"><dt class="text-slate-500">Alamat di Indonesia:</dt><dd id="review-address" class="font-medium text-slate-800">-</dd></div>
                        </dl>
                    </div>

                    <!-- Seksi 3 Review -->
                    <div class="border border-slate-200 rounded p-4 bg-slate-50/60">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Seksi 3: Data Keluarga</h3>
                            <button type="button" onclick="goToStep(3)" class="text-xs text-[#1B365D] hover:underline font-semibold">&larr; Ubah</button>
                        </div>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 text-xs">
                            <div><dt class="text-slate-500">Nama Ibu Kandung:</dt><dd id="review-mother-name" class="font-bold text-slate-800 font-mono">-</dd></div>
                            <div><dt class="text-slate-500">Kewarganegaraan Ibu:</dt><dd id="review-mother-nat" class="font-bold text-slate-800">-</dd></div>
                            <div><dt class="text-slate-500">Nama Ayah Kandung:</dt><dd id="review-father-name" class="font-bold text-slate-800 font-mono">-</dd></div>
                            <div><dt class="text-slate-500">Nomor Telepon Ayah:</dt><dd id="review-father-phone" class="font-bold text-slate-800 font-mono">-</dd></div>
                            <div id="review-spouse-row" class="sm:col-span-2 hidden">
                                <dt class="text-slate-500">Nama Pasangan (Suami/Istri):</dt>
                                <dd id="review-spouse-name" class="font-bold text-slate-800 font-mono">-</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Seksi 4 Review -->
                    <div class="border border-slate-200 rounded p-4 bg-slate-50/60">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Seksi 4: Kontak Darurat</h3>
                            <button type="button" onclick="goToStep(4)" class="text-xs text-[#1B365D] hover:underline font-semibold">&larr; Ubah</button>
                        </div>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 text-xs">
                            <div><dt class="text-slate-500">Nama Kontak Darurat:</dt><dd id="review-emergency-name" class="font-bold text-slate-800 font-mono">-</dd></div>
                            <div><dt class="text-slate-500">Hubungan Keluarga:</dt><dd id="review-emergency-rel" class="font-bold text-slate-800">-</dd></div>
                            <div class="sm:col-span-2"><dt class="text-slate-500">Nomor Telepon Darurat:</dt><dd id="review-emergency-phone" class="font-bold text-slate-800 font-mono">-</dd></div>
                        </dl>
                    </div>

                    <!-- Seksi 5 Review: Ringkasan Dokumen Terunggah -->
                    <div class="border border-slate-200 rounded p-4 bg-slate-50/60">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Seksi 5: Dokumen Persyaratan Digital</h3>
                            <button type="button" onclick="goToStep(5)" class="text-xs text-[#1B365D] hover:underline font-semibold">&larr; Kelola Dokumen</button>
                        </div>
                        <div id="review-documents-list" class="pt-2 text-xs space-y-2">
                            <p class="text-slate-400 italic">Memuat ringkasan dokumen...</p>
                        </div>
                    </div>
                </div>

                <!-- Pernyataan Integritas & Keabsahan Data (Adaptif Kategori A vs B) -->
                <div class="border-2 border-slate-300 rounded-lg p-4 bg-slate-50 space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="declaration_accepted" id="declaration_accepted" value="1" {{ old('declaration_accepted') ? 'checked' : '' }} required class="mt-1 rounded border-slate-300 text-[#1B365D] focus:ring-[#1B365D]">
                        <span class="text-xs text-slate-800 leading-relaxed">
                            <strong>Pernyataan Integritas (Pasal 242 KUHP &amp; UU Keimigrasian):</strong>
                            <span id="declaration-clause-text">
                                Saya yang bertanda tangan di bawah ini menyatakan dengan sesungguhnya bahwa seluruh data, keterangan, dan dokumen persyaratan digital yang saya berikan dalam Formulir PERDIM 14 ini adalah benar dan sah milik saya pribadi. Apabila di kemudian hari terbukti keterangan atau dokumen ini palsu atau dipalsukan, saya bersedia dituntut sesuai ketentuan perundang-undangan (Pasal 242 KUHP dan UU No. 6 Tahun 2011 tentang Keimigrasian).
                            </span>
                            <span class="text-rose-600 font-bold">*</span>
                        </span>
                    </label>
                    @error('declaration_accepted')
                        <p class="text-[11px] text-rose-600 font-medium pl-7">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-4 rounded bg-emerald-50 border border-emerald-200 text-emerald-950 text-xs">
                    <strong class="block font-bold mb-1">Siap untuk Mengirimkan Permohonan?</strong>
                    Setelah tombol kirim ditekan, sistem akan memvalidasi kelengkapan dokumen persyaratan secara otomatis, menerbitkan <strong>Nomor Registrasi Resmi SPRI</strong>, dan menyusun lembar bukti tanda terima pendaftaran untuk Anda bawa ke loket verifikasi kantor imigrasi.
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- Panel Tombol Navigasi Wizard (Bawah) -->
            <!-- ============================================================== -->
            <div class="flex items-center justify-between pt-6 border-t border-slate-200 mt-8">
                <!-- Tombol Sebelumnya -->
                <button type="button" id="prev-btn" onclick="prevStep()" class="px-4 py-2 border border-slate-300 text-xs font-semibold rounded text-slate-700 bg-white hover:bg-slate-50 transition-colors hidden">
                    &larr; Sebelumnya
                </button>
                <div id="prev-placeholder" class="w-1"></div>

                <!-- Tombol Berikutnya / Submit -->
                <div class="flex items-center gap-3">
                    <button type="button" id="next-btn" onclick="nextStep()" class="px-5 py-2.5 bg-[#1B365D] hover:bg-[#12243E] text-white text-xs font-semibold rounded shadow-xs transition-colors flex items-center gap-1.5">
                        <span>Lanjutkan ke Langkah Berikutnya</span>
                        <span>&rarr;</span>
                    </button>

                    <button type="submit" id="submit-btn" class="px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded shadow-xs transition-colors flex items-center gap-1.5 hidden">
                        <span>Kirim Permohonan Paspor (PERDIM 14)</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<!-- Wizard Logic & Asynchronous Dropzone JavaScript -->
<script>
    let currentStep = 1;
    const totalSteps = 6;
    const uploadedDocuments = {};

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // Label pemetaan jenis dokumen resmi PERDIM 14
    const documentTypeLabels = {
        'ktp_pemohon': 'KTP Elektronik Pemohon',
        'kartu_keluarga': 'Kartu Keluarga (KK)',
        'akta_kelahiran': 'Akta Kelahiran',
        'buku_nikah_pemohon': 'Buku Nikah Pemohon',
        'paspor_lama': 'Paspor Lama',
        'surat_pewarganegaraan': 'Surat Pewarganegaraan',
        'penetapan_ganti_nama': 'Penetapan Ganti Nama',
        'ktp_ayah': 'KTP Ayah Kandung',
        'ktp_ibu': 'KTP Ibu Kandung',
        'ktp_wali': 'KTP Wali Sah',
        'akta_kematian_orang_tua': 'Akta Kematian Orang Tua',
        'buku_nikah_orang_tua': 'Buku Nikah Orang Tua',
        'akta_cerai_orang_tua': 'Akta Cerai Orang Tua',
        'putusan_hak_asuh': 'Putusan Hak Asuh Pengadilan',
        'penetapan_pengadilan_wali': 'Penetapan Pengadilan Wali',
        'paspor_orang_tua': 'Paspor Orang Tua',
        'surat_pernyataan_orang_tua': 'Surat Pernyataan Orang Tua'
    };

    document.addEventListener('DOMContentLoaded', function () {
        @if ($errors->any())
            @if ($errors->hasAny(['application_type', 'passport_type', 'replacement_reason', 'change_reason']))
                currentStep = 1;
            @elseif ($errors->hasAny(['full_name', 'alias_name', 'gender', 'height_cm', 'birth_place', 'birth_date', 'nik', 'identity_number', 'identity_issued_place', 'identity_issued_date', 'identity_expiry_date', 'occupation', 'workplace_address', 'office_phone', 'indonesia_address', 'indonesia_phone', 'overseas_address', 'overseas_phone', 'email', 'marital_status']))
                currentStep = 2;
            @elseif ($errors->hasAny(['mother_name', 'mother_nationality', 'mother_birth_place', 'mother_birth_date', 'father_name', 'father_nationality', 'father_birth_place', 'father_birth_date', 'father_address', 'father_phone', 'spouse_name', 'spouse_nationality', 'spouse_birth_place', 'spouse_birth_date', 'spouse_address']))
                currentStep = 3;
            @elseif ($errors->hasAny(['change_details', 'emergency_id_name', 'emergency_id_relationship', 'emergency_id_address', 'emergency_id_phone', 'emergency_overseas_name', 'emergency_overseas_address', 'emergency_overseas_phone']))
                currentStep = 4;
            @elseif ($errors->hasAny(['document_tokens', 'document_types']))
                currentStep = 5;
            @elseif ($errors->hasAny(['declaration_accepted']))
                currentStep = 6;
            @endif
        @endif

        updateCategoryReactivity();
        updateStepView();
    });

    // ==============================================================
    // Deteksi Kategori Pemohon & Reaktivitas UI
    // ==============================================================
    function getApplicantCategory() {
        const birthDateInput = document.getElementById('birth_date');
        const maritalInput = document.getElementById('marital_status');

        if (!birthDateInput || !birthDateInput.value) {
            return 'A'; // Standar Kategori A (Dewasa)
        }

        const birthDate = new Date(birthDateInput.value);
        if (isNaN(birthDate.getTime())) {
            return 'A';
        }

        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        const marital = (maritalInput?.value || '').toLowerCase().trim();
        const isMarried = ['kawin', 'cerai_hidup', 'cerai_mati'].includes(marital);

        if (age >= 17 || isMarried) {
            return 'A'; // Kategori A: Dewasa
        }

        return 'B'; // Kategori B: Anak-Anak
    }

    function updateCategoryReactivity() {
        const category = getApplicantCategory();
        const bannerAdult = document.getElementById('category-banner-adult');
        const bannerChild = document.getElementById('category-banner-child');
        const docsAdult = document.getElementById('category-a-docs');
        const docsChild = document.getElementById('category-b-docs');
        const step3ChildNotice = document.getElementById('step3-child-notice');
        const spouseSec = document.getElementById('spouse-section-container');
        const declText = document.getElementById('declaration-clause-text');

        if (category === 'A') {
            if (bannerAdult) bannerAdult.classList.remove('hidden');
            if (bannerChild) bannerChild.classList.add('hidden');
            if (docsAdult) docsAdult.classList.remove('hidden');
            if (docsChild) docsChild.classList.add('hidden');
            if (step3ChildNotice) step3ChildNotice.classList.add('hidden');
            if (declText) {
                declText.textContent = "Saya yang bertanda tangan di bawah ini menyatakan dengan sesungguhnya bahwa seluruh data, keterangan, dan dokumen persyaratan digital yang saya berikan dalam Formulir PERDIM 14 ini adalah benar dan sah milik saya pribadi. Apabila di kemudian hari terbukti keterangan atau dokumen ini palsu atau dipalsukan, saya bersedia dituntut sesuai ketentuan perundang-undangan (Pasal 242 KUHP dan UU No. 6 Tahun 2011 tentang Keimigrasian).";
            }
            handleMaritalStatusChange();
        } else {
            if (bannerAdult) bannerAdult.classList.add('hidden');
            if (bannerChild) bannerChild.classList.remove('hidden');
            if (docsAdult) docsAdult.classList.add('hidden');
            if (docsChild) docsChild.classList.remove('hidden');
            if (step3ChildNotice) step3ChildNotice.classList.remove('hidden');
            if (spouseSec) spouseSec.classList.add('hidden');
            if (declText) {
                declText.textContent = "Saya selaku Orang Tua / Wali penanggung jawab sah dari anak pemohon menyatakan dengan sesungguhnya bahwa seluruh data, keterangan, dan dokumen persyaratan digital yang diunggah dalam Formulir PERDIM 14 ini adalah benar dan sah. Apabila di kemudian hari terbukti keterangan atau dokumen ini palsu atau dipalsukan, saya bersedia dituntut sesuai ketentuan perundang-undangan yang berlaku di Republik Indonesia.";
            }
        }
    }

    function handleApplicationTypeChange() {
        const type = document.getElementById('application_type').value;
        const replContainer = document.getElementById('replacement-reason-container');
        const replInput = document.getElementById('replacement_reason');
        const changeContainer = document.getElementById('change-reason-container');
        const changeInput = document.getElementById('change_reason');
        const changeDetailsSec = document.getElementById('change-details-section');

        if (type === 'penggantian') {
            replContainer.classList.remove('hidden');
            replInput.required = true;
        } else {
            replContainer.classList.add('hidden');
            replInput.required = false;
        }

        if (type === 'perubahan') {
            changeContainer.classList.remove('hidden');
            changeInput.required = true;
            if (changeDetailsSec) changeDetailsSec.classList.remove('hidden');
        } else {
            changeContainer.classList.add('hidden');
            changeInput.required = false;
            if (changeDetailsSec) changeDetailsSec.classList.add('hidden');
        }
    }

    function handleMaritalStatusChange() {
        const marital = document.getElementById('marital_status').value;
        const spouseSec = document.getElementById('spouse-section-container');
        const spouseName = document.getElementById('spouse_name');
        const spouseNat = document.getElementById('spouse_nationality');
        const category = getApplicantCategory();

        if (category === 'A' && marital === 'kawin') {
            spouseSec.classList.remove('hidden');
            spouseName.required = true;
            spouseNat.required = true;
        } else {
            spouseSec.classList.add('hidden');
            spouseName.required = false;
            spouseNat.required = false;
        }
    }

    // ==============================================================
    // Asynchronous Dropzone Upload & State Management
    // ==============================================================
    function triggerFileInput(docType) {
        // Cari kontainer yang terlihat untuk docType yang bersangkutan
        const containers = document.querySelectorAll(`[data-doc-type="${docType}"]`);
        for (const card of containers) {
            if (!card.closest('.hidden')) {
                const input = card.querySelector('.doc-file-input');
                if (input) {
                    input.click();
                    return;
                }
            }
        }
        // Fallback jika tidak ada yang terlihat
        const fallback = document.querySelector(`[data-doc-type="${docType}"] .doc-file-input`);
        if (fallback) fallback.click();
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        const dropzone = e.currentTarget;
        dropzone.classList.add('border-[#1B365D]', 'bg-blue-50/50', 'is-dragover');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        const dropzone = e.currentTarget;
        dropzone.classList.remove('border-[#1B365D]', 'bg-blue-50/50', 'is-dragover');
    }

    function handleFileDrop(e, docType) {
        e.preventDefault();
        e.stopPropagation();
        const dropzone = e.currentTarget;
        dropzone.classList.remove('border-[#1B365D]', 'bg-blue-50/50', 'is-dragover');

        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            handleFileSelect(docType, e.dataTransfer.files);
        }
    }

    function handleFileSelect(docType, files) {
        if (!files || files.length === 0) return;
        const file = files[0];

        // Validasi ekstensi di sisi klien
        const validExts = ['jpg', 'jpeg', 'png', 'pdf'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!validExts.includes(ext)) {
            showDocError(docType, 'Format berkas tidak didukung. Harap unggah berkas JPG, PNG, atau PDF.');
            return;
        }

        // Validasi ukuran di sisi klien (maks 2 MB = 2.097.152 bytes)
        if (file.size <= 0 || file.size > 2097152) {
            showDocError(docType, 'Ukuran berkas melebihi batas maksimum 2 MB (' + (file.size / 1024 / 1024).toFixed(2) + ' MB).');
            return;
        }

        uploadDocument(docType, file);
    }

    async function uploadDocument(docType, file) {
        setDocUploadingState(docType, true);
        clearDocError(docType);

        const formData = new FormData();
        formData.append('file', file);
        formData.append('document_type', docType);

        try {
            const response = await fetch('{{ route('public.perdim.upload-temp') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok && result.success) {
                uploadedDocuments[docType] = result.data;
                renderDropzoneUI(docType);
                syncHiddenInputs();
                clearDocValidationAlert();
            } else {
                let errorMsg = 'Gagal mengunggah berkas. Periksa format dan ukuran berkas.';
                if (result.errors && result.errors.file) {
                    errorMsg = Array.isArray(result.errors.file) ? result.errors.file[0] : result.errors.file;
                } else if (result.message) {
                    errorMsg = result.message;
                }
                showDocError(docType, errorMsg);
                setDocUploadingState(docType, false);
            }
        } catch (err) {
            showDocError(docType, 'Terjadi kendala jaringan saat mengunggah berkas. Silakan coba kembali.');
            setDocUploadingState(docType, false);
        }
    }

    async function removeDocument(docType) {
        const doc = uploadedDocuments[docType];
        if (!doc) return;

        const token = doc.token;
        delete uploadedDocuments[docType];
        renderDropzoneUI(docType);
        syncHiddenInputs();

        try {
            await fetch('{{ route('public.perdim.delete-temp') }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token: token })
            });
        } catch (err) {
            console.warn('Gagal menghapus berkas sementara dari server:', err);
        }
    }

    function setDocUploadingState(docType, isUploading) {
        const cards = document.querySelectorAll(`[data-doc-type="${docType}"]`);
        cards.forEach(card => {
            const idle = card.querySelector('.doc-idle-state');
            const uploading = card.querySelector('.doc-uploading-state');
            const success = card.querySelector('.doc-success-state');

            if (isUploading) {
                if (idle) idle.classList.add('hidden');
                if (success) success.classList.add('hidden');
                if (uploading) uploading.classList.remove('hidden');
            } else {
                if (uploading) uploading.classList.add('hidden');
            }
        });
    }

    function renderDropzoneUI(docType) {
        const doc = uploadedDocuments[docType];
        const cards = document.querySelectorAll(`[data-doc-type="${docType}"]`);

        cards.forEach(card => {
            const idle = card.querySelector('.doc-idle-state');
            const uploading = card.querySelector('.doc-uploading-state');
            const success = card.querySelector('.doc-success-state');
            const dropzone = card.querySelector('.file-dropzone');
            const fileInput = card.querySelector('.doc-file-input');

            if (doc) {
                if (idle) idle.classList.add('hidden');
                if (uploading) uploading.classList.add('hidden');
                if (success) {
                    success.classList.remove('hidden');
                    const fnEl = success.querySelector('.filename-display');
                    const fsEl = success.querySelector('.filesize-display');
                    if (fnEl) fnEl.textContent = doc.original_name || 'berkas_persyaratan';
                    if (fsEl) {
                        const kb = doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '';
                        fsEl.textContent = kb ? `${kb} • Terunggah dan Terverifikasi` : 'Terunggah dan Terverifikasi';
                    }
                }
                if (dropzone) {
                    dropzone.classList.remove('border-dashed', 'border-slate-300');
                    dropzone.classList.add('border-emerald-400', 'bg-emerald-50/30', 'is-valid');
                }
            } else {
                if (success) success.classList.add('hidden');
                if (uploading) uploading.classList.add('hidden');
                if (idle) idle.classList.remove('hidden');
                if (dropzone) {
                    dropzone.classList.remove('border-emerald-400', 'bg-emerald-50/30', 'is-valid', 'border-rose-500', 'has-error');
                    dropzone.classList.add('border-dashed', 'border-slate-300');
                }
                if (fileInput) fileInput.value = '';
            }
        });
    }

    function showDocError(docType, message) {
        const cards = document.querySelectorAll(`[data-doc-type="${docType}"]`);
        cards.forEach(card => {
            const errEl = card.querySelector('.doc-error-state');
            const dropzone = card.querySelector('.file-dropzone');
            if (errEl) {
                errEl.textContent = message;
                errEl.classList.remove('hidden');
            }
            if (dropzone) {
                dropzone.classList.add('border-rose-500', 'has-error', 'has-error-shake');
            }
        });
    }

    function clearDocError(docType) {
        const cards = document.querySelectorAll(`[data-doc-type="${docType}"]`);
        cards.forEach(card => {
            const errEl = card.querySelector('.doc-error-state');
            const dropzone = card.querySelector('.file-dropzone');
            if (errEl) errEl.classList.add('hidden');
            if (dropzone) dropzone.classList.remove('border-rose-500', 'has-error', 'has-error-shake');
        });
    }

    function clearDocValidationAlert() {
        const alertBox = document.getElementById('document-validation-alert');
        if (alertBox) alertBox.classList.add('hidden');
    }

    function syncHiddenInputs() {
        const container = document.getElementById('uploaded-document-tokens-container');
        if (!container) return;

        let html = '';
        for (const [type, data] of Object.entries(uploadedDocuments)) {
            if (data && data.token) {
                html += `<input type="hidden" name="document_tokens[]" value="${data.token}">\n`;
                html += `<input type="hidden" name="document_types[]" value="${type}">\n`;
            }
        }
        container.innerHTML = html;
    }

    // ==============================================================
    // Validasi Formulir Wizard Antar Langkah
    // ==============================================================
    function validateCurrentStep() {
        const pane = document.getElementById(`step-pane-${currentStep}`);
        const inputs = pane.querySelectorAll('input, select, textarea');
        let isValid = true;
        let firstInvalid = null;

        inputs.forEach(input => {
            // Abaikan input pada elemen kontainer yang tersembunyi
            if (input.closest('.hidden')) {
                return;
            }

            if (!input.checkValidity()) {
                isValid = false;
                if (!firstInvalid) {
                    firstInvalid = input;
                }
                input.classList.add('border-rose-500', 'has-error', 'has-error-shake');
                input.classList.remove('is-valid');
            } else {
                input.classList.remove('border-rose-500', 'has-error', 'has-error-shake');
                if (input.value && input.value.trim() !== '') {
                    input.classList.add('is-valid');
                }
            }
        });

        // Validasi khusus NIK vs Identity Number pada Step 2
        if (currentStep === 2) {
            const nik = document.getElementById('nik').value.trim();
            const idNum = document.getElementById('identity_number').value.trim();
            if (!nik && !idNum) {
                isValid = false;
                document.getElementById('nik').classList.add('border-rose-500', 'has-error', 'has-error-shake');
                document.getElementById('identity_number').classList.add('border-rose-500', 'has-error', 'has-error-shake');
                document.getElementById('nik').classList.remove('is-valid');
                document.getElementById('identity_number').classList.remove('is-valid');
                if (!firstInvalid) {
                    firstInvalid = document.getElementById('nik');
                }
                alert('Peringatan: Minimal salah satu dari NIK (16 digit) atau Nomor Identitas Lainnya wajib diisi.');
            } else if (nik && !/^[0-9]{16}$/.test(nik)) {
                isValid = false;
                document.getElementById('nik').classList.add('border-rose-500', 'has-error', 'has-error-shake');
                document.getElementById('nik').classList.remove('is-valid');
                if (!firstInvalid) {
                    firstInvalid = document.getElementById('nik');
                }
                alert('Peringatan: Format NIK harus terdiri dari tepat 16 digit angka.');
            }
        }

        // Validasi kelengkapan dokumen pada Step 5 (Kategori A vs B)
        if (currentStep === 5) {
            const category = getApplicantCategory();
            const uploadedTypes = Object.keys(uploadedDocuments);
            const errors = [];

            if (category === 'A') {
                if (!uploadedTypes.includes('ktp_pemohon')) {
                    errors.push('KTP Elektronik Pemohon wajib diunggah.');
                }
                if (!uploadedTypes.includes('kartu_keluarga')) {
                    errors.push('Kartu Keluarga (KK) wajib diunggah.');
                }
                const hasSupporting = uploadedTypes.includes('akta_kelahiran') || 
                                      uploadedTypes.includes('buku_nikah_pemohon') || 
                                      uploadedTypes.includes('paspor_lama');
                if (!hasSupporting) {
                    errors.push('Minimal salah satu dokumen bukti diri wajib diunggah: Akta Kelahiran, Buku Nikah Pemohon, atau Paspor Lama.');
                }
            } else {
                // Kategori B (Anak)
                if (!uploadedTypes.includes('kartu_keluarga')) {
                    errors.push('Kartu Keluarga wajib diunggah.');
                }
                if (!uploadedTypes.includes('akta_kelahiran')) {
                    errors.push('Akta Kelahiran Anak wajib diunggah.');
                }
                const hasParentId = uploadedTypes.includes('ktp_ayah') || 
                                    uploadedTypes.includes('ktp_ibu') || 
                                    uploadedTypes.includes('ktp_wali') || 
                                    uploadedTypes.includes('akta_kematian_orang_tua');
                if (!hasParentId) {
                    errors.push('Identitas Orang Tua / Wali wajib diunggah (KTP Ayah, KTP Ibu, KTP Wali, atau Akta Kematian).');
                }
                const isGuardianship = uploadedTypes.includes('ktp_wali') || uploadedTypes.includes('penetapan_pengadilan_wali');
                if (isGuardianship) {
                    if (!uploadedTypes.includes('penetapan_pengadilan_wali')) {
                        errors.push('Permohonan perwalian anak wajib melampirkan Penetapan Pengadilan tentang Perwalian.');
                    }
                    if (!uploadedTypes.includes('ktp_wali')) {
                        errors.push('Permohonan perwalian anak wajib melampirkan KTP Wali.');
                    }
                } else {
                    const hasMarriageOrLegal = uploadedTypes.includes('buku_nikah_orang_tua') || 
                                               uploadedTypes.includes('akta_cerai_orang_tua') || 
                                               uploadedTypes.includes('putusan_hak_asuh') || 
                                               uploadedTypes.includes('akta_kematian_orang_tua');
                    if (!hasMarriageOrLegal) {
                        errors.push('Dokumen legalitas hubungan orang tua (Buku Nikah / Akta Cerai / Hak Asuh / Akta Kematian) wajib diunggah.');
                    }
                }
            }

            const alertBox = document.getElementById('document-validation-alert');
            const listEl = document.getElementById('document-validation-list');
            if (errors.length > 0) {
                isValid = false;
                if (listEl) {
                    listEl.innerHTML = errors.map(err => `<li>${err}</li>`).join('');
                }
                if (alertBox) {
                    alertBox.classList.remove('hidden');
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            } else {
                if (alertBox) alertBox.classList.add('hidden');
            }
        }

        // Validasi khusus Pernyataan Integritas pada Step 6
        if (currentStep === 6) {
            const decl = document.getElementById('declaration_accepted');
            if (!decl.checked) {
                isValid = false;
                decl.classList.add('outline-rose-500');
                if (!firstInvalid) firstInvalid = decl;
                alert('Peringatan: Anda harus mencentang dan menyetujui pernyataan integritas dan keabsahan data sebelum mengirim formulir.');
            }
        }

        if (!isValid && firstInvalid) {
            firstInvalid.reportValidity();
            firstInvalid.focus();
        }

        return isValid;
    }

    function nextStep() {
        if (!validateCurrentStep()) {
            return;
        }

        if (currentStep < totalSteps) {
            currentStep++;
            if (currentStep === 6) {
                populatePreview();
            }
            updateStepView();
            window.scrollTo({ top: 120, behavior: 'smooth' });
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepView();
            window.scrollTo({ top: 120, behavior: 'smooth' });
        }
    }

    function goToStep(stepNumber) {
        if (stepNumber < currentStep) {
            currentStep = stepNumber;
            updateStepView();
            window.scrollTo({ top: 120, behavior: 'smooth' });
        } else if (stepNumber > currentStep) {
            if (validateCurrentStep()) {
                currentStep = stepNumber;
                if (currentStep === 6) {
                    populatePreview();
                }
                updateStepView();
                window.scrollTo({ top: 120, behavior: 'smooth' });
            }
        }
    }

    function updateStepView() {
        // Tampilkan/sembunyikan panel langkah
        for (let i = 1; i <= totalSteps; i++) {
            const pane = document.getElementById(`step-pane-${i}`);
            const navBtn = document.getElementById(`step-nav-${i}`);
            if (!pane || !navBtn) continue;
            
            if (i === currentStep) {
                pane.classList.remove('hidden');
                navBtn.classList.remove('opacity-70');
                navBtn.classList.add('border-[#1B365D]', 'bg-[#12243E]', 'text-white');
                navBtn.querySelector('.step-num').classList.remove('text-slate-400', 'text-emerald-700');
                navBtn.querySelector('.step-num').classList.add('text-amber-300');
                navBtn.querySelector('.step-title').classList.remove('text-slate-800');
                navBtn.querySelector('.step-title').classList.add('text-white');
            } else if (i < currentStep) {
                pane.classList.add('hidden');
                navBtn.classList.remove('opacity-70', 'border-[#1B365D]', 'bg-[#12243E]', 'text-white');
                navBtn.classList.add('border-emerald-500', 'bg-emerald-50/50');
                navBtn.querySelector('.step-num').classList.remove('text-amber-300', 'text-slate-400');
                navBtn.querySelector('.step-num').classList.add('text-emerald-700');
                navBtn.querySelector('.step-title').classList.remove('text-white');
                navBtn.querySelector('.step-title').classList.add('text-slate-800');
            } else {
                pane.classList.add('hidden');
                navBtn.classList.remove('border-[#1B365D]', 'bg-[#12243E]', 'border-emerald-500', 'bg-emerald-50/50');
                navBtn.classList.add('opacity-70', 'border-slate-200');
                navBtn.querySelector('.step-num').classList.remove('text-amber-300', 'text-emerald-700');
                navBtn.querySelector('.step-num').classList.add('text-slate-400');
                navBtn.querySelector('.step-title').classList.remove('text-white');
                navBtn.querySelector('.step-title').classList.add('text-slate-800');
            }
        }

        // Progress bar width (berdasarkan 6 langkah)
        const pct = (currentStep / totalSteps) * 100;
        document.getElementById('progress-bar-fill').style.width = `${pct}%`;

        // Update tombol navigasi
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');

        if (currentStep === 1) {
            prevBtn.classList.add('hidden');
            document.getElementById('prev-placeholder').classList.remove('hidden');
        } else {
            prevBtn.classList.remove('hidden');
            document.getElementById('prev-placeholder').classList.add('hidden');
        }

        if (currentStep === totalSteps) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }
    }

    // ==============================================================
    // Pengisian Data Ringkasan Pratinjau (Langkah 6)
    // ==============================================================
    function populatePreview() {
        const getText = (id) => {
            const el = document.getElementById(id);
            if (!el) return '-';
            if (el.tagName === 'SELECT') {
                return el.options[el.selectedIndex]?.text || '-';
            }
            return el.value.trim() || '-';
        };

        // Seksi 1
        document.getElementById('review-app-type').textContent = getText('application_type');
        document.getElementById('review-passport-type').textContent = getText('passport_type');

        const appType = document.getElementById('application_type').value;
        const replReview = document.getElementById('review-replacement-container');
        const changeReview = document.getElementById('review-change-container');

        if (appType === 'penggantian') {
            replReview.classList.remove('hidden');
            document.getElementById('review-replacement-reason').textContent = getText('replacement_reason');
        } else {
            replReview.classList.add('hidden');
        }

        if (appType === 'perubahan') {
            changeReview.classList.remove('hidden');
            document.getElementById('review-change-reason').textContent = getText('change_reason');
        } else {
            changeReview.classList.add('hidden');
        }

        // Seksi 2
        document.getElementById('review-full-name').textContent = getText('full_name');
        document.getElementById('review-gender').textContent = getText('gender');
        document.getElementById('review-ttl').textContent = `${getText('birth_place')}, ${getText('birth_date')}`;
        document.getElementById('review-height').textContent = `${getText('height_cm')} cm`;
        
        const nikVal = document.getElementById('nik').value.trim();
        const idVal = document.getElementById('identity_number').value.trim();
        document.getElementById('review-nik-id').textContent = nikVal ? `NIK: ${nikVal}` : `Nomor ID: ${idVal}`;

        document.getElementById('review-email').textContent = getText('email');
        document.getElementById('review-phone').textContent = getText('indonesia_phone');
        document.getElementById('review-marital').textContent = getText('marital_status');
        document.getElementById('review-address').textContent = getText('indonesia_address');

        // Seksi 3
        document.getElementById('review-mother-name').textContent = getText('mother_name');
        document.getElementById('review-mother-nat').textContent = getText('mother_nationality');
        document.getElementById('review-father-name').textContent = getText('father_name');
        document.getElementById('review-father-phone').textContent = getText('father_phone');

        const marital = document.getElementById('marital_status').value;
        const spouseRow = document.getElementById('review-spouse-row');
        const category = getApplicantCategory();
        if (category === 'A' && marital === 'kawin') {
            spouseRow.classList.remove('hidden');
            document.getElementById('review-spouse-name').textContent = `${getText('spouse_name')} (${getText('spouse_nationality')})`;
        } else {
            spouseRow.classList.add('hidden');
        }

        // Seksi 4
        document.getElementById('review-emergency-name').textContent = getText('emergency_id_name');
        document.getElementById('review-emergency-rel').textContent = getText('emergency_id_relationship');
        document.getElementById('review-emergency-phone').textContent = getText('emergency_id_phone');

        // Seksi 5: Ringkasan Dokumen Terunggah
        const reviewDocsList = document.getElementById('review-documents-list');
        if (reviewDocsList) {
            const docEntries = Object.entries(uploadedDocuments);
            if (docEntries.length === 0) {
                reviewDocsList.innerHTML = '<p class="text-slate-400 italic">Belum ada dokumen yang diunggah.</p>';
            } else {
                reviewDocsList.innerHTML = docEntries.map(([type, data]) => {
                    const label = documentTypeLabels[type] || type;
                    const sizeKb = data.file_size ? (data.file_size / 1024).toFixed(1) + ' KB' : '';
                    return `
                        <div class="flex items-center justify-between p-2.5 rounded bg-white border border-slate-200">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <span class="text-emerald-700 font-bold">✓</span>
                                <div class="truncate">
                                    <span class="font-bold text-slate-800 block truncate">${label}</span>
                                    <span class="text-[11px] text-slate-500 font-mono">${data.original_name || '-'} ${sizeKb ? `(${sizeKb})` : ''}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                                Terverifikasi
                            </span>
                        </div>
                    `;
                }).join('');
            }
        }
    }

    // Micro-interactions pada blur dan input field
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('perdim-wizard-form');
        if (!form) return;

        form.querySelectorAll('input, select, textarea').forEach(el => {
            el.addEventListener('blur', () => {
                if (el.closest('.hidden')) return;
                if (el.value && el.value.trim() !== '') {
                    if (el.checkValidity()) {
                        el.classList.remove('has-error', 'border-rose-500', 'has-error-shake');
                        el.classList.add('is-valid');
                    } else {
                        el.classList.remove('is-valid');
                        el.classList.add('has-error', 'border-rose-500', 'has-error-shake');
                    }
                } else if (!el.required) {
                    el.classList.remove('has-error', 'border-rose-500', 'has-error-shake', 'is-valid');
                }
            });

            el.addEventListener('input', () => {
                if (el.classList.contains('has-error') || el.classList.contains('border-rose-500')) {
                    if (el.checkValidity()) {
                        el.classList.remove('has-error', 'border-rose-500', 'has-error-shake');
                    }
                }
            });
        });
    });
</script>
@endsection
