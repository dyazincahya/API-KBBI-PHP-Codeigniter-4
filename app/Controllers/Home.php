<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $envExists = file_exists(ROOTPATH . '.env');
        
        $geonodeSetup = false;
        if ($envExists) {
            // Check dot-notation keys
            $i = 0;
            while (true) {
                $key = env("geonode.apiKeys.{$i}.apikey");
                if (empty($key)) {
                    break;
                }
                if ($key !== 'MASUKKAN_API_KEY_ANDA_DI_SINI' && $key !== '') {
                    $geonodeSetup = true;
                    break;
                }
                $i++;
            }
            
            // Try JSON fallback
            if (!$geonodeSetup) {
                $apiKeysJson = env('geonode.apiKeys');
                if (!empty($apiKeysJson)) {
                    $rawKeys = json_decode($apiKeysJson, true) ?? [];
                    foreach ($rawKeys as $rawKey) {
                        if (isset($rawKey['apikey']) && $rawKey['apikey'] !== 'MASUKKAN_API_KEY_ANDA_DI_SINI' && $rawKey['apikey'] !== '') {
                            $geonodeSetup = true;
                            break;
                        }
                    }
                }
            }
        }

        return view('welcome_message', [
            'phpVersion'   => PHP_VERSION,
            'ciVersion'    => \CodeIgniter\CodeIgniter::CI_VERSION,
            'envExists'    => $envExists,
            'geonodeSetup' => $geonodeSetup,
        ]);
    }
}
