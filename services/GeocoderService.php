<?php

namespace app\services;

use yii\httpclient\Client;
use yii\httpclient\Exception;

class GeocoderService
{
    protected string $apiKey;
    protected Client $client;

    public function __construct()
    {
        $this->apiKey = env('GEOAPIFY_API_KEY');
        $this->client = new Client(['baseUrl' => 'https://api.geoapify.com/v1/geocode/']);
    }

    /**
     * @param string $address
     * @return array
     * @throws Exception
     */
    public function searchByString(string $address): array
    {
        $response = $this->client->get('search', ['text' => $address, 'apiKey' => $this->apiKey])->send();
        $results = [];

        if ($response->isOk) {
            $results = $response->data;
        } else {
            error_log($response->content);
        }

        return $results;
    }
}