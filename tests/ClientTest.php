<?php

declare(strict_types=1);

namespace Sunrise\Http\Client\Curl\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\NetworkExceptionInterface;
use Sunrise\Http\Client\Curl\Client;
use Sunrise\Http\Client\Curl\MultiRequest;
use Sunrise\Http\Message\RequestFactory;
use Sunrise\Http\Message\ResponseFactory;
use const CURLOPT_VERBOSE;

final class ClientTest extends TestCase
{
    private const TEST_URI = 'https://www.php.net/robots.txt';

    public function testSendSingleRequest(): void
    {
        $request = (new RequestFactory())->createRequest('GET', self::TEST_URI);
        $response = (new Client(new ResponseFactory()))->sendRequest($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($response->hasHeader('X-Request-Time'));
    }

    public function testSendMultiRequest(): void
    {
        $requestFactory = new RequestFactory();

        $request = new MultiRequest(
            foo: $requestFactory->createRequest('GET', self::TEST_URI),
            bar: $requestFactory->createRequest('GET', self::TEST_URI),
        );

        $responses = (new Client(new ResponseFactory()))->sendRequest($request)->getResponses();

        self::assertArrayHasKey('foo', $responses);
        self::assertSame(200, $responses['foo']->getStatusCode());
        self::assertTrue($responses['foo']->hasHeader('X-Request-Time'));

        self::assertArrayHasKey('bar', $responses);
        self::assertSame(200, $responses['bar']->getStatusCode());
        self::assertTrue($responses['bar']->hasHeader('X-Request-Time'));
    }

    public function testSendSingleRequestWithEmptyUri(): void
    {
        $client = new Client(new ResponseFactory());
        $request = (new RequestFactory())->createRequest('GET', '');

        $this->expectException(NetworkExceptionInterface::class);
        // $this->expectExceptionMessage('<url> malformed');
        $client->sendRequest($request);
    }

    public function testSendMultiRequestWithEmptyUri(): void
    {
        $client = new Client(new ResponseFactory(), curlOptions: [CURLOPT_VERBOSE => true]);
        $request = new MultiRequest((new RequestFactory())->createRequest('GET', ''));

        $this->expectException(NetworkExceptionInterface::class);
        // $this->expectExceptionMessage('<url> malformed');
        $client->sendRequest($request);
    }
}
