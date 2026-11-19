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
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->string('registration_code', 32)->unique()->index();
            $table->string('applicant_name', 150);
            $table->string('document_type', 80);
            $table->string('status', 30)->default('pending')->index();
            $table->text('public_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('created_by')->nullable()->nullOnDelete()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
