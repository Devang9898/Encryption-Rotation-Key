<?php

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\PatientController;
// use App\Http\Controllers\Api\MedicalRecordController;
// use App\Http\Controllers\Api\MedicationController;
// use App\Http\Controllers\Api\PrescriptionController;

// // // Auth routes
// // Route::post('/register', [AuthController::class, 'register']);
// // Route::post('/login', [AuthController::class, 'login']);

// // // Protected routes
// // Route::middleware('auth:api')->group(function () {
// //     Route::post('/logout', [AuthController::class, 'logout']);
// //     Route::get('/user', function (Request $request) {
// //         return $request->user();
// //     });

// //     Route::apiResource('patients', PatientController::class);
// //     // Route::apiResource('medical-records', MedicalRecordController::class); // Consider scoping to patient: /patients/{patient}/medical-records
// //     Route::apiResource('medications', MedicationController::class)->except(['show']); // Maybe only need index/store?

// //     // Nested routes might be better for records/prescriptions
// //     Route::prefix('patients/{patient}')->group(function () {
// //         Route::apiResource('medical-records', MedicalRecordController::class)->only(['index', 'store', 'show']); // Add update/delete if needed
// //     });

// //     Route::apiResource('prescriptions', PrescriptionController::class)->only(['store']); // Add index/show if doctor needs to see list

// // });
// // routes/api.php

// // Public routes (like login/register)
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

// // Protected routes MUST be inside this group
// Route::middleware('auth:api')->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);
//     Route::get('/user', [AuthController::class, 'user']); // Example

//     // Ensure your patient routes are here:
//     Route::apiResource('patients', PatientController::class);
//     Route::apiResource('medications', MedicationController::class);
//     // ... other protected resources like medical records, prescriptions ...
//     Route::post('/patients/{patient}/medical-records', [MedicalRecordController::class, 'store']);
//     Route::post('/prescriptions', [PrescriptionController::class, 'store']);
//     Route::apiResource('patients.medical-records', MedicalRecordController::class)
//          ->except(['update', 'destroy']) // Add update/destroy if you implement them
//          ->shallow(); // Makes routes like /medical-records/{medicalRecord} for show/update/destroy


// });



// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\PatientController;
// use App\Http\Controllers\Api\MedicalRecordController;
// use App\Http\Controllers\Api\MedicationController;
// use App\Http\Controllers\Api\PrescriptionController; // Assuming this controller exists

// /*
// |--------------------------------------------------------------------------
// | API Routes
// |--------------------------------------------------------------------------
// |
// | Here is where you can register API routes for your application. These
// | routes are loaded by the RouteServiceProvider and all of them will
// | be assigned to the "api" middleware group. Make something great!
// |
// */

// // --- Public Routes ---
// Route::post('/register', [AuthController::class, 'register'])->name('api.register'); // Optional: name routes
// Route::post('/login', [AuthController::class, 'login'])->name('api.login');     // Optional: name routes

// // --- Protected Routes (Require Authentication via Passport) ---
// Route::middleware('auth:api')->group(function () {

//     // Auth related
//     Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
//     Route::get('/user', [AuthController::class, 'user'])->name('api.user.show');

//     // Patient Management
//     Route::apiResource('patients', PatientController::class); // Provides standard CRUD routes for patients

//     // Medication Management
//     Route::apiResource('medications', MedicationController::class); // Provides standard CRUD routes for medications

//     // Prescription Management
//     // Define 'store' separately as it's a specific action.
//     // If you need other prescription routes (index, show for a doctor), add them or use apiResource.
//     Route::post('/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');

//     // Medical Record Management (Nested under Patients)
//     // This single apiResource line handles:
//     // GET    /patients/{patient}/medical-records             (medical-records.index)
//     // POST   /patients/{patient}/medical-records             (medical-records.store)
//     // GET    /medical-records/{medical_record}               (medical-records.show) -> due to shallow()
//     // PUT    /medical-records/{medical_record}               (medical-records.update) -> if not excepted
//     // DELETE /medical-records/{medical_record}               (medical-records.destroy) -> if not excepted
//     Route::apiResource('patients.medical-records', MedicalRecordController::class)
//          ->except(['update', 'destroy']) // Exclude update and destroy if you haven't implemented them yet
//          ->shallow(); // Makes 'show', 'update', 'destroy' routes non-nested for convenience

//     // Remove the duplicate 'store' route for medical records as apiResource already covers it:
//     // Route::post('/patients/{patient}/medical-records', [MedicalRecordController::class, 'store']); // <-- REMOVE THIS LINE

// });





use Illuminate\Http\Request; // Not strictly needed here if not used directly
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\MedicationController;
use App\Http\Controllers\Api\PrescriptionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// --- Public Routes ---
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// --- Protected Routes (Require Authentication via Passport) ---
Route::middleware('auth:api')->group(function () {

    // Auth related
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user.show');

    // Patient Management
    Route::apiResource('patients', PatientController::class);

    // Medication Management
    Route::apiResource('medications', MedicationController::class);

    // Prescription Management
    Route::post('/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');

    // Medical Record Management (Nested under Patients)
    // This handles:
    // GET    /patients/{patient}/medical-records             (patients.medical-records.index)
    // POST   /patients/{patient}/medical-records             (patients.medical-records.store)
    // GET    /medical-records/{medical_record}               (medical-records.show) -> due to shallow()
    // PUT    /medical-records/{medical_record}               (medical-records.update) -> if not in except()
    // DELETE /medical-records/{medical_record}               (medical-records.destroy) -> if not in except()
    Route::apiResource('patients.medical-records', MedicalRecordController::class)
         ->except(['update', 'destroy']) // Remove from except() when implemented
         ->shallow();
});