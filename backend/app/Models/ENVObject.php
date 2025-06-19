<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class ENVObject
{
    public $env;

    public function __construct(){
        $this->env = $this->readEnvFile();
    }

    private function readEnvFile()
    {
        $path = base_path('.env');
        $env = [];

        if (file_exists($path)) {
            // Read the .env file content
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                // Parse the environment variable
                list($key, $value) = explode('=', $line, 2);
                $env[$key] = $value;
            }
        }

        return $env;
    }

    private function updateEnv(array $data)
    {
        $path = base_path('.env');
        $env = file($path);
    
        // Loop through the .env file lines
        foreach ($env as $key => $line) {
            // Loop through the data to replace or add new values
            foreach ($data as $envKey => $envValue) {
                if (strpos($line, "$envKey=") === 0) {
                    $env[$key] = "$envKey=\"$envValue\"\n";
                    unset($data[$envKey]);
                }
            }
        }
    
        // Add new values at the end of the .env file
        foreach ($data as $envKey => $envValue) {
            $env[] = "$envKey=\"$envValue\"\n";
        }
    
        // Write the changes back to the .env file
        file_put_contents($path, implode('', $env));
    
        // Clear and cache the configuration
        Artisan::call('config:clear');
        Artisan::call('config:cache');
    }
}
