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
       // database/migrations/xxxx_create_medical_records_table.php
    Schema::create('medical_records', function (Blueprint $table) {
        $table->id();
        $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
        $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade'); // Record who added it
        $table->timestamp('record_date')->useCurrent();
        $table->text('diagnosis_encrypted'); // Store encrypted data
        $table->text('notes_encrypted');     // Store encrypted data
        // Add field for key version if rotating keys for these fields too
        // $table->unsignedInteger('encryption_key_version')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
