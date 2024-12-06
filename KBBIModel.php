<?php

namespace App\Models;

use CodeIgniter\Model;
use DOMDocument;
use DOMXPath;

class KBBIModel extends Model
{
    protected $table = 'kbbi_entries';

    private function _fetchHtml($word)
    {
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

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('Error fetching HTML: ' . curl_error($ch));
        }

        curl_close($ch);

        return $response;
    }

    private function _request__KBBI_API_Zhirrr($word)
    {
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
        $doc = Dom\HTMLDocument::createFromString($htmlData, LIBXML_NOERROR);
        $dataResponse = [];
    
        $contentDiv = $doc->querySelector("div.container.body-content");
        if (!$contentDiv) {
            return false;
        }
    
        // Mengambil semua elemen h2 dalam div body-content
        foreach ($contentDiv->querySelectorAll("h2[style*='margin-bottom:3px']") as $h2Element) {
            // Mengambil lema dari link a di dalam span rootword
            $lemaLink = $h2Element->querySelector("span.rootword > a");
            $lema = $lemaLink ? $this->_cleanText($lemaLink->textContent) : '';
    
            // Mengambil link Tesaurus
            $tesaurusLink = $h2Element->querySelector("p > a[href*='tematis/lema']")?->getAttribute('href') ?? "http://tesaurus.kemdikbud.go.id/tematis/lema/" . $word;
    
            // Mengambil deskripsi/arti dari ul/li setelah h2
            $ulElement = $h2Element->nextElementSibling?->classList->contains('adjusted-par') ? $h2Element->nextElementSibling : null;
            $arti = [];
            if ($ulElement) {
                foreach ($ulElement->querySelectorAll("li") as $listItem) {
                    $deskripsi = $this->_cleanText($listItem->textContent);
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
        $doc = Dom\HTMLDocument::createFromString($htmlData, LIBXML_NOERROR);
        $dataResponse = [];
    
        // Mengambil semua elemen h2 yang memiliki style 'margin-bottom:3px'
        foreach ($doc->querySelectorAll("h2[style*='margin-bottom:3px']") as $h2Element) {
            $lema = $this->_cleanText($h2Element->textContent);
    
            // Mengambil link Tesaurus dari elemen <p><a>
            $tesaurusLink = $h2Element->nextElementSibling?->querySelector("a[href*='tematis/lema']")?->getAttribute('href') ?? "http://tesaurus.kemdikbud.go.id/tematis/lema/" . $lema;
    
            // Mengambil deskripsi/arti dari ol/li setelah h2
            $arti = [];
            $olElement = $h2Element->nextElementSibling?->tagName === 'OL' ? $h2Element->nextElementSibling : null;
            if ($olElement) {
                foreach ($olElement->querySelectorAll("li") as $listItem) {
                    $deskripsi = $this->_cleanText($listItem->textContent);
                    $arti[] = ['deskripsi' => $deskripsi];
                }
            }
    
            // Mengambil deskripsi/arti dari ul/li setelah h2
            $ulElement = $h2Element->nextElementSibling?->classList->contains('adjusted-par') ? $h2Element->nextElementSibling : null;
            if ($ulElement) {
                foreach ($ulElement->querySelectorAll("li") as $listItem) {
                    $deskripsi = $this->_cleanText($listItem->textContent);
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
