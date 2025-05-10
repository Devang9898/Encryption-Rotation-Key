<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\Prescription;
// use App\Models\Patient;
// use App\Models\Medication;
// use App\Services\EncryptionService;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Validator;
// use SimpleSoftwareIO\QrCode\Facades\QrCode; // For QR Code

// class PrescriptionController extends Controller
// {
//     protected $encryptionService;

//     public function __construct(EncryptionService $encryptionService)
//     {
//         $this->encryptionService = $encryptionService;
//     }

//     /**
//      * Store a newly created prescription in storage.
//      */
//     public function store(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'patient_id' => 'required|exists:patients,id',
//             'medication_id' => 'required|exists:medications,id',
//             'dosage' => 'required|string|max:255',
//             'instructions' => 'required|string|max:1000',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         $doctor = Auth::user(); // Get authenticated doctor
//         $patient = Patient::findOrFail($request->patient_id);
//         $medication = Medication::findOrFail($request->medication_id);

//         // Optional: Add authorization check - ensure doctor can prescribe for this patient
//         // if ($doctor->cannot('prescribeFor', $patient)) {
//         //     return response()->json(['message' => 'Unauthorized'], 403);
//         // }

//         // Prepare payload for encryption
//         $payload = json_encode([
//             'patient_name' => $patient->name, // Store name for easy display on verification
//             'medication_name' => $medication->name, // Store name for easy display
//             'dosage' => $request->dosage,
//             'instructions' => $request->instructions,
//             'doctor_name' => $doctor->name, // Store doctor name
//             'prescribed_at' => now()->toIso8601String(),
//         ]);

//         // Encrypt using the CURRENT key
        
//         $encryptionResult = $this->encryptionService->encryptWithCurrentKey($payload);

//         // Create the prescription record
//         $prescription = new Prescription();
//         $prescription->patient_id = $patient->id;
//         $prescription->doctor_id = $doctor->id;
//         $prescription->medication_id = $medication->id;
//         $prescription->encrypted_payload = $encryptionResult['data'];
//         $prescription->encryption_key_version = $encryptionResult['key_version'];
//         // UUID is generated automatically by the model's boot method
//         $prescription->save();

//         // Generate QR code data (URL)
//         $verificationUrl = route('prescription.verify', ['uuid' => $prescription->uuid]);
//         $qrCode = base64_encode(QrCode::format('png')->size(200)->generate($verificationUrl));


//         return response()->json([
//             'message' => 'Prescription created successfully.',
//             'prescription_id' => $prescription->id,
//             'verification_uuid' => $prescription->uuid,
//             'verification_url' => $verificationUrl,
//             'qr_code_png_base64' => $qrCode, // Send QR code data to frontend
//         ], 201);
//     }

//     // Add other methods (index, show) as needed, decrypting data for the doctor
// }



// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Http\Resources\PrescriptionResource;
// use App\Models\Patient;
// use App\Models\Medication;
// use App\Models\Prescription;
// use App\Services\EncryptionService; // Your custom encryption service
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Validator;
// use SimpleSoftwareIO\QrCode\Facades\QrCode; // For QR Code generation
// use Illuminate\Http\JsonResponse;

// class PrescriptionController extends Controller
// {
//     protected EncryptionService $encryptionService;

//     public function __construct(EncryptionService $encryptionService)
//     {
//         $this->encryptionService = $encryptionService;
//     }

//     /**
//      * Store a newly created prescription in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @return \App\Http\Resources\PrescriptionResource|\Illuminate\Http\JsonResponse
//      */
//     public function store(Request $request): PrescriptionResource|JsonResponse
//     {
//         $doctor = Auth::user(); // Get authenticated doctor

//         $validator = Validator::make($request->all(), [
//             'patient_id' => 'required|exists:patients,id',
//             'medication_id' => 'required|exists:medications,id',
//             'dosage' => 'required|string|max:255',
//             'instructions' => 'required|string|max:1000',
//         ]);

//         if ($validator->fails()) {
//             return response()->json(['errors' => $validator->errors()], 422);
//         }

