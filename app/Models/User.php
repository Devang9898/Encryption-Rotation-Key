<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Uncomment if using email verification
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens; // Make sure Passport trait is used

class User extends Authenticatable // Add implements MustVerifyEmail if needed
{
    use HasApiTokens, HasFactory, Notifiable; // Add HasApiTokens

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',      // <-- Add this
        'email',     // <-- Add this
        'password',  // <-- Add this
        // Add any other fields you want to allow mass assignment for
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Use 'hashed' cast in Laravel 9+
    ];

    // Add relationships (patients, prescriptions) if needed
    // public function patients() { ... }
    // public function prescriptions() { ... }
}