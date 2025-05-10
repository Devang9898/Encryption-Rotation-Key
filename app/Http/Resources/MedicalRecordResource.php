<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        // Note: Accessing diagnosis/notes here will trigger the model's accessor
        // which performs the decryption automatically.
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'record_date' => $this->record_date ? $this->record_date->toIso8601String() : $this->created_at->toIso8601String(),
            'diagnosis' => $this->diagnosis, // Accessor decrypts
            'notes' => $this->notes,         // Accessor decrypts
            // Include doctor/patient names if needed and loaded
            // 'doctor_name' => $this->whenLoaded('doctor', fn() => $this->doctor->name),
            // 'patient_name' => $this->whenLoaded('patient', fn() => $this->patient->name),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}