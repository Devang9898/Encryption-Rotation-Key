<?php

// database/migrations/xxxx_create_prescriptions_table.php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up() {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // For secure link access
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade'); // Prescribing doctor
            $table->foreignId('medication_id')->constrained('medications')->onDelete('restrict'); // Don't delete med if prescribed
            $table->text('encrypted_payload'); // Encrypted JSON/data string
            $table->unsignedInteger('encryption_key_version'); // Key version used
            $table->timestamp('prescription_date')->useCurrent();
            $table->boolean('is_filled')->default(false); // Optional status
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('prescriptions'); }
};