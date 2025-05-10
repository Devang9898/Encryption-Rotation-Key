<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport; // Import Passport

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // **Crucial for commands if keys aren't found during bootstrap**
        Passport::loadKeysFrom(storage_path());

        // --- Passport v13+ Specific Configurations ---

        // If you want to use incremental integer IDs for clients instead of UUIDs (default)
        // Passport::$clientUuids = false;

        // If you were using Passport's old views and need to define a custom one
        // (Passport is now headless by default)
        // Passport::authorizationView('path.to.your.custom.oauth.authorize_view');

        // If you need to continue using the deprecated JSON API routes
        // Passport::$registersJsonApiRoutes = true;

        // Define token and refresh token lifetimes (optional)
        // Passport::tokensExpireIn(now()->addDays(15));
        // Passport::refreshTokensExpireIn(now()->addDays(30));
        // Passport::personalAccessTokensExpireIn(now()->addMonths(6)); // Still relevant for personal access tokens
    }
}