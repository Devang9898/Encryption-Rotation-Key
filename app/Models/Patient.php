<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model {
    use HasFactory;
    protected $fillable = ['name', 'dob', 'contact_info', 'doctor_id'];
    protected $casts = ['dob' => 'date'];

    public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }
    public function medicalRecords() { return $this->hasMany(MedicalRecord::class); }
    public function prescriptions() { return $this->hasMany(Prescription::class); }
    public function patients()
    {
        return $this->hasMany(Patient::class, 'doctor_id');
    }
}
