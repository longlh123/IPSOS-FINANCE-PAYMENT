<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

class ENVObject
{
    public $env;
    public $environment;
    public $url;
    public $merchantInfo;

    public $gotitEnvironment;
    public $gotitUrl;
    public $gotitInfo;

    public function __construct(){
        $this->env = $this->readEnvFile();

        $this->environment = $this->env['VINNET_ENV'];
        
        if($this->environment === 'production'){
            $this->url = $this->env['VINNET_URL'];

            $this->merchantInfo = [
                'VINNET_MERCHANT_CODE' => str_replace('"', '', $this->env['VINNET_MERCHANT_CODE']),
                'VINNET_MERCHANT_KEY' => str_replace('"', '', $this->env['VINNET_MERCHANT_KEY'])
            ];
        } else {
            $this->url = $this->env['VINNET_URL_STAGING'];

            $this->merchantInfo = [
                'VINNET_MERCHANT_CODE' => str_replace('"', '', $this->env['VINNET_MERCHANT_CODE_STAGING']),
                'VINNET_MERCHANT_KEY' => str_replace('"', '', $this->env['VINNET_MERCHANT_KEY_STAGING'])
            ];
        }

        $this->gotitEnvironment = $this->env['GOTIT_ENV'];

        if($this->gotitEnvironment === 'production'){
            $this->gotitUrl = $this->env['GOTIT_URL'];

            $this->gotitInfo = [
                'API_KEY' => str_replace('"', '', $this->env['GOTIT_API_KEY']),
                'TRANSACTIONREFID_PREFIX' => str_replace('"', '', $this->env['GOTIT_TRANSACTIONREFID_PREFIX'])
            ];
        } else {
            $this->gotitUrl = $this->env['GOTIT_URL_STAGING'];

            $this->gotitInfo = [
                'API_KEY' => str_replace('"', '', $this->env['GOTIT_API_KEY_STAGING']),
                'TRANSACTIONREFID_PREFIX' => str_replace('"', '', $this->env['GOTIT_TRANSACTIONREFID_PREFIX_STAGING'])
            ];
        }
    }

    public function setEnvValue(string $key, string $value){

        $this->updateEnv([$key => str_replace('"', '', $value)]);
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
