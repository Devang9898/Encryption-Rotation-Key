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
        // database/migrations/xxxx_create_patients_table.php
    Schema::create('patients', function (Blueprint $table) {
        $table->id();
        $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('set null'); // Or restrict based on requirements
        $table->string('name');
        $table->date('dob'); // Consider encrypting if sensitive
        $table->string('contact_info')->nullable();
        // Add other relevant fields
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
