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
        Schema::create('verification_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained('verifications')->cascadeOnDelete();
            $table->foreignId('document_requirement_id')->constrained('document_requirements')->restrictOnDelete();
            $table->boolean('is_checked')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['verification_id', 'document_requirement_id']);
            $table->index(['verification_id', 'is_checked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_checklists');
    }
};
