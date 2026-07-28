<?php

namespace App\Libraries;

use Exception;

class GeoNodeScraperAPI
{
    protected array $apiKeys = [];
    private string $apiUrl = 'https://scraper.geonode.io/v1/extract';

    public function __construct()
    {
        $apiKeys = [];
        
        // 1. Try loading dot-notation configuration (e.g. geonode.apiKeys.0.apikey)
        $i = 0;
        while (true) {
            $key = env("geonode.apiKeys.{$i}.apikey");
            if (empty($key)) {
                break;
            }
            $apiKeys[] = [
                'name'      => env("geonode.apiKeys.{$i}.name") ?? "Key #{$i}",
                'apikey'    => $key,
                'limit'     => (int)(env("geonode.apiKeys.{$i}.limit") ?? 1499),
                'reset_day' => (int)(env("geonode.apiKeys.{$i}.reset_day") ?? 10),
            ];
            $i++;
        }

        // 2. Fallback to JSON string format if no dot-notation configuration found
        if (empty($apiKeys)) {
            $apiKeysJson = env('geonode.apiKeys');
            if (!empty($apiKeysJson)) {
                $rawKeys = json_decode($apiKeysJson, true) ?? [];
                foreach ($rawKeys as $index => $rawKey) {
                    if (isset($rawKey['apikey'])) {
                        $apiKeys[] = [
                            'name'      => $rawKey['name'] ?? "Key #{$index}",
                            'apikey'    => $rawKey['apikey'],
                            'limit'     => (int)($rawKey['limit'] ?? 1499),
                            'reset_day' => (int)($rawKey['reset_day'] ?? 10),
                        ];
                    }
                }
            }
        }

        $this->apiKeys = $apiKeys;
    }

    /**
     * Get the start date of the current billing cycle for a given reset day.
     *
     * @param int $resetDay
     * @param \DateTime|null $today
     * @return string
     */
    public function getBillingCycle(int $resetDay, ?\DateTime $today = null): string
    {
        if ($today === null) {
            $today = new \DateTime();
        }

        $currentYear = (int)$today->format('Y');
        $currentMonth = (int)$today->format('m');
        $currentDay = (int)$today->format('d');

        if ($currentDay >= $resetDay) {
            $startYear = $currentYear;
            $startMonth = $currentMonth;
        } else {
            $startMonth = $currentMonth - 1;
            $startYear = $currentYear;
            if ($startMonth === 0) {
                $startMonth = 12;
                $startYear--;
            }
        }

        return sprintf('%04d-%02d-%02d', $startYear, $startMonth, $resetDay);
    }

    /**
     * Scrape HTML from a target URL using GeoNode Scraper API.
     *
     * @param string $targetUrl
     * @return string
     * @throws Exception
     */
    public function scrape(string $targetUrl): string
    {
        // Filter out unconfigured or placeholder keys
        $configuredKeys = array_filter($this->apiKeys, function($keyInfo) {
            return !empty($keyInfo['apikey']) && 
                   $keyInfo['apikey'] !== 'MASUKKAN_API_KEY_ANDA_DI_SINI' && 
                   $keyInfo['apikey'] !== 'API_KEY_KEDUA';
        });

        if (empty($configuredKeys)) {
            throw new Exception('GeoNode Scraper API Key is not configured.');
        }

        $limitFile = WRITEPATH . 'geonode_limit.json';
        $limitData = [];
        if (file_exists($limitFile)) {
            $limitData = json_decode(file_get_contents($limitFile), true) ?? [];
        }

        $lastUsedIndex = (int)($limitData['last_used_index'] ?? -1);
        $keysUsage = $limitData['keys_usage'] ?? [];

        // Find available key using round-robin rotation
        $totalKeys = count($configuredKeys);
        $selectedKeyInfo = null;
        $selectedKeyOrigIndex = null;
        $keysList = array_values($configuredKeys); // reset array keys

        for ($i = 0; $i < $totalKeys; $i++) {
            $candidateIndex = ($lastUsedIndex + 1 + $i) % $totalKeys;
            $candidate = $keysList[$candidateIndex];
            
            // Generate a unique hash for tracking limit of this key without leaking it in storage
            $keyHash = md5($candidate['apikey']);
            $resetDay = (int)($candidate['reset_day'] ?? 1);
            $limit = (int)($candidate['limit'] ?? 1499);
            $currentCycle = $this->getBillingCycle($resetDay);

            $usageCount = 0;
            if (isset($keysUsage[$keyHash])) {
                $usage = $keysUsage[$keyHash];
                if (isset($usage['billing_cycle']) && $usage['billing_cycle'] === $currentCycle) {
                    $usageCount = (int)($usage['count'] ?? 0);
                }
            }

            if ($usageCount < $limit) {
                // Key is available!
                $selectedKeyInfo = $candidate;
                $selectedKeyOrigIndex = $candidateIndex;
                break;
            }
        }

        if ($selectedKeyInfo === null) {
            throw new Exception('All configured GeoNode Scraper API keys have reached their monthly request limits.');
        }

        $apiKey = $selectedKeyInfo['apikey'];
        $keyHash = md5($apiKey);
        $resetDay = (int)($selectedKeyInfo['reset_day'] ?? 1);
        $currentCycle = $this->getBillingCycle($resetDay);

        $postData = [
            'url' => $targetUrl,
            'formats' => ['html'],
            'render_js' => false,
            'processing_mode' => 'sync',
            'proxy' => [
                'country' => 'US',
                'type' => 'datacenter'
            ],
            'headers' => [
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7'
            ]
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 35);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('GeoNode cURL Error: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $resData = json_decode($response, true);
            $msg = $resData['message'] ?? ($resData['code'] ?? 'HTTP Code ' . $httpCode);
            throw new Exception('GeoNode API Error: ' . $msg);
        }

        $result = json_decode($response, true);
        $html = $result['data']['html'] ?? '';

        if (empty($html)) {
            throw new Exception('GeoNode API response did not contain HTML content.');
        }

        // Increment and save request counts
        $keysUsage[$keyHash] = [
            'billing_cycle' => $currentCycle,
            'count' => (($keysUsage[$keyHash]['billing_cycle'] ?? '') === $currentCycle)
                ? (int)($keysUsage[$keyHash]['count'] ?? 0) + 1 
                : 1
        ];

        $limitData['last_used_index'] = $selectedKeyOrigIndex;
        $limitData['keys_usage'] = $keysUsage;

        if (!is_dir(dirname($limitFile))) {
            mkdir(dirname($limitFile), 0777, true);
        }
        file_put_contents($limitFile, json_encode($limitData, JSON_PRETTY_PRINT));

        return $html;
    }
}
