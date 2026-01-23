<?php

namespace App\Models;

use CodeIgniter\Model;
use DOMDocument;
use DOMXPath;
use Exception;

class KBBIModel extends Model
{
  private function _user_agent()
  {
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
    $kbbiHost = 'kbbi.kemendikdasmen.go.id';
    $kbbiBaseUrl = 'https://' . $kbbiHost;
    $userAgent = $this->_user_agent();
    $headers = [
      "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
      "Accept-Encoding: gzip, deflate",
      "Accept-Language: en-US,en;q=0.5",
      "Connection: keep-alive",
      "Host: " . $kbbiHost,
      "Referer: " . $kbbiBaseUrl . "/",
      "Upgrade-Insecure-Requests: 1",
    ];
    // Path ke file cookie dalam folder writable
    $cookieDir = WRITEPATH . 'cookies/';
    if (!is_dir($cookieDir)) {
      mkdir($cookieDir, 0777, true);
    }
    $cookieFile = $cookieDir . 'kbbi-kemendikdasmen-goid.txt';

    $encodedWord = rawurlencode($word);
    $url = $kbbiBaseUrl . "/entri/" . $encodedWord;
    $ch = curl_init($url);
    $responseHeaders = [];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $headerLine) use (&$responseHeaders) {
      $len = strlen($headerLine);
      $line = trim($headerLine);
      if ($line === '' || !str_contains($line, ':')) {
        return $len;
      }

      [$name, $value] = explode(':', $line, 2);
      $name = strtolower(trim($name));
      $value = trim($value);
      if ($name !== '') {
        if (!array_key_exists($name, $responseHeaders)) {
          $responseHeaders[$name] = $value;
        } else {
          $responseHeaders[$name] .= ', ' . $value;
        }
      }

      return $len;
    });

    // Gunakan file cookie untuk menyimpan sesi
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      $error_msg = curl_error($ch);
      $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
      unset($ch);
      throw new Exception('cURL Error: ' . $error_msg . ($effectiveUrl ? ' (url: ' . $effectiveUrl . ')' : ''));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    unset($ch);

    $isCloudflare =
      (isset($responseHeaders['server']) && stripos($responseHeaders['server'], 'cloudflare') !== false) ||
      isset($responseHeaders['cf-ray']) ||
      isset($responseHeaders['cf-mitigated']) ||
      isset($responseHeaders['cf-cache-status']);

    if ($httpCode === 200 && $isCloudflare && is_string($response)) {
      $challengeMarkers = [
        'cf-chl',
        '__cf_chl',
        'cf-browser-verification',
        'Just a moment',
        'Checking your browser',
        'Attention Required',
      ];
      foreach ($challengeMarkers as $marker) {
        if (stripos($response, $marker) !== false) {
          $ray = $responseHeaders['cf-ray'] ?? null;
          throw new Exception('Cloudflare challenge page detected' . ($ray ? ' (cf-ray: ' . $ray . ')' : '') . ($effectiveUrl ? ' (url: ' . $effectiveUrl . ')' : ''));
        }
      }
    }

    if ($httpCode !== 200) {
      if ($isCloudflare && in_array($httpCode, [403, 429, 503], true)) {
        $ray = $responseHeaders['cf-ray'] ?? null;
        throw new Exception('Blocked by Cloudflare (HTTP status code: ' . $httpCode . ')' . ($ray ? ' (cf-ray: ' . $ray . ')' : '') . ($effectiveUrl ? ' (url: ' . $effectiveUrl . ')' : ''));
      }
      throw new Exception('Failed to fetch HTML, HTTP status code: ' . $httpCode . ($effectiveUrl ? ' (url: ' . $effectiveUrl . ')' : ''));
    }

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
        $tesaurusLink = $tesaurusAnchor instanceof \DOMElement ? $tesaurusAnchor->getAttribute('href') : '';
      } else {
        $tesaurusLink = "http://tesaurus.kemendikdasmen.go.id/tematis/lema/" . $word;
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
        $tesaurusLink = $tesaurusAnchor instanceof \DOMElement ? $tesaurusAnchor->getAttribute('href') : '';
      } else {
        $tesaurusLink = "http://tesaurus.kemendikdasmen.go.id/tematis/lema/" . $lema;
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

    $_parserV2 = $this->_parserV2($htmlData, $cleanWord);
    if (count($_parserV2)) {
      return $_parserV2;
    }

    $_parserV3 = $this->_parserV3($htmlData, $cleanWord);
    if (count($_parserV3)) {
      return $_parserV3;
    }

    return [];
  }

  public function searchWord($word)
  {
    try {
      $_KBBI_official = $this->_KBBI_official($word);
      if (count($_KBBI_official)) {
        return $_KBBI_official;
      }
    } catch (\Exception $e) {
      return false;
    }

    return false;
  }
}
