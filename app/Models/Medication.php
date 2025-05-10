<?php

// app/Models/Medication.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medication extends Model {
    use HasFactory;
    protected $fillable = ['name', 'description'];
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}
