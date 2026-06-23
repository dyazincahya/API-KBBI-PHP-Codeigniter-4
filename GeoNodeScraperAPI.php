<?php

namespace App\Libraries;

use Exception;

class GeoNodeScraperAPI
{
    private string $apiKey = 'YOU_API_KEY_HERE';
    private string $apiUrl = 'https://scraper.geonode.io/v1/extract';

    /**
     * Scrape HTML from a target URL using GeoNode Scraper API.
     *
     * @param string $targetUrl
     * @return string
     * @throws Exception
     */
    public function scrape(string $targetUrl): string
    {
        if (empty($this->apiKey) || $this->apiKey == 'YOU_API_KEY_HERE') {
            throw new Exception('GeoNode Scraper API Key is not configured.');
        }

        $limitFile = WRITEPATH . 'geonode_limit.json';
        $currentMonth = date('Y-m');
        $count = 0;

        // Check monthly request limit
        if (file_exists($limitFile)) {
            $data = json_decode(file_get_contents($limitFile), true);
            if (isset($data['month']) && $data['month'] === $currentMonth) {
                $count = (int)($data['count'] ?? 0);
            }
        }

        if ($count >= 1499) {
            throw new Exception('GeoNode Scraper API limit reached (max 1499 requests/month).');
        }

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
            'x-api-key: ' . $this->apiKey,
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

        // Increment and save the monthly request count
        $count++;
        if (!is_dir(dirname($limitFile))) {
            mkdir(dirname($limitFile), 0777, true);
        }
        file_put_contents($limitFile, json_encode([
            'month' => $currentMonth,
            'count' => $count
        ], JSON_PRETTY_PRINT));

        return $html;
    }
}
