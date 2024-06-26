<img src="https://raw.githubusercontent.com/dyazincahya/API-KBBI-PHP-Codeigniter-4/main/kbbi.webp" width="150" />

# Unofficial API Kamus Besar Bahasa Indonesia (KBBI) 2024

```json
{
    "api": {
        "name": "API KBBI 2024",
        "source": "https://kbbi.kemdikbud.go.id",
        "method": "HTML Parsing"
    },
    "technology": {
        "lang": "PHP 8.3.8",
        "framework": "CodeIgniter 4.3.8",
        "library": [
            "CURL",
            "DOMDocument",
            "DOMXPath"
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
https://x-labs.my.id/api/kbbi/search/<PARAM>
```

```
https://x-labs.my.id/api/kbbi?search=<PARAM>
```

[Coba Sekarang](https://x-labs.my.id/api/kbbi/search/demo)

## Kompatibel dengan
- PHP 8.3.8
- Codeigniter 4.3.8 atau lebih baru

## Pustaka yang digunakan
- CURL
- DOMDocument
- DOMXPath

## Cara Instalasi
- Salin atau unduh kode model (Model) dengan nama [KBBIModel.php](https://github.com/dyazincahya/API-KBBI-PHP-Codeigniter-4/blob/main/KBBIModel.php)
- Salin atau unduh kode kontroler (Controller) dengan nama [ApiKBBI.php](https://github.com/dyazincahya/API-KBBI-PHP-Codeigniter-4/blob/main/ApiKBBI.php)
- Tambahkan baris router berikut pada file ```\app\Config\Routes.php```
```php
// KBBI Router : \Config\Routes.php
$routes->get('/api/kbbi', 'ApiKBBI::index');
$routes->get('/api/kbbi/search/(:any)', 'ApiKBBI::search/$1');
```

## End Point
- /api/kbbi
- /api/kbbi?search=```KATA_KUNCI```
- /api/kbbi/search/```KATA_KUNCI```

## Contoh Respon
#### /api/kbbi/search/bagaimana
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

#### /api/kbbi/search/bagai%20babi%20kelaparan
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

## Log Perubahan
[Lihat Log Perubahan](https://github.com/dyazincahya/API-KBBI-PHP-Codeigniter-4/releases)

## Penulis
[Kang Cahya](https://kang-cahya.com)
