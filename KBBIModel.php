<?php

namespace App\Models;

use CodeIgniter\Model;
use DOMDocument;
use DOMXPath;

class KBBIModel extends Model
{
    private function _user_agent(){
        $userAgents = [
            // Chrome (Desktop)
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36",

            // Chrome (Mobile)
            "Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Mobile Safari/537.36",
            "Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1",

            // Firefox (Desktop)
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:114.0) Gecko/20100101 Firefox/114.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:114.0) Gecko/20100101 Firefox/114.0",
            "Mozilla/5.0 (X11; Linux x86_64; rv:114.0) Gecko/20100101 Firefox/114.0",

            // Firefox (Mobile)
            "Mozilla/5.0 (Android 10; Mobile; rv:114.0) Gecko/114.0 Firefox/114.0",
            "Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) FxiOS/114.0 Mobile/15E148 Safari/604.1",

            // Edge (Desktop)
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.0.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.0.0",

            // Safari (Desktop)
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Safari/605.1.15",

            // Safari (Mobile)
            "Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1",
            "Mozilla/5.0 (iPad; CPU OS 16_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Mobile/15E148 Safari/604.1",

            // Opera (Desktop)
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 OPR/100.0.0.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 OPR/100.0.0.0",

            // Opera (Mobile)
            "Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Mobile Safari/537.36 OPR/74.0.0.0",

            // Samsung Internet
            "Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Mobile Safari/537.36 SamsungBrowser/18.0",

