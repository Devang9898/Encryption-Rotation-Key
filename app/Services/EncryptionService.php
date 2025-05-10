<?php

// namespace App\Services;

// use Illuminate\Contracts\Encryption\DecryptException;
// use Illuminate\Encryption\Encrypter;
// use RuntimeException;

// class EncryptionService
// {
//     protected $keys;
//     protected $currentVersion;

//    // app/Services/EncryptionService.php
//     public function __construct()
//     {
//         $this->keys = config('encryption_keys.keys');
//         $this->currentVersion = config('encryption_keys.current_version');

//         // Refined Check:
//         if (
//             empty($this->keys) || // Are there no versions defined at all?
//             !isset($this->keys[$this->currentVersion]) || // Does the current_version index not exist in the keys array?
//             empty($this->keys[$this->currentVersion])    // Is the key for the current_version empty/null?
//         ) {
//             // Construct a more detailed error message for debugging
//             $configPath = config_path('encryption_keys.php');
//             $envPath = base_path('.env');
//             $debugMessage = "Encryption keys or current version are not configured correctly. " .
//                             "Check your '{$configPath}' and '{$envPath}' files. " .
//                             "Current version requested: {$this->currentVersion}. Available key versions: " .
//                             (is_array($this->keys) ? implode(', ', array_keys($this->keys)) : 'none');
//             throw new RuntimeException($debugMessage);
//         }
//     }

//     protected function getEncrypterForKey(int $keyVersion): Encrypter
//     {
//         if (!isset($this->keys[$keyVersion])) {
//             throw new RuntimeException("Encryption key for version {$keyVersion} not found.");
//         }
//         $key = $this->keys[$keyVersion];
//          // Use Laravel's standard AES-256-CBC cipher
//         return new Encrypter($this->parseKey($key), config('app.cipher'));
//     }

//     /**
//      * Parse the encryption key. Copied from Laravel's bootstrap/app.php logic.
//      */
//     protected function parseKey(string $key): string
//     {
//          if (str_starts_with($key, $prefix = 'base64:')) {
//             $key = base64_decode(substr($key, strlen($prefix)));
//         }
//         return $key;
//     }

//     /**
//      * Encrypt data using the CURRENT key version.
//      */
//     public function encryptWithCurrentKey(string $data): array
//     {
//         $encrypter = $this->getEncrypterForKey($this->currentVersion);
//         $encryptedData = $encrypter->encryptString($data);

//         return [
//             'data' => $encryptedData,
//             'key_version' => $this->currentVersion,
//         ];
//     }

//     /**
//      * Decrypt data using a SPECIFIC key version.
//      */
//     public function decryptWithVersion(string $encryptedData, int $keyVersion): string
//     {
//         $encrypter = $this->getEncrypterForKey($keyVersion);
//         try {
//              return $encrypter->decryptString($encryptedData);
//         } catch (DecryptException $e) {
//              // Consider more specific error handling or logging
//             throw new DecryptException("Could not decrypt data with key version {$keyVersion}. " . $e->getMessage(), 0, $e);
//         }
//     }

//     // --- Optional: Simple encrypt/decrypt using Laravel's default key ---
//     // Useful for per-field encryption if you don't version those keys separately

//     /**
//      * Encrypt using Laravel's default Crypt facade (uses APP_KEY).
//      */
//     public function encrypt(string $data): string
//     {
//          return encrypt($data);
//          // Or if you want consistency, use the current version from this service:
//          // $result = $this->encryptWithCurrentKey($data);
//          // return $result['data']; // Note: Doesn't store version info implicitly
//     }

//     /**
//      * Decrypt using Laravel's default Crypt facade (uses APP_KEY).
//      */
//     public function decrypt(string $encryptedData): string
//     {
//         try {
//              return decrypt($encryptedData);
//         } catch (DecryptException $e) {
//             throw new DecryptException("Could not decrypt data using default key. " . $e->getMessage(), 0, $e);
//         }
//          // Or if you implement version detection for per-field:
//          // You'd need logic here to try different keys (v1, v2...) until one works,
//          // or store the key version alongside the encrypted field.
//     }
// }



namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use RuntimeException;
use Illuminate\Support\Facades\Log; // For logging decryption attempts

class EncryptionService
{
    protected array $keysConfig; // Renamed for clarity
    protected int $currentVersion;
    protected string $cipher;

