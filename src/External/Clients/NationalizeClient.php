<?php

declare(strict_types=1);

namespace App\External\Clients;

use App\External\ExternalApiClientInterface;
use App\External\HttpClient;
use Exception;

final class NationalizeClient implements ExternalApiClientInterface
{
    private HttpClient $httpClient;
    private const BASE_URL = 'https://api.nationalize.io';

    public function __construct()
    {
        $this->httpClient = new HttpClient();
    }

    public function getName(): string
    {
        return 'Nationalize';
    }

    public function fetch(string $name): array
    {
        $url = self::BASE_URL . '?name=' . urlencode($name);
        return $this->httpClient->get($url);
    }

    public function validate(array $response): array
    {
        // Check if countries array exists and has data
        if (empty($response['country']) || !is_array($response['country'])) {
            throw new Exception('Nationalize API returned no country data');
        }

        // Sort by probability and get highest
        $countries = $response['country'];
        usort($countries, fn($a, $b) => $b['probability'] <=> $a['probability']);

        $topCountry = $countries[0];

        return [
            'country_id' => $topCountry['country_id'],
            'country_probability' => (float)$topCountry['probability'],
        ];
    }
}
