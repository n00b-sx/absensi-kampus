<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->enum('role', ['admin','panitia','peserta'])->default('peserta');
            $t->enum('category', ['mahasiswa','dosen','tendik','umum'])->nullable();
            $t->enum('identity_type', ['NIM','NIP','NIK'])->nullable();
            $t->string('identity_number')->nullable()->unique();
            $t->foreignId('study_program_id')
              ->nullable()
              ->constrained('study_programs')
              ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('study_program_id');
            $t->dropColumn(['role','category','identity_type','identity_number']);
        });
    }
};