<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('perdim_documents', function (Blueprint $table) {
            $table->id();

            // Relasi ke perdim_applications dengan cascade delete
            $table->foreignId('perdim_application_id')
                ->constrained('perdim_applications')
                ->cascadeOnDelete();

            $table->string('document_type', 50)->index();
            $table->string('original_name', 255);
            $table->string('file_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('hash_sha256', 64);

            $table->timestamps();

            $table->index(['perdim_application_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perdim_documents');
    }
};
