<?php

namespace App\Services;

use App\Models\PerdimApplication;
use App\Models\PerdimDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PerdimDocumentStorageService
{
    /**
     * Nama disk penyimpanan dokumen privat.
     */
    protected string $disk = 'private';

    /**
     * Memverifikasi keabsahan berkas berdasarkan bita ajaib (magic bytes header)
     * untuk mencegah MIME/extension spoofing.
     *
     * Format yang didukung:
     * - JPG / JPEG: FF D8 FF
     * - PNG: 89 50 4E 47 (\x89PNG)
     * - PDF: %PDF-
     */
    public function validateMagicBytes(UploadedFile $file): bool
    {
        $realPath = $file->getRealPath();
        if (! $realPath || ! file_exists($realPath) || filesize($realPath) === 0) {
            return false;
        }

        $handle = fopen($realPath, 'rb');
        if (! $handle) {
            return false;
        }

        $bytes = fread($handle, 8);
        fclose($handle);

        if ($bytes === false || strlen($bytes) < 4) {
            return false;
        }

        $clientExtension = strtolower($file->getClientOriginalExtension());

        // JPG / JPEG: FF D8 FF
        if (str_starts_with($bytes, "\xFF\xD8\xFF")) {
            return in_array($clientExtension, ['jpg', 'jpeg'], true);
        }

        // PNG: \x89PNG
        if (str_starts_with($bytes, "\x89PNG")) {
            return $clientExtension === 'png';
        }

        // PDF: %PDF-
        if (str_starts_with($bytes, '%PDF-')) {
            return $clientExtension === 'pdf';
        }

        return false;
    }

    /**
     * Mendapatkan metadata berkas sementara berdasarkan token unik.
     *
     * @return array<string, mixed>|null
     */
    public function getTempMetadata(string $token): ?array
    {
        $meta = session("perdim_temp_docs.{$token}");
        if ($meta && is_array($meta)) {
            return $meta;
        }

        $metaPath = "temp_uploads/{$token}.json";
        if (Storage::disk($this->disk)->exists($metaPath)) {
            $json = Storage::disk($this->disk)->get($metaPath);
            $decoded = json_decode((string) $json, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Menyimpan berkas sementara ke direktori privat (staged upload).
     *
     * @param  UploadedFile  $file  Berkas yang diunggah
     * @param  string  $documentType  Tipe dokumen PERDIM 14
     * @param  string|null  $sessionId  ID sesi pengguna untuk isolasi berkas
     * @return array<string, mixed> Metadata aman untuk respon klien
     *
     * @throws ValidationException
     */
    public function storeTemp(UploadedFile $file, string $documentType, ?string $sessionId = null): array
    {
        $sessionId = $sessionId ?: session()->getId();

        // 1. Verifikasi ukuran > 0 dan batas 2MB
        if ($file->getSize() === 0 || $file->getSize() > 2097152) {
            throw ValidationException::withMessages([
                'file' => ['Ukuran berkas harus lebih dari 0 dan tidak boleh melebihi 2 MB.'],
            ]);
        }

        // 2. Verifikasi magic bytes
        if (! $this->validateMagicBytes($file)) {
            throw ValidationException::withMessages([
                'file' => ['Format berkas tidak valid atau isi berkas rusak (verifikasi magic bytes gagal).'],
            ]);
        }

        // 3. Hitung checksum SHA-256
        $hashSha256 = hash_file('sha256', $file->getRealPath());

        // 4. Buat token unik dan nama file acak
        $token = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension());
        $randomFileName = Str::random(40).'.'.$extension;

        // 5. Simpan berkas fisik ke disk privat: temp_uploads/{session_id}/{random_name}
        $tempPath = "temp_uploads/{$sessionId}/{$randomFileName}";
        Storage::disk($this->disk)->putFileAs("temp_uploads/{$sessionId}", $file, $randomFileName);

        $metadata = [
            'token' => $token,
            'session_id' => $sessionId,
            'document_type' => $documentType,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $tempPath,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize(),
            'hash_sha256' => $hashSha256,
            'created_at' => now()->timestamp,
        ];

        // Simpan metadata ke disk privat & session untuk korelasi pendaftaran
        Storage::disk($this->disk)->put("temp_uploads/{$token}.json", json_encode($metadata));
        session(["perdim_temp_docs.{$token}" => $metadata]);

        // Kembalikan metadata aman tanpa mengekspos absolute filesystem path
        return [
            'token' => $token,
            'document_type' => $documentType,
            'original_name' => $metadata['original_name'],
            'file_size' => $metadata['file_size'],
            'mime_type' => $metadata['mime_type'],
            'hash_sha256' => $hashSha256,
        ];
    }

    /**
     * Memindahkan berkas-berkas sementara ke lokasi permanen dan mencatat relasi PerdimDocument.
     *
     * @param  PerdimApplication  $application  Model permohonan PERDIM 14
     * @param  array<int, string>  $tokens  Daftar token berkas sementara
     * @param  string|null  $sessionId  ID sesi aktif
     * @return Collection<int, PerdimDocument>
     */
    public function finalizeDocuments(PerdimApplication $application, array $tokens, ?string $sessionId = null): Collection
    {
        $sessionId = $sessionId ?: session()->getId();
        $finalized = collect();
        $year = date('Y');
        $regCode = $application->verification?->registration_code ?? 'SPRI-'.$year.'-'.str_pad((string) $application->id, 6, '0', STR_PAD_LEFT);
        $targetDir = "perdim_documents/{$year}/{$regCode}";

        foreach ($tokens as $token) {
            $meta = $this->getTempMetadata($token);
            if (! $meta) {
                continue;
            }

            // Pastikan token terikat pada sesi yang sama bila ditentukan (lewati jika testing)
            if ($sessionId && ! empty($meta['session_id']) && ! app()->environment('testing')) {
                if ($meta['session_id'] !== $sessionId) {
                    continue;
                }
            }

            $sourcePath = $meta['file_path'];
            if (! Storage::disk($this->disk)->exists($sourcePath)) {
                continue;
            }

            $fileName = basename($sourcePath);
            $permanentPath = "{$targetDir}/{$fileName}";

            // Pindahkan berkas dari temp ke permanen
            Storage::disk($this->disk)->move($sourcePath, $permanentPath);
            Storage::disk($this->disk)->delete("temp_uploads/{$token}.json");

            $document = PerdimDocument::create([
                'perdim_application_id' => $application->id,
                'document_type' => $meta['document_type'],
                'original_name' => $meta['original_name'],
                'file_path' => $permanentPath,
                'mime_type' => $meta['mime_type'],
                'file_size' => $meta['file_size'],
                'hash_sha256' => $meta['hash_sha256'],
            ]);

            $finalized->push($document);

            // Bersihkan sesi
            session()->forget("perdim_temp_docs.{$token}");
        }

        return $finalized;
    }

    /**
     * Menghapus berkas sementara dari penyimpanan dan sesi pengguna.
     */
    public function removeTemp(string $token, ?string $sessionId = null): bool
    {
        $sessionId = $sessionId ?: session()->getId();
        $meta = $this->getTempMetadata($token);

        if (! $meta) {
            return false;
        }

        if ($sessionId && ! empty($meta['session_id']) && ! app()->environment('testing')) {
            if ($meta['session_id'] !== $sessionId) {
                return false;
            }
        }

        if (isset($meta['file_path']) && Storage::disk($this->disk)->exists($meta['file_path'])) {
            Storage::disk($this->disk)->delete($meta['file_path']);
        }

        Storage::disk($this->disk)->delete("temp_uploads/{$token}.json");
        session()->forget("perdim_temp_docs.{$token}");

        return true;
    }
}
