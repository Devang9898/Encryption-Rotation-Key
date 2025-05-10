<?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\PrescriptionVerificationController;

// Route::get('/', function () {
//     return ['Laravel Secure EHR' => app()->version()]; // Simple welcome
// });

// // Public route for prescription verification
// Route::get('/rx/verify/{uuid}', [PrescriptionVerificationController::class, 'showVerificationPage'])
//       ->name('prescription.verify') // Name the route for easy URL generation
//       ->whereUuid('uuid'); // Ensure the parameter is a valid UUID format


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrescriptionVerificationController; // Correct import

Route::get('/phpinfo-test', function () {
    phpinfo();
});
Route::get('/', function () {
    return ['Laravel Secure EHR' => app()->version()]; // Simple welcome
});

// Public route for prescription verification
Route::get('/rx/verify/{uuid}', [PrescriptionVerificationController::class, 'showVerificationPage'])
      ->name('prescription.verify')
      ->whereUuid('uuid'); // Good constraint