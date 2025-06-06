<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Dto\GoogleSpotDto;
use App\Application\Exceptions\GoogleMapException;
use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use JsonException;

final class GoogleMapService
{
    private const int RADIUS = 2000;

    private const string URI = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=%s,%s&radius=%s&keyword=%s&key=%s";

    public function __construct(private readonly Client $client, private readonly string $apiKey)
    {
    }

    /**
     * @param float $latitude
     * @param float $longitude
     *
     * @return Collection<GoogleSpotDto>
     * @throws GoogleMapException|JsonException
     */
    public function findExchangeSpots(float $latitude, float $longitude): Collection
    {
        $keywords = 'crypto exchange';
        $uri = sprintf(self::URI, $latitude, $longitude, self::RADIUS, $keywords, $this->apiKey);
        $result = $this->sendRequest($uri);

        $collection = array_map(function (array $item) {
            return new GoogleSpotDto(
              name: $item['name'],
              latitude: $item['geometry']['location']['lat'],
              longitude: $item['geometry']['location']['lng'],
              status: $item['business_status'],
              rating: $item['rating'] ?? null,
              vicinity: $item['vicinity'],
              user_rating: $item['user_ratings_total'] ?? null,
            );
        }, $result['results'] ?? []);

        return collect($collection);
    }

    private function sendRequest(string $uri): array
    {
        $response = $this->client->get($uri);

        if ($response->getStatusCode() !== StatusCodeInterface::STATUS_OK) {
            throw new GoogleMapException($response->getReasonPhrase(), $response->getStatusCode());
        }

        return json_decode($response->getBody()->getContents(), associative: true, flags: JSON_THROW_ON_ERROR);
    }

}
