<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request); // Default

        // Customize the output
        return [
            'id' => $this->id,
            'name' => $this->name,
            'dob' => $this->dob ? $this->dob->format('Y-m-d') : null, // Format date
            'contact_info' => $this->contact_info,
            'doctor_id' => $this->doctor_id, // Include doctor ID
            // Optionally include doctor name if loaded:
            // 'doctor_name' => $this->whenLoaded('doctor', fn() => $this->doctor->name),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}