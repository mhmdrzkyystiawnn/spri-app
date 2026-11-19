<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->nullable()->default(UserRole::ADMIN->value)->index()->after('is_admin');
            $table->boolean('is_active')->default(true)->after('role');
        });

        // Backfill berdasarkan kolom is_admin yang sudah ada
        DB::table('users')->where('is_admin', true)->update(['role' => UserRole::ADMIN->value]);
        DB::table('users')->where('is_admin', false)->update(['role' => UserRole::VERIFIKATOR->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