            // Internet Explorer
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; Trident/7.0; rv:11.0) like Gecko",

            // UC Browser (Mobile)
            "Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/114.0.0.0 Mobile Safari/537.36 UCBrowser/13.4.0.1306",

            // Brave (Desktop)
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Brave/114.0.0.0",

            // New User Agents Added
            "Mozilla/5.0 (Linux; Android 13; SM-S911B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Mobile Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36",
            "Mozilla/5.0 (iPad; CPU OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/605.1.15",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36",
            "Mozilla/5.0 (Android 11; Mobile; rv:117.0) Gecko/117.0 Firefox/117.0",
            "Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1",
        ];

        $userAgent = $userAgents[array_rand($userAgents)];

        return $userAgent;
    }

    private function _fetchHtml($word)
    {
        $userAgents = $this->_user_agent();
        $encodedWord = rawurlencode($word); 
        $url = "https://kbbi.kemdikbud.go.id/entri/" . $encodedWord;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL host verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL peer verification
        curl_setopt($ch, CURLOPT_USERAGENT, 'Localhost');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Forwarded-For: 127.0.0.1']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgents);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('Error fetching HTML: ' . curl_error($ch));
        }

        curl_close($ch);

        return $response;
    }

    private function _request__KBBI_API_Zhirrr($word)
    {
        $userAgents = $this->_user_agent();
        $encodedWord = rawurlencode($word);
        $url = "https://kbbi-api-zhirrr.vercel.app/api/kbbi?text=" . $encodedWord;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL host verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL peer verification
        curl_setopt($ch, CURLOPT_USERAGENT, 'Localhost');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Forwarded-For: 127.0.0.1']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgents);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Error: ' . curl_error($ch));
        }

        curl_close($ch);

        // Decode JSON response ke array association
        $result = json_decode($response, true);
        
        // Periksa apakah dekoding berhasil
        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        } else {
            // echo 'Error decoding JSON: ' . json_last_error_msg();
            return [];
        }
    }

    private function _cleanText($text)
    {
        return preg_replace('/\s+/', ' ', trim($text));
    }

    private function _cleanWord($word)
    {
        // Remove non-alphanumeric characters except spaces
        $cleanWord = preg_replace('/[^a-zA-Z0-9\s]/', '', $word);
        // Replace multiple spaces with a single space
        return preg_replace('/\s+/', ' ', strtolower(trim($cleanWord)));
    }

    // parserV1 disabled because has been enhance in parserV3
    /*private function _parserV1($htmlData, $word)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($htmlData);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $dataResponse = [];

        $bodyContent = $xpath->query("//div[contains(@class, 'body-content')]")->item(0);
        if (!$bodyContent) {
            return false;
        }

        $h2Elements = $xpath->query("//div[contains(@class, 'body-content')]/h2");
        foreach ($h2Elements as $i => $h2Element) {
            $lema = $this->_cleanText($h2Element->textContent);
            $arti = [];
            $tesaurusLink = '';

            // Get Tesaurus link if it exists
            $tesaurusAnchor = $xpath->query("following-sibling::p/a[contains(text(), 'Tesaurus')]", $h2Element)->item(0);
            if ($tesaurusAnchor) {
                $tesaurusLink = $tesaurusAnchor->getAttribute('href');
            }

            $nextSibling = $h2Element->nextSibling;
            while ($nextSibling && ($nextSibling->nodeName !== 'h2' && $nextSibling->nodeName !== 'hr')) {
                if ($nextSibling->nodeName === 'ul' || $nextSibling->nodeName === 'ol') {
                    $listItems = $xpath->query('.//li', $nextSibling);
                    foreach ($listItems as $j => $listItem) {
                        $deskripsi = $this->_cleanText(preg_replace('/<(?:.|\n)*?>/', '', $listItem->C14N()));
                        $arti[$j] = ['deskripsi' => $deskripsi];
                    }
                }
                $nextSibling = $nextSibling->nextSibling;
            }

            $dataResponse[$i] = [
                'word' => $word,
                'lema' => $lema,
                'arti' => $arti,
                'tesaurusLink' => $tesaurusLink,
            ];
        }

        return count($dataResponse) ? $dataResponse : [];
    }*/

    private function _parserV2($htmlData, $word)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($htmlData);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $dataResponse = [];

        $contentDiv = $xpath->query("//div[contains(@class, 'container body-content')]")->item(0);
        if (!$contentDiv) {
            return false;
        }

        // Mengambil semua elemen h2 dalam div body-content
        $h2Elements = $xpath->query(".//h2[contains(@style, 'margin-bottom:3px')]", $contentDiv);
        foreach ($h2Elements as $i => $h2Element) {
            // Mengambil lema dari link a di dalam span rootword
            $lemaLink = $xpath->query(".//span[contains(@class, 'rootword')]/a", $h2Element)->item(0);
            $lema = '';
            if ($lemaLink) {
                $lema = $this->_cleanText($lemaLink->nodeValue);
            }

            // Mengambil link Tesaurus
            $tesaurusLink = '';
            $tesaurusAnchor = $xpath->query(".//p/a[contains(@href, 'tematis/lema')]", $h2Element)->item(0);
            if ($tesaurusAnchor) {
                $tesaurusLink = $tesaurusAnchor->getAttribute('href');
            } else {
                $tesaurusLink = "http://tesaurus.kemdikbud.go.id/tematis/lema/".$word;
            }

            // Mengambil deskripsi/arti dari ul/li setelah h2
            $ulElement = $xpath->query("following-sibling::ul[@class='adjusted-par'][1]", $h2Element)->item(0);
            $arti = [];
            if ($ulElement) {
                $listItems = $xpath->query(".//li", $ulElement);
                foreach ($listItems as $j => $listItem) {
                    $deskripsi = $this->_cleanText($listItem->nodeValue);
                    $arti[] = ['deskripsi' => $deskripsi];
                }
            }

            // Menyimpan data dalam $dataResponse
            if (!empty($lema) && !empty($arti)) {
                $dataResponse[] = [
                    'word' => $word,
                    'lema' => $lema . " » " . $word,
                    'arti' => $arti,
                    'tesaurusLink' => $tesaurusLink,
                ];
            }
        }

        return count($dataResponse) ? $dataResponse : [];
    }

    private function _parserV3($htmlData, $word)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($htmlData);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $dataResponse = [];

        // Mengambil semua elemen h2 yang memiliki style 'margin-bottom:3px'
        $h2Elements = $xpath->query("//h2[contains(@style, 'margin-bottom:3px')]");
        foreach ($h2Elements as $h2Element) {
            // Mengambil teks dari elemen h2
            $lema = $this->_cleanText($h2Element->textContent);

            // Mengambil link Tesaurus dari elemen <p><a>
            $tesaurusLink = '';
            $tesaurusAnchor = $xpath->query("following-sibling::p[1]/a[contains(@href, 'tematis/lema')]", $h2Element)->item(0);
            if ($tesaurusAnchor) {
                $tesaurusLink = $tesaurusAnchor->getAttribute('href');
            } else {
                $tesaurusLink = "http://tesaurus.kemdikbud.go.id/tematis/lema/" . $lema;
            }

            // Mengambil deskripsi/arti dari ol/li setelah h2
            $arti = [];
            $olElement = $xpath->query("following-sibling::ol[1]", $h2Element)->item(0);
            if ($olElement) {
                $listItems = $xpath->query(".//li", $olElement);
                foreach ($listItems as $listItem) {
                    $deskripsi = $this->_cleanText($listItem->nodeValue);
                    $arti[] = ['deskripsi' => $deskripsi];
                }
            }

            // Mengambil deskripsi/arti dari ul/li setelah h2
            $ulElement = $xpath->query("following-sibling::ul[@class='adjusted-par'][1]", $h2Element)->item(0);
            if ($ulElement) {
                $listItems = $xpath->query(".//li", $ulElement);
                foreach ($listItems as $listItem) {
                    $deskripsi = $this->_cleanText($listItem->nodeValue);
                    $arti[] = ['deskripsi' => $deskripsi];
                }
            }

            // Menyimpan data dalam $dataResponse
            if (!empty($lema) && !empty($arti)) {
                $dataResponse[] = [
                    'word' => $word,
                    'lema' => $lema,
                    'arti' => $arti,
                    'tesaurusLink' => $tesaurusLink,
                ];
            }
        }

        return count($dataResponse) ? $dataResponse : [];
    }

    private function _KBBI_official($word)
    {
        // Clean the word
        $cleanWord = $this->_cleanWord($word);

        // If not found in the database, fetch from the web
        $htmlData = $this->_fetchHtml($word);

        // parserV1 disabled because has been enhance in parserV3
        /*$_parserV1 = $this->_parserV1($htmlData, $cleanWord);
        if(count($_parserV1)){
            $dataResponse = $_parserV1;

            return $dataResponse;
        }*/

        $_parserV2 = $this->_parserV2($htmlData, $cleanWord);
        if(count($_parserV2))
        {
            return $_parserV2;
        }

        $_parserV3 = $this->_parserV3($htmlData, $cleanWord);
        if(count($_parserV3))
        {
            return $_parserV3;
        }

        return [];
    }

    private function _KBBI_byZhirrr($word): array
    {
        // Clean the word
        $cleanWord = $this->_cleanWord($word);

        $response = $this->_request__KBBI_API_Zhirrr($word);
        $dataResponse = [];

        if(count($response))
        {
            $arti = [];
            $tesaurusLink = "http://tesaurus.kemdikbud.go.id/tematis/lema/" . $cleanWord;

            foreach ($response['arti'] as $k => $v) {
                $arti[$k]['deskripsi'] = $v;
            }

            $dataResponse[] = [
                'word' => $cleanWord,
                'lema' => $response['lema'],
                'arti' => $arti,
                'tesaurusLink' => $tesaurusLink,
            ];

            return $dataResponse;
        } 
        
        return $dataResponse;
    }

    public function searchWord($word)
    {
        try {
            // OFFICIAL
            $_KBBI_official = $this->_KBBI_official($word);
            if(count($_KBBI_official))
            {
                return $_KBBI_official;
            }
        } catch (\Exception $e) {
            // ZHIRRR
            $_KBBI_byZhirrr = $this->_KBBI_byZhirrr($word);
            if(count($_KBBI_byZhirrr))
            {
                return $_KBBI_byZhirrr;
            }
            // Log the error message or handle it as needed
            // error_log("Official API error: " . $e->getMessage());
        }
        
        return false;
    }
}
