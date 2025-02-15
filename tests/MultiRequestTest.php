<?php

declare(strict_types=1);

namespace Sunrise\Http\Client\Curl\Tests;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Sunrise\Http\Client\Curl\MultiRequest;

final class MultiRequestTest extends TestCase
{
    public function testCreateWithoutRequests(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MultiRequest();
    }

    public function testGetRequests(): void
    {
        $requests = [
            $this->createMock(RequestInterface::class),
            $this->createMock(RequestInterface::class),
        ];

        self::assertSame($requests, (new MultiRequest(...$requests))->getRequests());
    }

    public function testGetProtocolVersion(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->getProtocolVersion();
    }

    public function testWithProtocolVersion(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->withProtocolVersion('1.1');
    }

    public function testGetHeaders(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->getHeaders();
    }

    public function testHasHeader(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->hasHeader('X-Test');
    }

    public function testGetHeader(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->getHeader('X-Test');
    }

    public function testGetHeaderLine(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->getHeaderLine('X-Test');
    }

    public function testWithHeader(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->withHeader('X-Test', 'test');
    }

    public function testWithAddedHeader(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->withAddedHeader('X-Test', 'test');
    }

    public function testWithoutHeader(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->withoutHeader('X-Test');
    }

    public function testGetBody(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->getBody();
    }

    public function testWithBody(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->withBody($body);
    }

    public function testGetRequestTarget(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->getRequestTarget();
    }

    public function testWithRequestTarget(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->withRequestTarget('/');
    }

    public function testGetMethod(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->getMethod();
    }

    public function testWithMethod(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->withMethod('GET');
    }

    public function testGetUri(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->getUri();
    }

    public function testWithUri(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $multiRequest = new MultiRequest($request);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiRequest->withUri($uri);
    }
}
