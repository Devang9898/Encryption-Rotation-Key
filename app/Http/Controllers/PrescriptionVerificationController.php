<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View; // If returning a blade view

class PrescriptionVerificationController extends Controller
{
    /**
     * Show the prescription verification page.
     */
    public function showVerificationPage(string $uuid)
    {
        $prescription = Prescription::where('uuid', $uuid)->first();

        if (!$prescription) {
            abort(404, 'Prescription not found.');
        }

        // Decrypt the payload using the service and stored version
        $decryptedData = $prescription->getDecryptedPayload();

        if (is_null($decryptedData)) {
             // Log this issue - decryption failed or data missing
             report("Failed to decrypt prescription UUID: " . $uuid);
             abort(500, 'Could not retrieve prescription details.');
        }

        // Pass decrypted data to a view
        return view('prescription.verify', [
            'prescriptionDetails' => $decryptedData,
            'prescription' => $prescription // Pass the model if needed for other info like date
        ]);

         // Or return JSON if it's meant for an SPA frontend part
        // return response()->json([
        //     'prescriptionDetails' => $decryptedData,
        //     'prescriptionDate' => $prescription->prescription_date,
        // ]);
    }
}