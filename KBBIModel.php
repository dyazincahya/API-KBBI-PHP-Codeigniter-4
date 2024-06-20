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

    public function searchWord($word)
    {
        $htmlData = $this->_fetchHtml($word);

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
                'lema' => $lema,
                'arti' => $arti,
                'tesaurusLink' => $tesaurusLink,
            ];
        }

        return count($dataResponse) ? $dataResponse : false;
    }
}
