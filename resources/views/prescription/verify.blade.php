<!DOCTYPE html>
<html>
<head>
    <title>Prescription Verification</title>
    <style> /* Basic Styling */ </style>
</head>
<body>
    <h1>Prescription Verification</h1>

    @if(isset($prescriptionDetails))
        <p><strong>Patient:</strong> {{ $prescriptionDetails['patient_name'] ?? 'N/A' }}</p>
        <p><strong>Medication:</strong> {{ $prescriptionDetails['medication_name'] ?? 'N/A' }}</p>
        <p><strong>Dosage:</strong> {{ $prescriptionDetails['dosage'] ?? 'N/A' }}</p>
        <p><strong>Instructions:</strong> {{ $prescriptionDetails['instructions'] ?? 'N/A' }}</p>
        <p><strong>Prescribing Doctor:</strong> {{ $prescriptionDetails['doctor_name'] ?? 'N/A' }}</p>
        <p><strong>Prescribed At:</strong> {{ isset($prescriptionDetails['prescribed_at']) ? \Carbon\Carbon::parse($prescriptionDetails['prescribed_at'])->format('Y-m-d H:i') : 'N/A' }}</p>
        <p><strong>Prescription Date:</strong> {{ $prescription->prescription_date->format('Y-m-d H:i') }}</p>
        <p style="color: green; font-weight: bold;">Details Verified</p>
    @else
        <p style="color: red; font-weight: bold;">Could not load prescription details.</p>
    @endif

</body>
</html>