//         // Fetch patient and medication to include their names in the encrypted payload
//         // and to perform authorization checks.
//         $patient = Patient::find($request->patient_id);
//         $medication = Medication::find($request->medication_id);

//         if (!$patient || !$medication) {
//             return response()->json(['message' => 'Patient or Medication not found.'], 404);
//         }

//         // Authorization: Ensure the doctor is prescribing for their own patient
//         if ($patient->doctor_id !== $doctor->id) {
//             return response()->json(['message' => 'Unauthorized to prescribe for this patient.'], 403);
//         }

//         // Prepare payload for encryption
//         $payload = json_encode([
//             'patient_name' => $patient->name,
//             'medication_name' => $medication->name,
//             'dosage' => $request->dosage,
//             'instructions' => $request->instructions,
//             'doctor_name' => $doctor->name, // Name of the prescribing doctor
//             'prescribed_at' => now()->toIso8601String(), // Timestamp within payload
//         ]);

//         try {
//             // Encrypt using the CURRENT key from your EncryptionService
//             $encryptionResult = $this->encryptionService->encryptWithCurrentKey($payload);

//             // Create the prescription record
//             // UUID for the prescription model should be auto-generated by its boot method
//             $prescription = Prescription::create([
//                 'patient_id' => $patient->id,
//                 'doctor_id' => $doctor->id,
//                 'medication_id' => $medication->id,
//                 'encrypted_payload' => $encryptionResult['data'],
//                 'encryption_key_version' => $encryptionResult['key_version'],
//                 'prescription_date' => now(), // Actual DB timestamp for prescription
//             ]);

//             // Generate QR code data (URL) for the response
//             $verificationUrl = route('prescription.verify', ['uuid' => $prescription->uuid]);
//             $qrCodeBase64 = base64_encode(QrCode::format('png')->size(200)->generate($verificationUrl));

//             // Manually add qr_code_data to the resource for the creation response
//             // This is a bit of a workaround as the resource itself doesn't store it.
//             $resource = new PrescriptionResource($prescription);
//             $responseData = $resource->toArray($request);
//             $responseData['qr_code_png_base64'] = $qrCodeBase64;


//             return response()->json($responseData, 201); // Return the resource with 201 status

//         } catch (\Exception $e) {
//             report($e); // Log the exception
//             return response()->json(['message' => 'Failed to create prescription. ' . $e->getMessage()], 500);
//         }
//     }

//     // You might want an index method for doctors to view prescriptions they've written
//     // public function index(Request $request) { ... }

//     // You might want a show method for a doctor to view a specific prescription's details
//     // (which would involve decryption)
//     // public function show(Prescription $prescription) { ... ensure doctor owns it ... }
// }


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrescriptionResource; // Assuming this resource exists and is set up
use App\Models\Patient;
use App\Models\Medication;
use App\Models\Prescription;
use App\Services\EncryptionService; // Your custom encryption service
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // For QR Code generation
use Illuminate\Http\JsonResponse; // For type hinting

