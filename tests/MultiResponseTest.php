<?php

declare(strict_types=1);

namespace Sunrise\Http\Client\Curl\Tests;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Sunrise\Http\Client\Curl\MultiResponse;

final class MultiResponseTest extends TestCase
{
    public function testCreateWithoutResponses(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MultiResponse();
    }

    public function testGetResponses(): void
    {
        $responses = [
            $this->createMock(ResponseInterface::class),
            $this->createMock(ResponseInterface::class),
        ];

        self::assertSame($responses, (new MultiResponse(...$responses))->getResponses());
    }

    public function testGetProtocolVersion(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->getProtocolVersion();
    }

    public function testWithProtocolVersion(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->withProtocolVersion('1.1');
    }

    public function testGetHeaders(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->getHeaders();
    }

    public function testHasHeader(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->hasHeader('X-Test');
    }

    public function testGetHeader(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->getHeader('X-Test');
    }

    public function testGetHeaderLine(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->getHeaderLine('X-Test');
    }

    public function testWithHeader(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->withHeader('X-Test', 'test');
    }

    public function testWithAddedHeader(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->withAddedHeader('X-Test', 'test');
    }

    public function testWithoutHeader(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->withoutHeader('X-Test');
    }

    public function testGetBody(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->getBody();
    }

    public function testWithBody(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->withBody($body);
    }

    public function testGetStatusCode(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->getStatusCode();
    }

    public function testWithStatus(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->withStatus(200);
    }

    public function testGetReasonPhrase(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $multiResponse = new MultiResponse($response);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not implemented.');
        $multiResponse->getReasonPhrase();
    }
}
