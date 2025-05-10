<?php

declare(strict_types=1);

namespace Sunrise\Http\Client\Curl\Tests\Decorator;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sunrise\Http\Client\Curl\Decorator\RetryableClient;

final class RetryableClientTest extends TestCase
{
    private ClientInterface&MockObject $baseClient;
    private RequestInterface&MockObject $testRequest;
    private ResponseInterface&MockObject $testResponse;
    private NetworkExceptionInterface&MockObject $networkException;
    private int $sendAttempt;

    protected function setUp(): void
    {
        $this->baseClient = $this->createMock(ClientInterface::class);
        $this->testRequest = $this->createMock(RequestInterface::class);
        $this->testResponse = $this->createMock(ResponseInterface::class);
        $this->networkException = $this->createMock(NetworkExceptionInterface::class);
        $this->sendAttempt = 0;
    }

    public function testInvalidMaxAttempts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('maxAttempts must be >= 1');
        new RetryableClient($this->baseClient, maxAttempts: 0, baseDelay: 0);
    }

    public function testInvalidBaseDelay(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('baseDelay must be >= 0');
        new RetryableClient($this->baseClient, maxAttempts: 1, baseDelay: -1);
    }

    public function testSendRequestSucceedsOnFirstAttempt(): void
    {
        $client = new RetryableClient($this->baseClient, maxAttempts: 1, baseDelay: 0);
        $this->baseClient->expects(self::exactly(1))->method('sendRequest')->with($this->testRequest)->willReturn($this->testResponse);
        self::assertSame($this->testResponse, $client->sendRequest($this->testRequest));
    }

    public function testSendRequestSucceedsOnSecondAttempt(): void
    {
        $client = new RetryableClient($this->baseClient, maxAttempts: 2, baseDelay: 0);
        $this->baseClient->expects(self::exactly(2))->method('sendRequest')->with($this->testRequest)->willReturnCallback(fn() => ++$this->sendAttempt < 2 ? throw $this->networkException : $this->testResponse);
        self::assertSame($this->testResponse, $client->sendRequest($this->testRequest));
    }

    public function testSendRequestSucceedsOnThirdAttempt(): void
    {
        $client = new RetryableClient($this->baseClient, maxAttempts: 3, baseDelay: 0);
        $this->baseClient->expects(self::exactly(3))->method('sendRequest')->with($this->testRequest)->willReturnCallback(fn() => ++$this->sendAttempt < 3 ? throw $this->networkException : $this->testResponse);
        self::assertSame($this->testResponse, $client->sendRequest($this->testRequest));
    }

    public function testSendRequestFailsAfterFirstAttempt(): void
    {
        $client = new RetryableClient($this->baseClient, maxAttempts: 1, baseDelay: 0);
        $this->baseClient->expects(self::exactly(1))->method('sendRequest')->with($this->testRequest)->willThrowException($this->networkException);
        $this->expectException($this->networkException::class);
        $client->sendRequest($this->testRequest);
    }

    public function testSendRequestFailsAfterTwoAttempts(): void
    {
        $client = new RetryableClient($this->baseClient, maxAttempts: 2, baseDelay: 0);
        $this->baseClient->expects(self::exactly(2))->method('sendRequest')->with($this->testRequest)->willThrowException($this->networkException);
        $this->expectException($this->networkException::class);
        $client->sendRequest($this->testRequest);
    }

    public function testSendRequestFailsAfterThreeAttempts(): void
    {
        $client = new RetryableClient($this->baseClient, maxAttempts: 3, baseDelay: 0);
        $this->baseClient->expects(self::exactly(3))->method('sendRequest')->with($this->testRequest)->willThrowException($this->networkException);
        $this->expectException($this->networkException::class);
        $client->sendRequest($this->testRequest);
    }
}
