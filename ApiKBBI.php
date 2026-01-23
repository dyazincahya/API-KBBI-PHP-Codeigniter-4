<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\KBBIModel;

class ApiKBBI extends BaseController
{
    public function index(): ResponseInterface
    {
        $search = $this->request->getGet('search');

        if(!empty(isset($search)))
        {
            return $this->search($search);
        }
        
        return $this->response->setJSON([
            'api' => [
                "name" => "API KBBI 2024",
                "source" => "https://kbbi.kemendikdasmen.go.id",
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

    public function search($word=null): ResponseInterface
    {
        if(!empty($word))
        {
            $model = new KBBIOnlyModel();

            try {
                $result = $model->searchWord($word);
                if ($result) {
                    return $this->response->setHeader('Content-Type', 'application/json')->setJSON([
                        'success' => true,
                        'status' => 200,
                        'message' => "Hasil ditemukan.",
                        'data' => $result,
                    ]);
                }

                return $this->response->setHeader('Content-Type', 'application/json')->setJSON([
                    'success' => true,
                    'status' => 404,
                    'message' => 'Hasil tidak ditemukan!',
                    'data' => [],
                ], ResponseInterface::HTTP_NOT_FOUND);
            } catch (\Exception $e) {
                return $this->response->setHeader('Content-Type', 'application/json')->setJSON([
                    'success' => false,
                    'status' => 500,
                    'message' => $e->getMessage(),
                    'data' => [],
                ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->response->setHeader('Content-Type', 'application/json')->setJSON([
            'success' => false,
            'status' => 404,
            'message' => 'Parameter tidak ditemukan!',
            'data' => [],
        ], ResponseInterface::HTTP_NOT_FOUND);
    }
}