    public function __construct()
    {
        $this->keysConfig = config('encryption_keys.keys', []); // Default to empty array
        $this->currentVersion = (int) config('encryption_keys.current_version', 0); // Default to 0 or handle error
        $this->cipher = config('app.cipher');

        if (
            empty($this->keysConfig) ||
            $this->currentVersion === 0 || // Check if current_version was loaded
            !isset($this->keysConfig[$this->currentVersion]) ||
            empty($this->keysConfig[$this->currentVersion])
        ) {
            $configPath = config_path('encryption_keys.php');
            $envPath = base_path('.env');
            $debugMessage = "Encryption keys or current version are not configured correctly. " .
                            "Check your '{$configPath}' and '{$envPath}' files. " .
                            "Current version requested: {$this->currentVersion}. Available key versions: " .
                            (is_array($this->keysConfig) ? implode(', ', array_keys($this->keysConfig)) : 'none');
            Log::critical($debugMessage); // Log critical error
            throw new RuntimeException($debugMessage);
        }
    }

    protected function getEncrypterForKey(int $keyVersion): Encrypter
    {
        if (!isset($this->keysConfig[$keyVersion]) || empty($this->keysConfig[$keyVersion])) {
            throw new RuntimeException("Encryption key for version {$keyVersion} not found or is empty.");
        }
        $key = $this->keysConfig[$keyVersion];
        return new Encrypter($this->parseKey($key), $this->cipher);
    }

    protected function parseKey(string $key): string
    {
        if (str_starts_with($key, $prefix = 'base64:')) {
            $key = base64_decode(substr($key, strlen($prefix)));
        }
        return $key;
    }

    /**
     * Encrypt data using the CURRENT key version and return only the encrypted string.
     * Used for per-field encryption where version might not be stored alongside.
     */
    public function encryptWithCurrentKeyReturnData(string $value): string
    {
        if (empty($value)) return $value; // Don't encrypt empty strings, or handle as needed
        $encrypter = $this->getEncrypterForKey($this->currentVersion);
        return $encrypter->encryptString($value);
    }

    /**
     * Encrypt data using the CURRENT key version and return data and key version.
     * Used for payloads like prescriptions where version is stored.
     */
    public function encryptPayloadWithCurrentKey(string $payload): array
    {
        if (empty($payload)) return ['data' => $payload, 'key_version' => $this->currentVersion];
        $encrypter = $this->getEncrypterForKey($this->currentVersion);
        $encryptedData = $encrypter->encryptString($payload);

        return [
            'data' => $encryptedData,
            'key_version' => $this->currentVersion,
        ];
    }

    /**
     * Decrypt data using a SPECIFIC key version.
     * Used for payloads like prescriptions where version is known.
     */
    public function decryptWithVersion(string $encryptedData, int $keyVersion): string
    {
        if (empty($encryptedData)) return $encryptedData;
        $encrypter = $this->getEncrypterForKey($keyVersion);
        try {
            return $encrypter->decryptString($encryptedData);
        } catch (DecryptException $e) {
            Log::warning("Decryption failed for key version {$keyVersion}.", ['exception' => $e->getMessage()]);
            throw new DecryptException("Could not decrypt data with key version {$keyVersion}. " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Attempt to decrypt data by trying all known keys in reverse order (newest first).
     * Used for per-field encryption where version is not stored.
     */
    public function tryDecryptWithAllKeys(string $encryptedValue): ?string
    {
        if (empty($encryptedValue)) return $encryptedValue;

        // Get key versions and sort them in descending order (try newest first)
        $keyVersions = array_keys($this->keysConfig);
        rsort($keyVersions); // Sorts in place, numerically if keys are numbers

        foreach ($keyVersions as $version) {
            try {
                $encrypter = $this->getEncrypterForKey($version);
                return $encrypter->decryptString($encryptedValue);
            } catch (DecryptException $e) {
                // This is expected if the key is wrong, continue to the next key
                Log::debug("Decryption attempt failed with key version {$version} for a field. Trying next.");
            } catch (RuntimeException $e) {
                // This might happen if a key is configured but invalid (e.g., wrong length after parsing)
                Log::error("Runtime exception during decryption attempt with key version {$version} for a field: " . $e->getMessage());
            }
        }

        Log::error("Failed to decrypt field value after trying all known key versions.");
        // Optionally, throw an exception here if decryption is mandatory and fails
        // throw new DecryptException("Failed to decrypt field value after trying all known keys.");
        return null; // Or return the original encrypted value if you prefer, or throw
    }


    // --- Methods using Laravel's default APP_KEY (kept for reference or other uses) ---
    public function encryptWithAppKey(string $data): string
    {
         return encrypt($data); // Uses APP_KEY
    }

    public function decryptWithAppKey(string $encryptedData): string
    {
        try {
             return decrypt($encryptedData); // Uses APP_KEY
        } catch (DecryptException $e) {
            throw new DecryptException("Could not decrypt data using default APP_KEY. " . $e->getMessage(), 0, $e);
        }
    }
}