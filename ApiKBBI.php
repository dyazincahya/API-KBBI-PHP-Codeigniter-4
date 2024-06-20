<?php

namespace App\Controllers;

use App\Models\KBBIModel;
use CodeIgniter\HTTP\ResponseInterface;

class ApiKBBI extends BaseController
{
    public function index()
    {
        return $this->response->setJSON([
            'api' => [
                "name" => "API KBBI 2024",
                "source" => "https://kbbi.kemdikbud.go.id",
                "method" => "HTML Parsing"
            ],
            'technology' => [
                "lang" => "PHP 8.3.8",
                "framework" => "CodeIgniter 4.3.8",
                "library" => ["CURL", "DOMDocument", "DOMXPath"]
            ],
            'author' => [
                "name" => "Kang Cahya",
                "blog" => "https://kang-cahya.com",
                "github" => "https://github.com/dyazincahya"
            ]
        ]);
    }

    public function search($word)
    {
        $model = new KBBIModel();

        try {
            $result = $model->searchWord($word);
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 200,
                    'message' => "Results found.",
                    'data' => $result,
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => true,
                    'status' => 404,
                    'message' => 'No results found.',
                ], ResponseInterface::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'status' => 500,
                'message' => $e->getMessage(),
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
