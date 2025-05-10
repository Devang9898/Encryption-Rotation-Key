<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientResource; // Import the resource
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse; // For type hinting

class PatientController extends Controller
{
    /**
     * Display a listing of the authenticated doctor's patients.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        // Get the currently authenticated user (doctor)
        $doctor = Auth::user();

        // Retrieve patients associated with this doctor, optionally paginate
        $patients = $doctor->patients()->latest()->paginate(15); // Example pagination

        // Return a collection of Patient resources
        return PatientResource::collection($patients);
    }

    /**
     * Store a newly created patient in storage for the authenticated doctor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\PatientResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request): PatientResource|JsonResponse
    {
        $doctor = Auth::user();

        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'dob' => 'required|date_format:Y-m-d', // Expect YYYY-MM-DD format
            'contact_info' => 'nullable|string|max:255',
            // 'doctor_id' is set automatically, no need to validate here unless allowing override
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        // Prepare data for creation, including the doctor's ID
        $patientData = $validator->validated();
        $patientData['doctor_id'] = $doctor->id;

        // Create the patient record
        try {
            $patient = Patient::create($patientData);

            // Return the newly created patient resource with a 201 status code
            return (new PatientResource($patient))
                    ->response()
                    ->setStatusCode(201); // Created
        } catch (\Exception $e) {
            report($e); // Log the exception
            return response()->json(['message' => 'Failed to create patient.'], 500); // Internal Server Error
        }
    }

    /**
     * Display the specified patient, ensuring it belongs to the authenticated doctor.
     *
     * @param  string  $id The ID of the patient.
     * @return \App\Http\Resources\PatientResource|\Illuminate\Http\JsonResponse
     */
    public function show(string $id): PatientResource|JsonResponse
    {
        $doctor = Auth::user();

        // Find the patient by ID *only* if it belongs to the authenticated doctor
        $patient = $doctor->patients()->find($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404); // Not Found
        }

        // Return the single patient resource
        return new PatientResource($patient);
    }

    /**
     * Update the specified patient in storage, ensuring it belongs to the authenticated doctor.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id The ID of the patient.
     * @return \App\Http\Resources\PatientResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id): PatientResource|JsonResponse
    {
        $doctor = Auth::user();

        // Find the patient belonging to the doctor
        $patient = $doctor->patients()->find($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404); // Not Found
        }

        // Validate the incoming request data (allow partial updates)
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255', // 'sometimes' means validate only if present
            'dob' => 'sometimes|required|date_format:Y-m-d',
            'contact_info' => 'nullable|string|max:255',
            // Do not allow updating 'doctor_id' via this endpoint generally
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        // Update the patient record
        try {
            $patient->update($validator->validated());

            // Return the updated patient resource
            return new PatientResource($patient);
        } catch (\Exception $e) {
            report($e); // Log the exception
            return response()->json(['message' => 'Failed to update patient.'], 500); // Internal Server Error
        }
    }

    /**
     * Remove the specified patient from storage, ensuring it belongs to the authenticated doctor.
     *
     * @param  string  $id The ID of the patient.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $doctor = Auth::user();

        // Find the patient belonging to the doctor
        $patient = $doctor->patients()->find($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404); // Not Found
        }

        // Delete the patient record
        try {
            $patient->delete();

            // Return a success response with no content
            return response()->json(null, 204); // No Content
        } catch (\Exception $e) {
             // Handle potential foreign key constraints or other deletion issues if necessary
            report($e); // Log the exception
            return response()->json(['message' => 'Failed to delete patient.'], 500); // Internal Server Error
        }
    }
}