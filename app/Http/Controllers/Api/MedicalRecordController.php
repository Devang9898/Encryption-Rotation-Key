<?php

// CORRECT NAMESPACE DECLARATION:
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicalRecordResource; // Import resource
use App\Models\MedicalRecord;
use App\Models\Patient; // Import Patient model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse; // For type hinting

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of the medical records for a specific patient.
     * Ensures the patient belongs to the authenticated doctor.
     *
     * @param  \App\Models\Patient $patient (Route Model Binding)
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Patient $patient): \Illuminate\Http\Resources\Json\AnonymousResourceCollection|JsonResponse
    {
        $doctor = Auth::user();

        // Authorization: Check if the patient belongs to the authenticated doctor
        if ($patient->doctor_id !== $doctor->id) {
            return response()->json(['message' => 'Unauthorized access to patient records.'], 403); // Forbidden
        }

        // Get records for this patient, ordered by date descending
        $medicalRecords = $patient->medicalRecords()->latest('record_date')->paginate(15);

        return MedicalRecordResource::collection($medicalRecords);
    }

    /**
     * Store a newly created medical record for a specific patient.
     * Ensures the patient belongs to the authenticated doctor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Patient $patient (Route Model Binding)
     * @return \App\Http\Resources\MedicalRecordResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Patient $patient): MedicalRecordResource|JsonResponse
    {
        $doctor = Auth::user();

        // Authorization: Check if the patient belongs to the authenticated doctor
        if ($patient->doctor_id !== $doctor->id) {
            return response()->json(['message' => 'Unauthorized to add records for this patient.'], 403); // Forbidden
        }

        $validator = Validator::make($request->all(), [
            'diagnosis' => 'required|string|max:65535', // Max text length
            'notes' => 'nullable|string|max:65535',
            'record_date' => 'nullable|date_format:Y-m-d H:i:s', // Optional custom date
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        try {
            // Create the record, associating with patient and doctor
            // The model's mutator will automatically handle encryption for 'diagnosis' and 'notes'
            $medicalRecord = $patient->medicalRecords()->create([
                'doctor_id' => $doctor->id,
                'diagnosis' => $request->input('diagnosis'), // Mutator encrypts
                'notes' => $request->input('notes'),         // Mutator encrypts
                'record_date' => $request->input('record_date', now()), // Use provided date or now
            ]);

            return (new MedicalRecordResource($medicalRecord->fresh())) // Load fresh data if needed
                   ->response()
                   ->setStatusCode(201); // Created

        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'Failed to create medical record.'], 500);
        }
    }

    /**
     * Display the specified medical record for a specific patient.
     * Ensures the patient belongs to the authenticated doctor.
     *
     * @param  \App\Models\Patient $patient (Route Model Binding)
     * @param  \App\Models\MedicalRecord $medicalRecord (Route Model Binding)
     * @return \App\Http\Resources\MedicalRecordResource|\Illuminate\Http\JsonResponse
     */
    public function show(Patient $patient, MedicalRecord $medicalRecord): MedicalRecordResource|JsonResponse
    {
        $doctor = Auth::user();

        // Authorization: Check if the patient belongs to the doctor AND the record belongs to the patient
        if ($patient->doctor_id !== $doctor->id || $medicalRecord->patient_id !== $patient->id) {
            return response()->json(['message' => 'Unauthorized access to medical record.'], 403); // Forbidden
        }

        // Return the single resource (accessor decrypts fields)
        return new MedicalRecordResource($medicalRecord);
    }

    // Optional: Implement update and destroy methods if needed, following similar
    // authorization patterns (check doctor owns patient, record belongs to patient).
    // Remember that updating encrypted fields will trigger the model's mutator
    // to re-encrypt the new value.

    /**
     * Update the specified medical record.
     * (Example - Implement if needed)
     */
    // public function update(Request $request, Patient $patient, MedicalRecord $medicalRecord)
    // {
    //     $doctor = Auth::user();
    //     if ($patient->doctor_id !== $doctor->id || $medicalRecord->patient_id !== $patient->id) {
    //         return response()->json(['message' => 'Unauthorized'], 403);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'diagnosis' => 'sometimes|required|string|max:65535',
    //         'notes' => 'nullable|string|max:65535',
    //         'record_date' => 'sometimes|required|date_format:Y-m-d H:i:s',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     try {
    //         $medicalRecord->update($validator->validated()); // Mutators handle re-encryption
    //         return new MedicalRecordResource($medicalRecord);
    //     } catch (\Exception $e) {
    //         report($e);
    //         return response()->json(['message' => 'Failed to update record.'], 500);
    //     }
    // }

    /**
     * Remove the specified medical record.
     * (Example - Implement if needed)
     */
    // public function destroy(Patient $patient, MedicalRecord $medicalRecord)
    // {
    //     $doctor = Auth::user();
    //     if ($patient->doctor_id !== $doctor->id || $medicalRecord->patient_id !== $patient->id) {
    //         return response()->json(['message' => 'Unauthorized'], 403);
    //     }

    //     try {
    //         $medicalRecord->delete();
    //         return response()->json(null, 204); // No Content
    //     } catch (\Exception $e) {
    //         report($e);
    //         return response()->json(['message' => 'Failed to delete record.'], 500);
    //     }
    // }
}