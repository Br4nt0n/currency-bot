<?php

declare(strict_types=1);

namespace Application\Services;

use App\Application\Dto\GoogleSpotDto;
use App\Application\Exceptions\GoogleMapException;
use App\Application\Services\GoogleMapService;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;

class GoogleMapServiceTest extends TestCase
{
    private GoogleMapService $service;
    private ResponseInterface|MockObject $response;
    private string $apiKey;

    protected function setUp(): void
    {
        parent::setUp();
        $client = $this->getMockBuilder(Client::class)->getMock();
        $this->response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $client->method('get')->willReturn($this->response);
        $this->apiKey = 'some_key';

        $this->service = new GoogleMapService($client, $this->apiKey);
    }

    public function testFindExchangeSpotSuccess(): void
    {
        $map['results'] = [
            [
                'name' => 'One',
                'geometry' => [
                    'location' => [
                        'lat' => 466223.1,
                        'lng' => 85696593.3,
                    ],
                ],
                'business_status' => 'OPERATIONAL',
                'vicinity' => 'Street 1 apt 1',
            ],
            [
                'name' => 'Two',
                'geometry' => [
                    'location' => [
                        'lat' => 134545.1,
                        'lng' => 987674.3,
                    ],
                ],
                'rating' => 356.0,
                'business_status' => 'OPERATIONAL',
                'vicinity' => 'Street 2 apt 2',
                'user_ratings_total' => 5,
            ]
        ];

        $stream = $this->getMockBuilder(StreamInterface::class)->getMock();
        $this->response->expects(self::once())->method('getStatusCode')->willReturn(200);
        $this->response->expects(self::once())->method('getBody')->willReturn($stream);
        $stream->expects(self::once())->method('getContents')->willReturn(json_encode($map));

        $result = $this->service->findExchangeSpots(12345.1, -23466);

        $this->assertInstanceOf(Collection::class, $result);

        foreach ($result as $key => $item) {
            $this->assertInstanceOf(GoogleSpotDto::class, $item);
            $this->assertSame($map['results'][$key]['name'], $item->name);
            $this->assertSame($map['results'][$key]['business_status'], $item->status);
            $this->assertSame($map['results'][$key]['geometry']['location']['lat'], $item->latitude);
            $this->assertSame($map['results'][$key]['geometry']['location']['lng'], $item->longitude);
            $this->assertSame($map['results'][$key]['vicinity'], $item->vicinity);
            if (isset($map['results'][$key]['rating'])) {
                $this->assertSame($map['results'][$key]['rating'], $item->rating);
            }
            if (isset($map['results'][$key]['user_ratings_total'])) {
                $this->assertSame($map['results'][$key]['user_ratings_total'], $item->user_rating);
            }
        }
    }

    public function testFindExchangeSpotClientFail(): void
    {
        $this->response->expects(self::exactly(2))->method('getStatusCode')->willReturn(500);
        $this->response->expects(self::once())->method('getReasonPhrase')->willReturn('because');
        $this->expectException(GoogleMapException::class);
        $this->expectExceptionMessage('because');

        $this->service->findExchangeSpots(12345.1, -23466);
    }

    public function testFindExchangeSpotEmptyResponse(): void
    {
        $stream = $this->getMockBuilder(StreamInterface::class)->getMock();
        $this->response->expects(self::once())->method('getStatusCode')->willReturn(200);
        $this->response->expects(self::once())->method('getBody')->willReturn($stream);
        $stream->expects(self::once())->method('getContents')->willReturn(json_encode(['results' => []]));

        $result = $this->service->findExchangeSpots(12345.1, -23466);
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }
}
