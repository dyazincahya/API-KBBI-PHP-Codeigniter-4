<img src="https://raw.githubusercontent.com/dyazincahya/API-KBBI-PHP-Codeigniter-4/main/kbbi.webp" width="150" />

# Unofficial API Kamus Besar Bahasa Indonesia (KBBI) 2026

```json
{
    "api": {
        "name": "API KBBI 2026",
        "source": "https://kbbi.kemendikdasmen.go.id",
        "method": "HTML Parsing"
    },
    "technology": {
        "lang": "PHP 8.3.8",
        "framework": "CodeIgniter 4.6.4",
        "library": [
            "CURL",
            "DOMDocument",
            "DOMXPath",
            "GeoNodeScraperAPI",
        ]
    },
    "author": {
        "name": "Kang Cahya",
        "blog": "https://kang-cahya.com",
        "github": "https://github.com/dyazincahya"
    }
}
```
## Coba API
```
https://openapi.x-labs.my.id/kbbi/search/<PARAM>
```

```
https://openapi.x-labs.my.id/kbbi?search=<PARAM>
```

[Coba Sekarang](https://openapi.x-labs.my.id/kbbi/search/demo)

## Coba API KBBI (Versi GO Lang)
Untuk pengalaman lebih baik, bisa coba API KBBI ini. API ini di bangun dengan menggunakan bahasa GO. Anda dapat melihat kode lengkapnya pada repositori ini [https://github.com/dyazincahya/kbbi-go](https://github.com/dyazincahya/kbbi-go).
```
https://services.x-labs.my.id/kbbi/search?word=param
```

```
https://services.x-labs.my.id/kbbi/randomwords?limit=100
```

[Coba Sekarang](https://services.x-labs.my.id/kbbi/)

## Kompatibel dan sudah di test pada
- PHP 8.5
- Codeigniter 4.7.3 atau lebih baru

## Pustaka yang digunakan
- CURL
- DOMDocument
- DOMXPath
- GeoNode Scraper API (sebagai Fallback)

## Alur Kerja Pengambilan Data (Scraping Workflow)

Untuk menjaga performa dan efisiensi kuota, API ini menggunakan 3 lapis alur kerja cerdas saat mencari sebuah kata:

1. **Lapis 1 - Scraping Langsung (Direct Request):** Jika data belum ada di database, sistem mencoba melakukan koneksi cURL langsung ke situs resmi KBBI. Ini gratis dan tanpa batas (cocok untuk localhost).
2. **Lapis 2 - Fallback GeoNode Scraper API (Automated):** Jika koneksi langsung di atas gagal atau mengalami timeout (terutama saat dideploy di server VPS yang diblokir oleh WAF KBBI), sistem akan otomatis masuk ke blok `catch` dan mengalihkan request menggunakan **GeoNode Scraper API** via Proxy Residential.

---

## 🔌 Integrasi GeoNode Scraper API (Bypass Blokir IP Server)

<img width="1526" height="629" alt="image" src="https://github.com/user-attachments/assets/900b3c8b-a1c9-45f1-be09-bce4095aa3c0" />


Untuk melewati pemblokiran IP Data Center di server hosting/staging, sistem ini telah dilengkapi dengan library integrasi GeoNode Scraper API.

### 1. Cara Mendapatkan API Key Gratis
1. Buka dan daftar akun di website **[GeoNode Scraper API](https://app.geonode.com/scraper-api)**.
2. Selesaikan proses registrasi dan verifikasi email Anda.
3. Setelah masuk ke Dashboard, Anda akan mendapatkan **API Key** unik Anda.
4. Salin API Key tersebut dan masukkan ke dalam file `app/Libraries/GeoNodeScraperAPI.php` pada bagian:
   ```php
   private string $apiKey = 'MASUKKAN_API_KEY_ANDA_DI_SINI';
   ```

### 2. Jatah Kuota & Limitasi
* **Limit Gratis:** Setiap akun baru mendapatkan jatah **1.500 request gratis setiap bulan** yang akan di-renew secara otomatis setiap bulannya.
* **Fitur Safety Capping (Pembatas Lokal):** Library `GeoNodeScraperAPI` dilengkapi pengaman di `writable/geonode_limit.json`. Sistem akan otomatis memblokir request ke GeoNode jika hit bulanan lokal telah menyentuh **1.499 request** agar kuota gratis Anda tidak terlampaui.

### 3. File Library Baru (`app/Libraries/GeoNodeScraperAPI.php`)
Pastikan file ini dibuat untuk menangani pengiriman request API, rotasi proxy residensial, dan pencatatan limit bulanan:

```php
<?php

namespace App\Libraries;

use Exception;

class GeoNodeScraperAPI
{
    private string $apiKey = 'MASUKKAN_API_KEY_ANDA_DI_SINI';
    private string $apiUrl = 'https://scraper.geonode.io/v1/extract';

    public function scrape(string $targetUrl): string
    {
        if (empty($this->apiKey) || $this->apiKey == 'MASUKKAN_API_KEY_ANDA_DI_SINI') {
            throw new Exception('GeoNode Scraper API Key is not configured.');
        }

        $limitFile = WRITEPATH . 'geonode_limit.json';
        $currentMonth = date('Y-m');
        $count = 0;

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
            ]
        ];

        $ch = curl_init($this->apiUrl);
        // ... (konfigurasi cURL & increment count jika sukses)
    }
}
```

### 4. Integrasi ke Model (`app/Models/KBBIModel.php`)
Di dalam model, request cURL langsung dibungkus dalam blok `try-catch` seperti berikut untuk mengaktifkan fitur fallback otomatis:

```php
private function _fetchHtml($word)
{
    try {
        // ... (mencoba request langsung ke server KBBI)
        return $response;
    } catch (Exception $e) {
        // Fallback otomatis jika koneksi langsung gagal
        $targetUrl = 'https://kbbi.kemendikdasmen.go.id/entri/' . rawurlencode($word);
        $geoNode = new \App\Libraries\GeoNodeScraperAPI();
        return $geoNode->scrape($targetUrl);
    }
}
```

---

## Cara Instalasi
- Salin atau unduh kode model (Model) dengan nama [KBBIModel.php](https://github.com/dyazincahya/API-KBBI-PHP-Codeigniter-4/blob/main/KBBIModel.php)
- Salin atau unduh kode kontroler (Controller) dengan nama [ApiKBBI.php](https://github.com/dyazincahya/API-KBBI-PHP-Codeigniter-4/blob/main/ApiKBBI.php)
- Salin atau unduh kode pustaka (Library) dengan nama [GeoNodeScraperAPI.php](https://github.com/dyazincahya/API-KBBI-PHP-Codeigniter-4/blob/main/GeoNodeScraperAPI.php)
- Tambahkan baris router berikut pada file ```\app\Config\Routes.php```
```php
// KBBI Router : \Config\Routes.php
$routes->get('/kbbi', 'ApiKBBI::index');
$routes->get('/kbbi/search/(:any)', 'ApiKBBI::search/$1');
```

## End Point
- /kbbi
- /kbbi?search=```KATA_KUNCI```
- /kbbi/search/```KATA_KUNCI```

## Contoh Respon
#### /kbbi/search/bagaimana
```json
{
    "success": true,
    "status": 200,
    "message": "Results found.",
    "data": [
        {
            "lema": "ba.gai.ma.na bentuk tidak baku: begimana, gimana",
            "arti": [
                {
                    "deskripsi": "pron kata tanya untuk menanyakan cara, perbuatan (lazimnya diikuti kata cara): -- caranya membeli buku dari luar negeri?"
                },
                {
                    "deskripsi": "pron kata tanya untuk menanyakan akibat suatu tindakan: -- kalau dia lari nanti?"
                },
                {
                    "deskripsi": "pron kata tanya untuk meminta pendapat dari kawan bicara (diikuti kata kalau): -- kalau kita pergi ke Puncak?"
                },
                {
                    "deskripsi": "pron kata tanya untuk menanyakan penilaian atas suatu gagasan: -- pendapatmu?"
                }
            ],
            "tesaurusLink": "http://tesaurus.kemdikbud.go.id/tematis/lema/bagaimana"
        }
    ]
}
```

#### /kbbi/search/bagai%20babi%20kelaparan
```json
{
    "success": true,
    "status": 200,
    "message": "Results found.",
    "data": [
        {
            "lema": "babi » bagai babi kelaparan",
            "arti": [
                {
                    "deskripsi": "mengamuk dan bertindak tanpa perhitungan"
                }
            ],
            "tesaurusLink": "http://tesaurus.kemdikbud.go.id/tematis/lema/bagai babi kelaparan"
        }
    ]
}
```

## Optimasi Hosting
Anda dapat melalukan beberapa optimasi pada server hosting agar API ini dapat berjalan dengan lebih optimal. diantaranya sebagai berikut:
1. ```memory_limit```: Ubah ke nilai yang lebih besar, misalnya 256M atau 512M.
2. ```max_execution_time```: Atur ke nilai yang lebih tinggi, misalnya 120 detik atau lebih, sesuai kebutuhan.
3. Aktifkan ```OPcache```, pastikan versi PHP yang Anda gunakan mendukung OPcache (biasanya versi 7.0 ke atas)
4. Aktifkan ekstensi PHP ```dom``` atau ```simplexml```

## Aplikasi KBBI untuk Android
![MyKBBI](https://play-lh.googleusercontent.com/CC7HRNLH2h2Gd6CUvBAQJOKphi9wU1Wbwr-eXlaXtOB56Mmp3hX5jYdhlUloQZeJTUw=w240-h480-rw)

[MyKBBI - Kamus Bahasa Indonesia](https://play.google.com/store/apps/details?id=com.kang.cahya.apps.mykbbi) (Unduh via Google Play Store)

## KBBI SQL Database
Apabila tidak ingin menggunakan API, Anda juga dapat mengimpor data kata dan peribahasa ke dalam basis data pribadi. Anda dapat mengunduh basis datanya di sini: [KBBI-SQL-Database](https://github.com/dyazincahya/KBBI-SQL-database). Tersedia untuk MySQL, SQLite dan PostgreSQL. Juga tersedia untuk format data CSV, JSON, Markdown, PHP Array, XML, DbUnit, HTML

## Log Perubahan
[Lihat Log Perubahan](https://github.com/dyazincahya/API-KBBI-PHP-Codeigniter-4/releases)

## Penulis
[Kang Cahya](https://kang-cahya.com)
