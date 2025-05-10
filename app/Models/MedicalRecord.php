<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\EncryptionService; // Your service
use Illuminate\Database\Eloquent\Casts\Attribute;

class MedicalRecord extends Model
{
    use HasFactory;

    // Use virtual attributes for diagnosis/notes so they are in $fillable
    // The actual encrypted data will be stored in diagnosis_encrypted, notes_encrypted
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'record_date',
        'diagnosis', // Virtual attribute
        'notes'     // Virtual attribute
    ];

    // Hide the raw encrypted fields from direct JSON serialization
    protected $hidden = ['diagnosis_encrypted', 'notes_encrypted'];

    protected $casts = [
        'record_date' => 'datetime',
    ];

    // Relationships
    public function patient() { return $this->belongsTo(Patient::class); }
    public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }

    // Accessor & Mutator for Diagnosis
    protected function diagnosis(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => // $value here is the 'diagnosis' virtual attribute, usually null on get
                isset($attributes['diagnosis_encrypted']) && !empty($attributes['diagnosis_encrypted'])
                    ? app(EncryptionService::class)->tryDecryptWithAllKeys($attributes['diagnosis_encrypted'])
                    : null,
            set: fn ($valueToEncrypt) => [ // $valueToEncrypt is the plain text being set
                'diagnosis_encrypted' => $valueToEncrypt
                    ? app(EncryptionService::class)->encryptWithCurrentKeyReturnData($valueToEncrypt)
                    : null
            ],
        );
    }

     // Accessor & Mutator for Notes
    protected function notes(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) =>
                isset($attributes['notes_encrypted']) && !empty($attributes['notes_encrypted'])
                    ? app(EncryptionService::class)->tryDecryptWithAllKeys($attributes['notes_encrypted'])
                    : null,
            set: fn ($valueToEncrypt) => [
                'notes_encrypted' => $valueToEncrypt
                    ? app(EncryptionService::class)->encryptWithCurrentKeyReturnData($valueToEncrypt)
                    : null
            ],
        );
    }
}
// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use App\Services\EncryptionService;
// use Illuminate\Database\Eloquent\Casts\Attribute;

// class MedicalRecord extends Model {
//     use HasFactory;

//     protected $fillable = [
//         'patient_id',
//         'doctor_id',
//         'record_date', // This is being filled
//         'diagnosis',
//         'notes'
//     ];
//     protected $hidden = ['diagnosis_encrypted', 'notes_encrypted'];

//     /**
//      * The attributes that should be cast.
//      *
//      * @var array<string, string>
//      */
//     protected $casts = [
//         'record_date' => 'datetime', // <-- ADD THIS LINE or 'date' if it's just a date
//         // Eloquent usually handles created_at and updated_at automatically,
//         // but explicitly casting them doesn't hurt if they are also strings for some reason.
//         // 'created_at' => 'datetime',
//         // 'updated_at' => 'datetime',
//     ];

//     // Relationships
//     public function patient() { return $this->belongsTo(Patient::class); }
//     public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }

//     // Accessor & Mutator for Diagnosis
//     protected function diagnosis(): Attribute
//     {
//         return Attribute::make(
//             get: fn ($value, $attributes) => isset($attributes['diagnosis_encrypted'])
//                 ? app(EncryptionService::class)->decrypt($attributes['diagnosis_encrypted'])
//                 : null,
//             set: fn ($value) => [
//                 'diagnosis_encrypted' => app(EncryptionService::class)->encrypt($value)
//             ],
//         );
//     }

//      // Accessor & Mutator for Notes
//     protected function notes(): Attribute
//     {
//         return Attribute::make(
//             get: fn ($value, $attributes) => isset($attributes['notes_encrypted'])
//                 ? app(EncryptionService::class)->decrypt($attributes['notes_encrypted'])
//                 : null,
//             set: fn ($value) => [
//                 'notes_encrypted' => app(EncryptionService::class)->encrypt($value)
//             ],
//         );
//     }
// }