<!-- Jenis Dokumen SPRI -->
<div>
    <label for="document_type" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
        Jenis Permohonan SPRI <span class="text-red-700">*</span>
    </label>
    <select 
        id="document_type" 
        name="document_type" 
        required 
        class="editorial-input w-full text-sm bg-white @error('document_type') border-red-500 @enderror"
    >
        <option value="">Pilih Jenis Layanan SPRI...</option>
        @foreach ($documentTypes as $type)
            <option value="{{ $type }}" {{ old('document_type', $requirement->document_type) === $type ? 'selected' : '' }}>
                {{ $type }}
            </option>
        @endforeach
    </select>
    @error('document_type')
        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
    @enderror
</div>

<!-- Kode Persyaratan & Nomor Urut -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="code" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
            Kode Persyaratan <span class="text-red-700">*</span>
        </label>
        <input 
            type="text" 
            id="code" 
            name="code" 
            value="{{ old('code', $requirement->code) }}" 
            required 
            maxlength="50"
            class="editorial-input w-full text-sm font-mono uppercase @error('code') border-red-500 @enderror"
            placeholder="Misal: BARU-KTP, EL-KK"
        >
        <p class="mt-1 text-[11px] text-stone-500">Kombinasi unik huruf/angka bersama jenis dokumen.</p>
        @error('code')
            <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="sort_order" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
            Nomor Urut Tampilan <span class="text-stone-400 font-normal">(Opsional)</span>
        </label>
        <input 
            type="number" 
            id="sort_order" 
            name="sort_order" 
            value="{{ old('sort_order', $requirement->sort_order ?? 0) }}" 
            min="0" 
            max="9999"
            class="editorial-input w-full text-sm font-mono @error('sort_order') border-red-500 @enderror"
        >
        <p class="mt-1 text-[11px] text-stone-500">Urutan berkas saat ditampilkan pada checklist verifikator.</p>
        @error('sort_order')
            <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
        @enderror
    </div>
</div>

<!-- Nama Dokumen / Deskripsi Persyaratan -->
<div>
    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1">
        Nama Dokumen Persyaratan <span class="text-red-700">*</span>
    </label>
    <input 
        type="text" 
        id="name" 
        name="name" 
        value="{{ old('name', $requirement->name) }}" 
        required 
        maxlength="150"
        class="editorial-input w-full text-sm @error('name') border-red-500 @enderror"
        placeholder="Contoh: KTP Elektronik Asli / Surat Keterangan Perekaman"
    >
    @error('name')
        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
    @enderror
</div>

<!-- Sifat Dokumen & Status Aktif -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-stone-200">
    <div>
        <label class="flex items-start gap-2 cursor-pointer">
            <input 
                type="checkbox" 
                name="is_mandatory" 
                value="1" 
                {{ old('is_mandatory', $requirement->is_mandatory ? '1' : '0') === '1' ? 'checked' : '' }}
                class="rounded border-stone-300 text-stone-900 focus:ring-stone-900 h-4 w-4 mt-0.5"
            >
            <div>
                <span class="text-xs font-semibold text-stone-800 block">
                    Dokumen Wajib (Mandatori)
                </span>
                <span class="text-[11px] text-stone-500 block">
                    Jika dicentang, verifikasi berkas tidak dapat disetujui (Approved) sebelum dokumen ini terpenuhi.
                </span>
            </div>
        </label>
        @error('is_mandatory')
            <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="flex items-start gap-2 cursor-pointer">
            <input 
                type="checkbox" 
                name="is_active" 
                value="1" 
                {{ old('is_active', $requirement->is_active ? '1' : '0') === '1' ? 'checked' : '' }}
                class="rounded border-stone-300 text-stone-900 focus:ring-stone-900 h-4 w-4 mt-0.5"
            >
            <div>
                <span class="text-xs font-semibold text-stone-800 block">
                    Status Aktif Digunakan
                </span>
                <span class="text-[11px] text-stone-500 block">
                    Hanya persyaratan aktif yang otomatis di-attach pada permohonan baru.
                </span>
            </div>
        </label>
        @error('is_active')
            <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
        @enderror
    </div>
</div>
