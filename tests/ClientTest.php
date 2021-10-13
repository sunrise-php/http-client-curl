<?php

declare(strict_types=1);

namespace Sunrise\Http\Client\Curl\Tests;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Sunrise\Http\Client\Curl\Exception\ClientException;
use Sunrise\Http\Client\Curl\Exception\NetworkException;
use Sunrise\Http\Client\Curl\Exception\RequestException;
use Sunrise\Http\Client\Curl\Client;
use Sunrise\Http\Factory\RequestFactory;
use Sunrise\Http\Factory\ResponseFactory;

class ClientTest extends TestCase
{
    public function testConstructor()
    {
        $client = new Client(new ResponseFactory());
        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    public function testSendRequest()
    {
        $url = 'https://raw.githubusercontent.com';
        $url .= '/sunrise-php/http-client-curl/dea2ea60d8d5b9f0839d8dba8cd714213c1c2b50/LICENSE';
        $client = new Client(new ResponseFactory());
        $request = (new RequestFactory)->createRequest('GET', $url);
        $response = $client->sendRequest($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));
        $this->assertTrue($response->hasHeader('X-Request-Time'));
    }

    public function testSendRequests()
    {
        $requests = [];

        $url = 'https://raw.githubusercontent.com';
        $url .= '/sunrise-php/http-client-curl/dea2ea60d8d5b9f0839d8dba8cd714213c1c2b50/LICENSE';
        $requests[] = (new RequestFactory)->createRequest('GET', $url);

        $url = 'https://raw.githubusercontent.com';
        $url .= '/sunrise-php/http-client-curl/b52d6cf88186101562ed93d9dbbb909ae82924d4/README.md';
        $requests[] = (new RequestFactory)->createRequest('GET', $url);

        $client = new Client(new ResponseFactory());
        $responses = $client->sendRequests(...$requests);

        $this->assertInstanceOf(ResponseInterface::class, $responses[0]);
        $this->assertEquals(200, $responses[0]->getStatusCode());
        $this->assertEquals('OK', $responses[0]->getReasonPhrase());
        $this->assertEquals('text/plain; charset=utf-8', $responses[0]->getHeaderLine('content-type'));
        $this->assertTrue($responses[0]->hasHeader('X-Request-Time'));

        $this->assertInstanceOf(ResponseInterface::class, $responses[1]);
        $this->assertEquals(200, $responses[1]->getStatusCode());
        $this->assertEquals('text/plain; charset=utf-8', $responses[1]->getHeaderLine('content-type'));
        $this->assertTrue($responses[1]->hasHeader('X-Request-Time'));
    }

    public function testSendRequestWithEmptyUri()
    {
        $client = new Client(new ResponseFactory());
        $request = (new RequestFactory)->createRequest('GET', '');

        $this->expectException(NetworkExceptionInterface::class);
        // $this->expectExceptionMessage('<url> malformed');
        $client->sendRequest($request);
    }

    public function testClientException()
    {
        $message = 'foo';
        $code = 1;
        $previous = new RuntimeException('bar');

        $exception = new ClientException($message, $code, $previous);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertInstanceOf(ClientExceptionInterface::class, $exception);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($previous, $exception->getPrevious());
    }

    public function testNetworkException()
    {
        $request = (new RequestFactory)->createRequest('GET', 'http://php.net/');
        $message = 'foo';
        $code = 1;
        $previous = new RuntimeException('bar');

        $exception = new NetworkException($request, $message, $code, $previous);
        $this->assertInstanceOf(ClientException::class, $exception);
        $this->assertInstanceOf(NetworkExceptionInterface::class, $exception);

        $this->assertEquals($request, $exception->getRequest());
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($previous, $exception->getPrevious());
    }

    public function testRequestException()
    {
        $request = (new RequestFactory)->createRequest('GET', 'http://php.net/');
        $message = 'foo';
        $code = 1;
        $previous = new RuntimeException('bar');

        $exception = new RequestException($request, $message, $code, $previous);
        $this->assertInstanceOf(ClientException::class, $exception);
        $this->assertInstanceOf(RequestExceptionInterface::class, $exception);

        $this->assertEquals($request, $exception->getRequest());
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($previous, $exception->getPrevious());
    }
}
