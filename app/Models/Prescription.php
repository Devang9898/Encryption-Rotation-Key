<?php
// app/Models/Prescription.php
// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Support\Str;
// use App\Services\EncryptionService; // Assuming service exists

// class Prescription extends Model {
//     use HasFactory;

//     // Don't mass assign sensitive fields directly
//     protected $guarded = ['encrypted_payload', 'encryption_key_version'];

//     // Automatically generate UUID on creation
//     protected static function boot() {
//         parent::boot();
//         static::creating(function ($prescription) {
//             if (empty($prescription->uuid)) {
//                 $prescription->uuid = (string) Str::uuid();
//             }
//         });
//     }

//     // Relationships
//     public function patient() { return $this->belongsTo(Patient::class); }
//     public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }
//     public function medication() { return $this->belongsTo(Medication::class); }

//     /**
//      * Decrypts the payload using the stored key version.
//      * Returns null if decryption fails or payload is empty.
//      */
//     public function getDecryptedPayload(): ?array
//     {
//         if (empty($this->encrypted_payload) || is_null($this->encryption_key_version)) {
//             return null;
//         }
//         try {
//             $decryptedJson = app(EncryptionService::class)->decryptWithVersion(
//                 $this->encrypted_payload,
//                 $this->encryption_key_version
//             );
//             return json_decode($decryptedJson, true);
//         } catch (\Exception $e) {
//             // Log decryption error
//             report($e);
//             return null;
//         }
//     }
// }


// namespace App\Models; // Adjust namespace if different

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Support\Str;
// use App\Services\EncryptionService; // Assuming service exists

// class Prescription extends Model
// {
//     use HasFactory;

//     protected $guarded = ['encrypted_payload', 'encryption_key_version'];

//     /**
//      * The attributes that should be cast.
//      *
//      * @var array<string, string>
//      */
//     protected $casts = [
//         'prescription_date' => 'datetime', // <-- ADD THIS LINE
//         'is_filled' => 'boolean',         // Also good to cast booleans if you have them
//         // Eloquent usually handles created_at and updated_at automatically,
//         // but explicitly casting them doesn't hurt if they are also strings for some reason.
//         // 'created_at' => 'datetime',
//         // 'updated_at' => 'datetime',
//     ];

//     // Automatically generate UUID on creation
//     protected static function boot()
//     {
//         parent::boot();
//         static::creating(function ($prescription) {
//             if (empty($prescription->uuid)) {
//                 $prescription->uuid = (string) Str::uuid();
//             }
//         });
//     }

//     // Relationships
//     public function patient() { return $this->belongsTo(Patient::class); }
//     public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }
//     public function medication() { return $this->belongsTo(Medication::class); }

//     /**
//      * Decrypts the payload using the stored key version.
//      * Returns null if decryption fails or payload is empty.
//      */
//     // public function getDecryptedPayload(): ?array
//     // {
//     //     if (empty($this->encrypted_payload) || is_null($this->encryption_key_version)) {
//     //         return null;
//     //     }
//     //     try {
//     //         $decryptedJson = app(EncryptionService::class)->decryptWithVersion(
//     //             $this->encrypted_payload,
//     //             $this->encryption_key_version
//     //         );
//     //         return json_decode($decryptedJson, true);
//     //     } catch (\Exception $e) {
//     //         report($e);
//     //         return null;
//     //     }
//     // }
//     // app/Models/Prescription.php - Ensure getDecryptedPayload is robust
// // ... (rest of the model) ...
// public function getDecryptedPayload(): ?array
// {
//     if (empty($this->encrypted_payload) || is_null($this->encryption_key_version)) {
//         return null;
//     }
//     try {
//         // This already uses the correct method from EncryptionService
//         $decryptedJson = app(EncryptionService::class)->decryptWithVersion(
//             $this->encrypted_payload,
//             (int) $this->encryption_key_version // Ensure version is int
//         );
//         if ($decryptedJson === null) return null; // Decryption might return null for empty original string
//         return json_decode($decryptedJson, true);
//     } catch (\Exception $e) {
//         Log::error("Error decrypting prescription payload for UUID {$this->uuid}: " . $e->getMessage());
//         report($e);
//         return null;
//     }
// }
// }


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Log; // For getDecryptedPayload logging

class Prescription extends Model
{
    use HasFactory;

    // Define which attributes are mass assignable
    protected $fillable = [
        'uuid',                     // If your boot method handles it, it's fine, but can be fillable
        'patient_id',
        'doctor_id',
        'medication_id',
        'encrypted_payload',      // <<<--- ALLOW THIS
        'encryption_key_version', // <<<--- ALLOW THIS
        'prescription_date',
        'is_filled',                // If you set this on creation
    ];

    // Remove or comment out $guarded if using $fillable
    // protected $guarded = ['encrypted_payload', 'encryption_key_version']; // <-- REMOVE OR COMMENT OUT

    protected $casts = [
        'prescription_date' => 'datetime',
        'is_filled' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($prescription) {
            if (empty($prescription->uuid)) {
                $prescription->uuid = (string) Str::uuid();
            }
        });
    }

    public function patient() { return $this->belongsTo(Patient::class); }
    public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }
    public function medication() { return $this->belongsTo(Medication::class); }

    public function getDecryptedPayload(): ?array
    {
        if (empty($this->encrypted_payload) || is_null($this->encryption_key_version)) {
            return null;
        }
        try {
            $decryptedJson = app(EncryptionService::class)->decryptWithVersion(
                $this->encrypted_payload,
                (int) $this->encryption_key_version
            );
            if ($decryptedJson === null && !empty($this->encrypted_payload)) { // If original payload was not empty but decrypt is null
                Log::warning("Decryption of non-empty payload resulted in null for Prescription UUID {$this->uuid}. Key version: {$this->encryption_key_version}");
                return null;
            }
            return json_decode($decryptedJson, true);
        } catch (\Exception $e) {
            Log::error("Error decrypting prescription payload for UUID {$this->uuid}: " . $e->getMessage(), ['exception' => $e]);
            // report($e); // report() is good too
            return null;
        }
    }
}