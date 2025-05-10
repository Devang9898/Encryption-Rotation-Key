<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // The 'getDecryptedPayload()' method should exist on your Prescription model
        // and return the decrypted prescription details.
        $decryptedDetails = $this->getDecryptedPayload();

        return [
            'id' => $this->id,
            'uuid' => $this->uuid, // For the verification link
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'medication_id' => $this->medication_id,
            'prescription_date' => $this->prescription_date->toIso8601String(),
            'is_filled' => (bool) $this->is_filled,

            // Display decrypted details if available
            'patient_name' => $decryptedDetails['patient_name'] ?? null,
            'medication_name' => $decryptedDetails['medication_name'] ?? null,
            'dosage' => $decryptedDetails['dosage'] ?? null,
            'instructions' => $decryptedDetails['instructions'] ?? null,
            'doctor_name' => $decryptedDetails['doctor_name'] ?? null,
            'prescribed_at_payload' => $decryptedDetails['prescribed_at'] ?? null, // From payload

            'verification_url' => route('prescription.verify', ['uuid' => $this->uuid]),
            // 'qr_code_png_base64' => $this->when($this->qr_code_data, base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(200)->generate(route('prescription.verify', ['uuid' => $this->uuid])))),

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}