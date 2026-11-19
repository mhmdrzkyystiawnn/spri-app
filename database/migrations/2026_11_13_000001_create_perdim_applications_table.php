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
        Schema::create('perdim_applications', function (Blueprint $table) {
            $table->id();

            // Relasi 1-to-1 ke tabel verifications (foreign key unique dengan cascade delete)
            $table->foreignId('verification_id')
                ->unique()
                ->constrained('verifications')
                ->cascadeOnDelete();

            // Seksi 1: Permohonan & SPRI
            $table->string('application_type', 30)->index();
            $table->string('passport_type', 40)->index();
            $table->string('replacement_reason', 40)->nullable();
            $table->string('change_reason', 40)->nullable();

            // Seksi 2: Identitas Diri Pemohon & Alamat
            $table->string('full_name', 255);
            $table->string('alias_name', 255)->nullable();
            $table->string('gender', 1);
            $table->unsignedSmallInteger('height_cm');
            $table->string('birth_place', 100);
            $table->date('birth_date')->index();
            $table->string('nik', 16)->nullable();
            $table->string('identity_number', 50)->nullable();
            $table->string('identity_issued_place', 100);
            $table->date('identity_issued_date');
            $table->date('identity_expiry_date')->nullable();
            $table->string('occupation', 100);
            $table->text('workplace_address')->nullable();
            $table->string('office_phone', 30)->nullable();
            $table->text('indonesia_address');
            $table->string('indonesia_phone', 30);
            $table->text('overseas_address')->nullable();
            $table->string('overseas_phone', 30)->nullable();
            $table->string('email', 150)->index();
            $table->string('marital_status', 30);

            // Seksi 3: Data Keluarga (Ibu, Ayah, Pasangan)
            $table->string('mother_name', 150);
            $table->string('mother_nationality', 50);
            $table->string('mother_birth_place', 100);
            $table->date('mother_birth_date');

            $table->string('father_name', 150);
            $table->string('father_nationality', 50);
            $table->string('father_birth_place', 100);
            $table->date('father_birth_date');
            $table->text('father_address');
            $table->string('father_phone', 30);

            $table->string('spouse_name', 150)->nullable();
            $table->string('spouse_nationality', 50)->nullable();
            $table->string('spouse_birth_place', 100)->nullable();
            $table->date('spouse_birth_date')->nullable();
            $table->text('spouse_address')->nullable();

            // Seksi 4: Perubahan Data, Kontak Darurat & Pernyataan
            $table->text('change_details')->nullable();
            $table->string('emergency_id_name', 150);
            $table->string('emergency_id_relationship', 50);
            $table->text('emergency_id_address');
            $table->string('emergency_id_phone', 30);

            $table->string('emergency_overseas_name', 150)->nullable();
            $table->text('emergency_overseas_address')->nullable();
            $table->string('emergency_overseas_phone', 30)->nullable();

            $table->boolean('declaration_accepted')->default(false);
            $table->timestamp('declaration_accepted_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perdim_applications');
    }
};
