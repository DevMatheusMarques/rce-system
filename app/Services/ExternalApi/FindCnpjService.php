<?php

namespace App\Services\ExternalApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class FindCnpjService
{
    protected Client $client;

    public function __construct()
    {
        $certPath = storage_path('app\cacert.pem');

        $this->client = new Client([
            'verify' => $certPath,
        ]);
    }

    public function checkCnpj(string $cnpj): array
    {
        try {

            $apiUrl = 'https://api-publica.speedio.com.br/buscarcnpj';

            $response = $this->client->get($apiUrl  . '?cnpj=' . $cnpj);
            $body = $response->getBody();

            $data = json_decode($body, true);

            if (isset($data['error'])) {
                throw new \Exception($data['error']);
            }

            return [
                'success' => true,
                'message' => 'Fornecedor encontrado.',
                'model' => $data
            ];

        } catch (RequestException $e) {

            if ($e->hasResponse()) {
                $errorResponse = json_decode($e->getResponse()->getBody(), true);

                return [
                    'success' => false,
                    'message' => $errorResponse,
                    'model' => null
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'model' => null
                ];
            }
        }
    }
}
