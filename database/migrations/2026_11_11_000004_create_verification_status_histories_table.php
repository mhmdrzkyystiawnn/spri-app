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
        Schema::create('verification_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained('verifications')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable()->index();
            $table->string('to_status', 30)->index();
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->index(['verification_id', 'changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_status_histories');
    }
};