class PrescriptionController extends Controller
{
    protected EncryptionService $encryptionService; // PHP 7.4+ type hinting

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    /**
     * Store a newly created prescription in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\PrescriptionResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request): PrescriptionResource|JsonResponse // Return type hinting
    {
        $doctor = Auth::user(); // Get authenticated doctor

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'medication_id' => 'required|exists:medications,id',
            'dosage' => 'required|string|max:255',
            'instructions' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Fetch patient and medication to include their names in the encrypted payload
        // and to perform authorization checks if needed.
        $patient = Patient::find($request->patient_id); // Use find instead of findOrFail to handle not found gracefully
        $medication = Medication::find($request->medication_id);

        if (!$patient || !$medication) {
            // You might want to be more specific if one or the other is not found
            return response()->json(['message' => 'Patient or Medication not found.'], 404);
        }

        // Authorization: Ensure the doctor is prescribing for their own patient
        // This assumes a 'doctor_id' field on the Patient model linking to the User model (doctors)
        if ($patient->doctor_id !== $doctor->id) {
            return response()->json(['message' => 'Unauthorized to prescribe for this patient.'], 403);
        }

        // Prepare payload for encryption
        $payload = json_encode([
            'patient_name' => $patient->name,
            'medication_name' => $medication->name,
            'dosage' => $request->dosage,
            'instructions' => $request->instructions,
            'doctor_name' => $doctor->name, // Name of the prescribing doctor
            'prescribed_at' => now()->toIso8601String(), // Timestamp within payload for when it was logically prescribed
        ]);

        try {
            // Encrypt using the CURRENT key from your EncryptionService
            // **MODIFICATION: Use encryptPayloadWithCurrentKey**
            $encryptionResult = $this->encryptionService->encryptPayloadWithCurrentKey($payload);

            // Create the prescription record
            // UUID for the prescription model should be auto-generated by its boot method if set up
            $prescription = Prescription::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'medication_id' => $medication->id,
                'encrypted_payload' => $encryptionResult['data'],
                'encryption_key_version' => $encryptionResult['key_version'],
                'prescription_date' => now(), // Actual database timestamp for prescription record creation
                // 'is_filled' defaults to false as per migration
            ]);

            // Generate QR code data (URL) for the response
            // Ensure you have a named route 'prescription.verify' in routes/web.php
            $verificationUrl = route('prescription.verify', ['uuid' => $prescription->uuid]);
            $qrCodeBase64 = base64_encode(QrCode::format('png')->size(200)->generate($verificationUrl));

            // Return the new resource.
            // If PrescriptionResource is set up to include the QR code, it will be there.
            // Otherwise, we might need to add it to the response data manually.
            $resource = new PrescriptionResource($prescription->fresh()); // Use fresh() to get all attributes including UUID

            // To include the QR code in the response if not handled by the resource itself:
            $responseData = $resource->toArray($request);
            $responseData['qr_code_png_base64'] = $qrCodeBase64;
            $responseData['verification_url'] = $verificationUrl; // Also good to return the URL explicitly

            return response()->json($responseData, 201); // Return the resource with 201 status

        } catch (\Exception $e) {
            report($e); // Log the exception
            // Provide a more generic message to the user but log the specific error.
            return response()->json(['message' => 'Failed to create prescription. Please try again later.'], 500);
        }
        // try {
        //     $encryptionResult = $this->encryptionService->encryptPayloadWithCurrentKey($payload);
        //     // dd('Encryption done', $encryptionResult); // Checkpoint 1
        
        //     $prescription = Prescription::create([
        //         'patient_id' => $patient->id,
        //         'doctor_id' => $doctor->id,
        //         'medication_id' => $medication->id,
        //         'encrypted_payload' => $encryptionResult['data'],
        //         'encryption_key_version' => $encryptionResult['key_version'],
        //         'prescription_date' => now(),
        //     ]);
        //     // dd('Prescription created', $prescription); // Checkpoint 2
        
        //     $verificationUrl = route('prescription.verify', ['uuid' => $prescription->uuid]);
        //     // dd('Verification URL generated', $verificationUrl); // Checkpoint 3
        
        //     $qrCodeBase64 = base64_encode(QrCode::format('png')->size(200)->generate($verificationUrl));
        //     // dd('QR Code generated'); // Checkpoint 4
        
        //     $resource = new PrescriptionResource($prescription->fresh());
        //     $responseData = $resource->toArray($request);
        //     // dd('Resource prepared', $responseData); // Checkpoint 5
        //     // ...
        
        // } catch (\Exception $e) {
        //     report($e);
        //     // For debugging, you can temporarily return the actual error:
        //     // return response()->json(['message' => 'Failed to create prescription.', 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        //     return response()->json(['message' => 'Failed to create prescription. Please try again later.'], 500);
        // }
    }

    // You might want an index method for doctors to view prescriptions they've written
    // public function index(Request $request) { ... }

    // You might want a show method for a doctor to view a specific prescription's details
    // (which would involve decryption and authorization)
    // public function show(Prescription $prescription) { ... }
}
// https://www.youtube.com/watch?v=FGjB0Srtcpk&t=3s
// https://youtu.be/bDLVeJQei_Q?si=YrNzL3kdz1OnOJOD
// https://youtu.be/zgGZSQYFuhw?si=pTEWuW4kBR9EN71l