<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Geocoding
{
    const API_TOKEN = 'e97abf966fe53e9a98ca2bef6a348546';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $address
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getGeolocationFromAddress(string $address): array
    {
        try {
            $response = $this->client->request(
                'GET',
                'http://api.positionstack.com/v1/forward',
                [
                    'query' => [
                        'access_key' => self::API_TOKEN,
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
                die;
            }
        }


        return [];
    }
}