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

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('Error fetching HTML: ' . curl_error($ch));
        }

        curl_close($ch);

        return $response;
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

    private function _parserV1($htmlData, $word)
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
    }

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

        return $dataResponse;
    }

    public function searchWord($word)
    {
        // Clean the word
        $cleanWord = $this->_cleanWord($word);

        $htmlData = $this->_fetchHtml($word);

        $dataResponse = [];

        $_parserV1 = $this->_parserV1($htmlData, $cleanWord);
        if(count($_parserV1)){ 
            $dataResponse = $_parserV1;
        } else {
            $_parserV2 = $this->_parserV2($htmlData, $cleanWord);
            if(count($_parserV2)){ 
                $dataResponse = $_parserV2;
            }
        }

        return count($dataResponse) ? $dataResponse : false;
    }
}
