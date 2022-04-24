<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Geocoding
{
    private HttpClientInterface $client;
    private string $api_token;

    public function __construct(string $api_token ,HttpClientInterface $client)
    {
        $this->client = $client;
        $this->api_token = $api_token;
    }

    /**
     * @param string $address
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Throwable
     */
    public function getGeolocationFromAddress(string $address): array
    {
        try {
            $response = $this->client->request(
                'GET',
                'http://api.positionstack.com/v1/forward',
                [
                    'query' => [
                        'access_key' => $this->api_token,
                        'query' => $address,
                        'output' => 'json',
                        'limit' => 1
                    ]
                ]
            );

            if ($response->getStatusCode() === 200) {
                return reset($response->toArray()['data']);
            }
        } catch (\Throwable $e) {
            var_dump($address);;
            if (isset($response)) {
                var_dump($response->getContent());
            }
            throw $e;
        }


        return [];
    }
}