<?php
return [
    App\Providers\AppServiceProvider::class,
    // ... other providers
    Laravel\Passport\PassportServiceProvider::class, // <<< THIS MUST BE PRESENT
];